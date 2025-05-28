<?php
namespace Concrete\Package\EasyImageGallery\Block\EasyImageGallery;

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Attribute\Category\FileCategory;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\EditResponse as FileEditResponse;
use Concrete\Core\File\File;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\File\Set\SetList as FileSetList;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Package\EasyImageGallery\Options;
use Concrete\Package\EasyImageGallery\Tags;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette;

class Controller extends BlockController
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

    public function composer()
    {
        $this->addOrEdit();
        $this->set('isComposer', true);
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
        $this->requireAsset('css','easy-gallery-view');
        $this->requireAsset('javascript', 'jquery');
        $this->requireAsset('javascript', 'imagesloaded');
        $this->requireAsset('javascript', 'masonry');
        $this->requireAsset('javascript', 'isotope');
        $this->requireAsset('javascript', 'lazyload');
        switch ($this->getOptions()->lightbox) {
            case 'lightbox':
                $this->requireAsset('javascript', 'fancybox');
                $this->requireAsset('css', 'fancybox');
                break;
            case 'intense':
                $this->requireAsset('javascript', 'intense');
                break;
        }
    }

    public function view()
    {
        $options =  $this->getOptions();
        $files = [];
        foreach ($this->getAllFileIDs(explode(',', (string) $this->fIDs)) as $fID) {
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

    private function addOrEdit()
    {
        $this->setAssetEdit();
        $this->set('token', $this->app->make(Token::class));
        $this->set('form', $this->app->make(Form::class));
        $this->set('colorWidget', $this->app->make('helper/form/color'));
        $this->set('options', $this->getOptions());
        $this->set('fileSets', $this->getFileSetList());
        $this->set('fDetails', $this->getFilesDetails(explode(',', (string) $this->fIDs)));
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
     * @param int[]|string[] $fIDs
     *
     * @return array
     */
    private function expandFileIDs(array $fIDs)
    {
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
    private function getAllFileIDs(array $fIDs)
    {
        return array_map('intval', array_keys($this->expandFileIDs($fIDs)));
    }

    /**
     * @param int[]|string[] $fIDs
     *
     * @return array
     */
    private function getFilesDetails(array $fIDs)
    {
        $expandedIDs = $this->expandFileIDs($fIDs);
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
        $this->requireAsset('select2');
        $this->requireAsset('javascript', 'underscore');
        $this->requireAsset('javascript', 'core/app');
        $this->requireAsset('javascript', 'bootstrap-editable');
        $this->requireAsset('css', 'core/app/editable-fields');
        $this->requireAsset('javascript','knob');
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

}
