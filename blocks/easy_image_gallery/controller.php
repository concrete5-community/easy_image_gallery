<?php
namespace Concrete\Package\EasyImageGallery\Block\EasyImageGallery;

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Attribute\Category\FileCategory;
use Concrete\Core\Backup\ContentExporter;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\EditResponse as FileEditResponse;
use Concrete\Core\File\File;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\File\Set\SetList as FileSetList;
use Concrete\Core\File\Tracker\FileTrackableInterface;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Page\Page;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Utility\Service\Xml;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Package\EasyImageGallery\Options;
use Concrete\Package\EasyImageGallery\Tags;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette;
use SimpleXMLElement;

class Controller extends BlockController implements FileTrackableInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$helpers
     */
    protected $helpers = [];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btTable
     */
    protected $btTable = 'btEasyImageGallery';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btInterfaceWidth
     */
    protected $btInterfaceWidth = 600;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btInterfaceHeight
     */
    protected $btInterfaceHeight = 465;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btWrapperClass
     */
    protected $btWrapperClass = 'ccm-ui';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btCacheBlockRecord
     */
    protected $btCacheBlockRecord = false;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btCacheBlockOutput
     */
    protected $btCacheBlockOutput = false;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btCacheBlockOutputOnPost
     */
    protected $btCacheBlockOutputOnPost = false;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btCacheBlockOutputForRegisteredUsers
     */
    protected $btCacheBlockOutputForRegisteredUsers = false;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btSupportsInlineEdit
     */
    protected $btSupportsInlineEdit = true;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btSupportsInlineAdd
     */
    protected $btSupportsInlineAdd = true;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btDefaultSet
     */
    protected $btDefaultSet = 'multimedia';

    /**
     * @var string|null
     */
    protected $fIDs;

    /**
     * @var string|null
     */
    protected $options;

    /**
     * @var \Concrete\Package\EasyImageGallery\Options|null
     */
    private $decodedOptions;

    public function getBlockTypeName()
    {
        return t('Easy Images Gallery');
    }

    public function getBlockTypeDescription()
    {
        return t('Display your images and captions in an attractive way.');
    }

    public function add()
    {
        $this->addOrEdit();
    }

    public function edit()
    {
        $this->addOrEdit();
    }

    public function composer()
    {
        $this->addOrEdit();
        $this->set('isComposer', true);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function action_saveField()
    {
        $token = $this->app->make(Token::class);
        if (!$token->validate('eig_saveField')) {
            throw new UserMessageException($token->getErrorMessage());
        }
        $fID = (int) $this->request->request->get('fID');
        $file = $fID > 0 ? File::getByID($fID) : null;
        if (!$file) {
            throw new UserMessageException(t('File not found.'));
        }
        $fp = new Checker($file);
        if (!$fp->canEditFileProperties()) {
            throw new UserMessageException(t('Access Denied.'));
        }
        $fv = $file->getVersionToModify();
        $name = $this->request->request->get('name');
        $value = $this->request->request->get('value');
        switch ($this->request->request->get('name')) {
            case 'fvTitle':
                $fv->updateTitle($value);
                break;
            case 'fvDescription':
                $fv->updateDescription($value);
                break;
            case 'fvTags':
                $fv->updateTags($value);
                break;
            default:
                $category = $this->app->make(FileCategory::class);
                $ak = $category->getAttributeKeyByHandle($name);
                if ($ak) {
                    $fv->setAttribute($ak, $value);
                }
                break;
        }
        $sr = new FileEditResponse();
        $sr->setFile($file);
        $sr->setMessage(t('File updated successfully.'));
        $sr->setAdditionalDataAttribute('value', $value);
        $sr->setAdditionalDataAttribute('name', $name);

        return $this->app->make(ResponseFactoryInterface::class)->json($sr);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function action_getFileSetImages()
    {
        $token = $this->app->make(Token::class);
        if (!$token->validate('eig_getFileSetImages')) {
            throw new UserMessageException($token->getErrorMessage());
        }
        $files = [];
        $fsID = (int) $this->request->request->get('fsID');
        $fs = $fsID > 0 ? FileSet::getByID($fsID) : null;
        if ($fs) {
            $fsf = $fs->getFiles();
            if (!empty($fsf)) {
                foreach (array_filter($fsf) as $f) {
                    $fd = $this->getFileDetails($f, $fsID);
                    if ($fd) {
                        $files[] = $fd;
                    }
                }
            }
        }

        return $this->app->make(ResponseFactoryInterface::class)->json($files);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function action_getFileDetails()
    {
        $token = $this->app->make(Token::class);
        if (!$token->validate('eig_getFileDetails')) {
            throw new UserMessageException($token->getErrorMessage());
        }
        $fID = (int) $this->request->request->get('fID');
        $file = $fID > 0 ? File::getByID($fID) : null;
        if (!$file) {
            throw new UserMessageException(t('Unable to find the file specified.'));
        }
        $details = $this->getFileDetails($file, $fID);

        return $this->app->make(ResponseFactoryInterface::class)->json($details);
    }

    public function save($args)
    {
        $args = (is_array($args) ? $args : []) + [
            'fID' => null,
            'fIDs' => null,
            'options' => null,
            'internal_link_cid' => null,
            'uniqueFID' => null,
        ];
        $options = $args['options'];
        if (!$options instanceof Options) {
            $options = Options::fromUI($args);
        }
        if (is_array($args['internal_link_cid'])) {
            $ak = $this->app->make(FileCategory::class)->getAttributeKeyByHandle('internal_link_cid');
            if ($ak) {
                foreach ($args['internal_link_cid'] as $fID => $valueArray) {
                    $fID = (int) $fID;
                    $f = $fID > 0 ? File::getByID($fID) : null;
                    $fv = $f ? $f->getVersionToModify() : null;
                    if ($fv) {
                        $fv->setAttribute($ak, isset($valueArray[0]) ? (int) $valueArray[0] : 0);
                    }
                }
            }
        }
        if (is_array($args['fID'])) {
            $fIDs = implode(',', array_unique($args['fID']));
            if (is_array($args['uniqueFID'])) {
                $this->sortFileSets($args['fID'], $args['uniqueFID']);
            }
        } elseif (is_string($args['fIDs'])) {
            $fIDs = $args['fIDs'];
        } else {
            $fIDs = '';
        }
        parent::save([
            'fIDs' => $fIDs,
            'options' => json_encode((array) $options),
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::registerViewAssets()
     */
    public function registerViewAssets($outputContent = '')
    {
        $package = $this->getPackage();
        $assetList = AssetList::getInstance();
        $this->requireAsset('javascript', 'jquery');
        if (!$assetList->getAsset('css','easy-gallery-view')) {
            $assetList->register('css', 'easy-gallery-view', 'blocks/easy_image_gallery/stylesheet/block-view.css', ['version' => '1', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
        }
        $this->requireAsset('css','easy-gallery-view');
        if (!$assetList->getAsset('javascript', 'imagesloaded')) {
            $assetList->register('javascript', 'imagesloaded', 'blocks/easy_image_gallery/javascript/build/imagesloaded.pkgd.min.js', ['version' => '3.1.4', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
        }
        $this->requireAsset('javascript', 'imagesloaded');
        if (!$assetList->getAsset('javascript', 'masonry')) {
            $assetList->register('javascript', 'masonry', 'blocks/easy_image_gallery/javascript/build/masonry.pkgd.min.js', ['version' => '3.1.4', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
        }
        $this->requireAsset('javascript', 'masonry');
        if (!$assetList->getAsset('javascript', 'isotope')) {
            $assetList->register('javascript', 'isotope', 'blocks/easy_image_gallery/javascript/build/isotope.pkgd.min.js', ['version' => '3.1.4', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
        }
        $this->requireAsset('javascript', 'isotope');
        if (!$assetList->getAsset('javascript', 'lazyload')) {
            $assetList->register('javascript', 'lazyload', 'blocks/easy_image_gallery/javascript/build/jquery.lazyload.min.js', ['version' => '1.9.1', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
        }
        $this->requireAsset('javascript', 'lazyload');
        switch ($this->getOptions()->lightbox) {
            case 'lightbox':
                if (!$assetList->getAsset('javascript', 'fancybox')) {
                    $assetList->register('javascript', 'fancybox', 'blocks/easy_image_gallery/javascript/build/jquery.fancybox.pack.js', ['version' => '2.1.5', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
                }
                $this->requireAsset('javascript', 'fancybox');
                if (!$assetList->getAsset('css', 'fancybox')) {
                    $assetList->register('css', 'fancybox', 'blocks/easy_image_gallery/stylesheet/jquery.fancybox.css', ['version' => '2.1.5', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
                }
                $this->requireAsset('css', 'fancybox');
                break;
            case 'intense':
                if (!$assetList->getAsset('javascript', 'intense')) {
                    $assetList->register('javascript', 'intense', 'blocks/easy_image_gallery/javascript/build/intense.js', ['version' => '1', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
                }
                $this->requireAsset('javascript', 'intense');
                break;
        }
    }

    public function view()
    {
        $options =  $this->getOptions();
        $files = [];
        foreach ($this->getAllFileIDs() as $fID) {
            $file = File::getByID($fID);
            $fileVersion = $file ? $file->getApprovedVersion() : null;
            if (!$fileVersion) {
                continue;
            }
            $c = new Checker($file);
            if (!$c->canViewFile()) {
                continue;
            }
            $files[] = $file;
        }
        $this->generatePlaceHolders($files);
        $page = Page::getCurrentPage();
        $editMode = $page && !$page->isError() && $page->isEditMode();
        $this->set('editMode', $editMode);
        if ($editMode) {
            $this->set('localization', Localization::getInstance());
        }
        $this->set('files', $files);
        $this->set('options', $options);
        $this->set('tagsObject', $this->createTagsObject($files));
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\File\Tracker\FileTrackableInterface::getUsedCollection()
     */
    public function getUsedCollection()
    {
        return $this->getCollectionObject();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\File\Tracker\FileTrackableInterface::getUsedFiles()
     */
    public function getUsedFiles()
    {
        return $this->getAllFileIDs();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::export()
     */
    public function export(SimpleXMLElement $blockNode)
    {
        $xmlService = $this->app->make(Xml::class);
        $dataNode = $blockNode->addChild('data');
        $match = null;
        foreach (explode(',', (string) $this->fIDs) as $fID) {
            if (!preg_match('/^(?<type>[^0-9]*)(?<id>[1-9]\d{0,18})$/', $fID, $match)) {
                continue;
            }
            $id = (int) $match['id'];
            switch ($match['type']) {
                case 'fsID':
                    $fileSet = FileSet::getByID($id);
                    if ($fileSet) {
                        $fileSetNode = $dataNode->addChild('fileset');
                        $fileSetNode->addAttribute('name', $fileSet->getFileSetName());
                        foreach (array_filter($fileSet->getFiles()) as $file) {
                            $fileSetNode->addChild('file', ContentExporter::replaceFileWithPlaceHolder($file->getFileID()));
                        }
                    }
                    break;
                case '':
                    $exportFile = ContentExporter::replaceFileWithPlaceHolder($fID);
                    if ($exportFile) {
                        $dataNode->addChild('file', $exportFile);
                    }
                    break;
            }
        }
        $attributeKeys = [];
        $category = $this->app->make(FileCategory::class);
        foreach ([
            'gallery_columns',
            'link_type',
            'internal_link_cid',
            'external_link_url',
            'image_tag',
        ] as $akHandle) {
            $attributeKey = $category->getAttributeKeyByHandle($akHandle);
            if ($attributeKey) {
                $attributeKeys[] = $attributeKey;
            }
        }
        foreach (array_unique($this->getAllFileIDs()) as $fID) {
            $file = File::getByID($fID);
            $fileVersion = $file ? $file->getApprovedVersion() : null;
            if (!$fileVersion) {
                continue;
            }
            $fileAttributesNode = $dataNode->addChild('fileAttributes');
            $fileAttributesNode->addAttribute('file', ContentExporter::replaceFileWithPlaceHolder($fID));
            $fileAttributesNode->addAttribute('title', (string) $fileVersion->getTitle());
            $fileAttributesNode->addAttribute('description', (string) $fileVersion->getDescription());
            foreach (preg_split('/[\r\n]+/', (string) $fileVersion->getTags(), -1, PREG_SPLIT_NO_EMPTY) as $tag) {
                $xmlService->createCDataNode($fileAttributesNode, 'tag', $tag);
            }
            foreach ($attributeKeys as $attributeKey) {
                $attributeValue = $fileVersion->getAttributeValueObject($attributeKey);
                if (!$attributeValue) {
                    continue;
                }
                $attributeNode = $fileAttributesNode->addChild('attribute');
                $attributeNode->addAttribute('handle', $attributeKey->getAttributeKeyHandle());
                $attributeValue->getController()->exportValue($attributeNode);
            }
        }
        $xmlService->createCDataNode($dataNode, 'options', $this->getOptions()->export());
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::getImportData()
     */
    protected function getImportData($blockNode, $page)
    {
        $category = $this->app->make(FileCategory::class);
        $dataNode = isset($blockNode->data) ? $blockNode->data : null;
        $optionsJSON = isset($dataNode->options) ? (string) $dataNode->options : null;
        $options = Options::fromJson($optionsJSON);
        $fIDs = [];
        if ($dataNode !== null) {
            $inspector = $this->app->make('import/value_inspector');
            foreach ($dataNode->children() as $node) {
                $nodeName = $node->getName();
                switch ($nodeName) {
                    case 'fileset':
                        $fileSetName = isset($node['name']) ? (string) $node['name'] : '';
                        if ($fileSetName !== '') {
                            $fileSet = FileSet::getByName($fileSetName);
                            if ($fileSet) {
                                $fileSetFiles = array_filter($fileSet->getFiles());
                            } else {
                                $fileSet = FileSet::create($fileSetName);
                                $fileSetFiles = [];
                            }
                        }
                        $fIDs[] = 'fsID' . $fileSet->getFileSetID();
                        if (isset($node->file)) {
                            foreach ($node->file as $fileNode) {
                                $fileID = (int) $inspector->inspect((string) $fileNode)->getReplacedValue();
                                if ($fileID < 1) {
                                    continue;
                                }
                                foreach ($fileSetFiles as $existingFile) {
                                    if ($fileID === (int) $existingFile->getFileID()) {
                                        continue 2;
                                    }
                                }
                                $fileSet->addFileToSet($fileID);
                            }
                        }
                        break;
                    case 'file':
                        $fileID = (int) $inspector->inspect((string) $node)->getReplacedValue();
                        if ($fileID > 0) {
                            $fIDs[] = $fileID;
                        }
                        break;
                    case 'fileAttributes':
                        $fileID = isset($node['file']) ? (int) $inspector->inspect((string) $node['file'])->getReplacedValue() : 0;
                        $file = $fileID > 0 ? File::getByID($fileID) : null;
                        $fileVersion = $file ? $file->getApprovedVersion() : null;
                        if ($fileVersion) {
                            $title = isset($node['title']) ? (string) $node['title'] : '';
                            if ($title !== '' && $title !== $fileVersion->getTitle()) {
                                $fileVersion->updateTitle($title);
                            }
                            $description = isset($node['description']) ? (string) $node['description'] : '';
                            if ($description !== '' && $description !== $fileVersion->getDescription()) {
                                $fileVersion->updateDescription($description);
                            }
                            $tags = [];
                            if (isset($node->tag)) {
                                foreach ($node->tag as $tagNode) {
                                    if (($tag = (string) $tagNode) !== '') {
                                        $tags[] = $tag;
                                    }
                                }
                            }
                            if ($tags !== []) {
                                $fileVersion->updateTags(implode("\n", $tags));
                            }
                            if (isset($node->attribute)) {
                                foreach ($node->attribute as $attributeNode) {
                                    $akHandle = isset($attributeNode['handle']) ? (string) $attributeNode['handle'] : '';
                                    $attributeKey = $akHandle === '' ? null : $category->getAttributeKeyByHandle($akHandle);
                                    if (!$attributeKey) {
                                        continue;
                                    }
                                    $attributeValue = $attributeKey->getController()->importValue($attributeNode);
                                    if ($attributeValue) {
                                        $fileVersion->setAttribute($attributeKey, $attributeValue);
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }

        return [
            'fIDs' => implode(',', $fIDs),
            'options' => $options,
        ];
    }

    private function addOrEdit()
    {
        $this->setAssetEdit();
        $this->set('token', $this->app->make(Token::class));
        $this->set('form', $this->app->make(Form::class));
        $this->set('colorWidget', $this->app->make('helper/form/color'));
        $this->set('options', $this->getOptions());
        $this->set('fileSets', $this->getFileSetList());
        $this->set('fDetails', $this->getFilesDetails());
        $this->set('fsIDs', array_values(
            array_unique(
                array_filter(
                    array_map(
                        static function ($fID) {
                            return preg_match('/^fsID[1-9]\d{0,18}$/', $fID) ? (int) substr($fID, strlen('fsID')) : 0;
                        },
                        explode(',', (string) $this->fIDs)
                    ),
                    static function ($fsID) {
                        return $fsID > 0;
                    }
                )
            )
        ));
        $this->set('isComposer', false);
    }

    /**
     * @return \Concrete\Package\EasyImageGallery\Options
     */
    private function getOptions()
    {
        if ($this->decodedOptions === null) {
            $this->decodedOptions = Options::fromJson($this->options);
        }
        return $this->decodedOptions;
    }

    /**
     * @return array
     */
    private function getExpandFileIDs()
    {
        $fIDs = explode(',', (string) $this->fIDs);
        if ($fIDs === []) {
            return [];
        }
        $cn = $this->app->make(Connection::class);
        $match = null;
        $expandedIDs = [];
        foreach ($fIDs as $value) {
            if (!preg_match('/^(?<type>[^0-9]*)(?<id>[1-9]\d{0,18})$/', (string) $value, $match)) {
                continue;
            }
            $id = (int) $match['id'];
            switch ($match['type']) {
                case 'fsID':
                    $rs = $cn->executeQuery('SELECT fID FROM FileSetFiles WHERE fsID = ? ORDER BY fsDisplayOrder ASC', [$id]);
                    foreach ($rs->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                        $expandedIDs[$row['fID']] = $value;
                    }
                    break;
                case '':
                    $expandedIDs[$value] = 'file';
                    break;
            }
        }

        return $expandedIDs;
    }

    /**
     * @param int[]|string[] $fIDs
     *
     * @return int[]
     */
    private function getAllFileIDs()
    {
        return array_map('intval', array_keys($this->getExpandFileIDs()));
    }

    /**
     * @return array
     */
    private function getFilesDetails()
    {
        $expandedIDs = $this->getExpandFileIDs();
        if ($expandedIDs === []) {
            return [];
        }
        $result = [];
        foreach ($expandedIDs as $fID => $type) {
            $f = File::getByID($fID);
            if (!$f) {
                continue;
            }
            if (strpos($type,'fsID') === 0) {
                $origin = substr($type, 4);
            } else {
                $origin = 'file';
            }
            if (($detail = $this->getFileDetails($f, $origin)) !== null) {
                $result[] = $detail;
            }
        }

        return $result;
    }

    /**
     * @return \Concrete\Core\File\Set\Set[]
     */
    private function getFileSetList()
    {
        $fs = new FileSetList();

        return $fs->get();
    }

    private function setAssetEdit()
    {
        $package = $this->getPackage();
        $assetList = AssetList::getInstance();
        $this->requireAsset('core/file-manager');
        $this->requireAsset('css', 'core/file-manager');
        $this->requireAsset('css', 'jquery/ui');
        $this->requireAsset('javascript', 'bootstrap/dropdown');
        $this->requireAsset('javascript', 'bootstrap/tooltip');
        $this->requireAsset('javascript', 'bootstrap/popover');
        $this->requireAsset('javascript', 'jquery/ui');
        $this->requireAsset('javascript', 'core/events');
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        if (version_compare(APP_VERSION, '9') < 0) {
            $this->requireAsset('select2');
        }
        $this->requireAsset('javascript', 'underscore');
        $this->requireAsset('javascript', 'core/app');
        if (version_compare(APP_VERSION, '9') >= 0) {
            if (!$assetList->getAsset('javascript', 'bootstrap-editable')) {
                $assetList->register('javascript', 'bootstrap-editable', 'js/bootstrap-editable.js', ['version' => '1.5.3', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => true], $package);
            }
            $this->requireAsset('javascript', 'bootstrap-editable');
            if (!$assetList->getAsset('css', 'bootstrap-editable')) {
                $assetList->register('css', 'bootstrap-editable', 'css/bootstrap-editable.css', ['version' => '1.5.3', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => true], $package);
            }
            $this->requireAsset('css', 'bootstrap-editable');
        } else {
            $this->requireAsset('javascript', 'bootstrap-editable');
            $this->requireAsset('css', 'core/app/editable-fields');
        }
        if (!$assetList->getAsset('javascript', 'knob')) {
            $assetList->register('javascript', 'knob', 'blocks/easy_image_gallery/javascript/build/jquery.knob.js', ['version' => '1.2.11', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
        }
        $this->requireAsset('javascript','knob');
        if (!$assetList->getAsset('css', 'easy-gallery-edit')) {
            $assetList->register('css', 'easy-gallery-edit', 'blocks/easy_image_gallery/stylesheet/block-edit.css', ['version' => '1', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true], $package);
        }
        $this->requireAsset('css','easy-gallery-edit');
    }

    /**
     * @param \Concrete\Core\Entity\File\File $file
     * @param \Concrete\Package\EasyImageGallery\Options $options
     *
     * @return string
     */
    public function getImageLink($file, $options)
    {
        $fileVersion = $file->getApprovedVersion();
        if (!$fileVersion) {
            return '';
        }
        if ($options->lightbox) {
            return (string) $fileVersion->getRelativePath();
        }
        $link_type = str_replace('<br/>', '', (string) $fileVersion->getAttribute('link_type', 'display'));
        switch ($link_type) {
            case 'Page':
                $page = Page::getByID($fileVersion->getAttribute('internal_link_cid'), 'ACTIVE');
                return $page && !$page->isError() ? (string) $page->getCollectionLink() : '';
            case 'URL':
                return (string) $fileVersion->getAttribute('external_link_url');
            default:
                return '';
        }
    }

    /**
     * @param \Concrete\Core\Entity\File\File[] $files
     */
    private function generatePlaceHolders(array $files)
    {
        if ($files === []) {
            return;
        }
        $imagine = $this->app->make(ImagineInterface::class);
        $placeholderMaxSize = 600;
        $palette = new Palette\RGB();
        $backgroundColor = $palette->color([0, 0, 0], 87);
        foreach ($files as $file) {
            $fileVersion = $file->getApprovedVersion();
            if (!$fileVersion) {
                continue;
            }
            if (($fileWidth = (int) $fileVersion->getAttribute('width')) <= 0) {
                continue;
            }
            if (($fileHeight = (int) $fileVersion->getAttribute('height')) <= 0) {
                continue;
            }
            $newWidth = $placeholderMaxSize;
            $newHeight = floor($fileHeight * $placeholderMaxSize / $fileWidth);
            $placeholderFile =  __DIR__ . "/images/placeholders/placeholder-{$fileWidth}-{$fileHeight}.png";
            if (file_exists($placeholderFile)) {
                continue;
            }
            $imagine
                ->create(new Box($newWidth, $newHeight), $backgroundColor)
                ->save($placeholderFile)
            ;
        }
    }

    /**
     * @return \Concrete\Package\EasyImageGallery\Tags
     */
    private function createTagsObject(array $files)
    {
        $tags = new Tags();
        if ($files === []) {
            return $tags;
        }
        $ak = $this->app->make(FileCategory::class)->getAttributeKeyByHandle('image_tag');
        if (!$ak) {
            return $tags;
        }
        $cn = $this->app->make(Connection::class);
        $qb = $cn->createQueryBuilder();
        $ors = [];
        foreach ($files as $index => $file) {
            if (!$file->getAttribute('image_tag')) {
                continue;
            }
            $ors[] = $qb->expr()->andX(
                "FileAttributeValues.fID = :fID{$index}",
                "FileAttributeValues.fvID = :fvID{$index}"
            );
            $qb
                ->setParameter("fID{$index}", $file->getFileID())
                ->setParameter("fvID{$index}", $file->getFileVersionID())
            ;
        }
        if ($ors === []) {
            return $tags;
        }
        $qb
            ->select([
                'FileAttributeValues.fID',
                'atSelectOptions.value',
            ])
            ->from('FileAttributeValues', 'FileAttributeValues')
            ->innerJoin('FileAttributeValues', 'atSelectOptionsSelected', 'atSelectOptionsSelected', 'FileAttributeValues.avID = atSelectOptionsSelected.avID')
            ->innerJoin('atSelectOptionsSelected', 'atSelectOptions', 'atSelectOptions', 'atSelectOptionsSelected.avSelectOptionID = atSelectOptions.avSelectOptionID')
            ->andWhere('FileAttributeValues.akID = :akID')
            ->setParameter('akID', $ak->getAttributeKeyID())
            ->andWhere(call_user_func_array([$qb->expr(), 'orX'], $ors))
        ;
        $rs = $qb->execute();
        $lcase = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        while (($row = $rs->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $fID = $row['fID'];
            $handle = preg_replace('/\s+/', '', $lcase((string) $row['value']));
            if (!isset($tags->fileTags[$fID])) {
                $tags->fileTags[$fID] = [];
            }
            $tags->fileTags[$fID][] = $handle;
            $tags->tags[$handle] = $row['value'];
        }

        return $tags;
    }

    private function sortFileSets(array $fIDs, array $uniqueFIDs)
    {
        $filesetIDAndFiles = [];
        $m = null;
        foreach ($fIDs as $index => $fID) {
            if (!preg_match('/^fsID([1-9][0-9]*)$/', (string) $fID, $m)) {
                continue;
            }
            $fsID = (int) $m[1];
            if (!isset($uniqueFIDs[$index]) || !is_numeric($uniqueFIDs[$index])) {
                continue;
            }
            if (!isset($filesetIDAndFiles[$fsID])) {
                $filesetIDAndFiles[$fsID] = [];
            }
            $filesetIDAndFiles[$fsID][] = (int) $uniqueFIDs[$index];
        }
        foreach ($filesetIDAndFiles as $fsID => $fileIDs) {
            $set = FileSet::getByID($fsID);
            if ($set) {
                $set->updateFileSetDisplayOrder($fileIDs);
            }
        }
    }

    /**
     * @param \Concrete\Core\Entity\File\File $file
     * @param string|int $origin
     *
     * @return \stdClass|null
     */
    private function getFileDetails($file, $origin)
    {
        $checker = new Checker($file);
        if (!$checker->canViewFile()) {
            return null;
        }
        $fileVersion = $file ? $file->getVersionToModify() : null;
        if (!$fileVersion) {
            return null;
        }
        $to = $fileVersion->getTypeObject();
        $o = $fileVersion->getJSONObject();
        $o->generic_type = $to->getGenericType();
        // Value fro the image link
        $o->internal_link_cid = $fileVersion->getAttribute('internal_link_cid') ?: false;
        $o->external_link_url = $fileVersion->getAttribute('external_link_url') ?: false;
        $o->link_type = str_replace('<br/>', '', $fileVersion->getAttribute('link_type', 'display'));
        $o->originType = 'file';
        $o->fsID = false;
        if (is_numeric($origin)) {
            $origin = (int) $origin;
            $fs = FileSet::getByID($origin);
            if ($fs) {
                $o->originType = 'fileset';
                $o->fsID = $origin;
                $o->filesetName = $fs->getFileSetName();
            }
        }

        return $o;
    }

    /**
     * @return \Concrete\Core\Entity\Package
     */
    private function getPackage()
    {
        static $result;

        if ($result === null) {
            $result = $this->app->make(PackageService::class)->getByHandle('easy_image_gallery');
        }

        return $result;
    }
}
