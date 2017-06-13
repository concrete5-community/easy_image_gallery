<?php
namespace Concrete\Package\EasyImageGallery\Block\EasyImageGallery;

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Block\BlockController;
use Loader;
use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use \Concrete\Core\Http\ResponseAssetGroup;
use Permissions;
use Page;
use Core;
use View;
use File;
use FileSet;
use StdClass;
use \Concrete\Core\File\Set\SetList as FileSetList;
use FileAttributeKey;

use Concrete\Package\EasyImageGallery\Controller\Tools\EasyImageGalleryTools;


class Controller extends BlockController
{
    protected $btTable = 'btEasyImageGallery';
    protected $btInterfaceWidth = "600";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "465";
    protected $btCacheBlockRecord = false;
    protected $btExportFileColumns = array('fID');
    protected $btCacheBlockOutput = false;
    protected $btCacheBlockOutputOnPost = false;
    protected $btCacheBlockOutputForRegisteredUsers = false;
    protected $btSupportsInlineEdit = true;
    protected $btSupportsInlineAdd = true;
    protected $btDefaultSet = 'multimedia';

    public function getBlockTypeDescription()
    {
        return t("Display your images and captions in an attractive way.");
    }

    public function getBlockTypeName()
    {
        return t("Easy Images Gallery");
    }

    public function add() {
        $this->setAssetEdit();
        $this->set('fileSets', $this->getFileSetList());
        $this->set('options', $this->getOptionsJson());
        $this->set('selectedFilesets',array());
    }

    public function edit()
    {
        $this->setAssetEdit();

        $this->set('fileSets', $this->getFileSetList());
        $this->set('selectedFilesets', $this->getSelectedFilesets());
        $this->set('options', $this->getOptionsJson());
        $this->set('fDetails',$this->getFilesDetails());
    }

    function getSelectedFilesets() {
      $options = json_decode($this->options,true);
      return (is_array($options['fsIDs']) && count($options['fsIDs'])) ? $options['fsIDs'] : array();
    }

    function getOptionsJson ()  {
        // Cette fonction retourne un objet option
        // SI le block n'existe pas encore, ces options sont préréglées
        // Si il existe on transfome la chaine de charactère en json
        if ($this->isValueEmpty()) :
            $options = new StdClass();
            $options->lightbox = 'lightbox';
            $options->galleryColumns = 4;
            $options->galleryTitle = 1;
            $options->galleryDescription = 0;
            $options->lightboxTitle = 1;
            $options->lightboxDescription = 0;
            $options->fancyOverlay = '#f0f0f0';
            $options->fancyOverlayAlpha = .9;
            $options->hoverColor = '#f0f0f0';
            $options->hoverTitleColor = '#333333';
            return $options;
        else:
            $options = json_decode($this->options);
            // legacy
            if(!$options->fancyOverlay) $options->fancyOverlay = '#f0f0f0';
            if(!$options->fancyOverlayAlpha) $options->fancyOverlayAlpha = .9;
            if(!$options->hoverColor) $options->hoverColor = '#f0f0f0';
            if(!$options->hoverTitleColor) $options->hoverTitleColor = '#333333';
            if(!$options->dateFormat) $options->dateFormat = 'm - Y';
            // end legacy
            return $options ;
        endif;

    }


    function getFilesDetails ($fIDs = false, $details = true) {
      $tools = new EasyImageGalleryTools();
      $db = Loader::db();

      if (!$fIDs)
        $fIDs = explode(',', $this->fIDs);
      $_fIDs = array();
      $fDetails = array();

      foreach ($fIDs as $key => $value) :
        if(strpos($value,'fsID') === 0 ): // Le fID commence par "fsID" Donc on va extraire les images
          $fsID = substr($value,4);
          $r = $db->query('SELECT fID FROM FileSetFiles WHERE fsID = ? ORDER BY fsDisplayOrder ASC', array($fsID));
          while ($row = $r->FetchRow()) {
              $_fIDs[$row['fID']] = 'fsID' . $fsID;
          }
        else:
          $_fIDs[$value] = 'file';
        endif;
      endforeach;
      $fIDs = $_fIDs;

      // Si on ne veut pas de details,
      // On retourne un tableau avec les fID
      if (!$details) return array_keys($fIDs);

      // Maintenant on extriait les details de chaque images
      foreach ($fIDs as $fID => $type) {
          $f = File::getByID($fID);
          if (is_object($f)):
            $origin = "file";
            // Si le fichier fait partie d'un FS, son origine sera numerique
            // Et représentera le fsID
            if(strpos($type,'fsID') === 0 ) $origin = substr($type,4);
             $fDetails[] = $tools->getFileDetails($f,$origin);
          endif;
      }
        return $fDetails;
    }

    // For view
    function getFileFromFileID ($fID) {
        if ($fID) :
            $f = File::getByID($fID);
            if (is_object($f)) return $f;
        endif;
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('css','easy-gallery-view');
        $this->requireAsset('javascript', 'jquery');
        $this->requireAsset('javascript', 'imagesloaded');
        $this->requireAsset('javascript', 'masonry');
        $this->requireAsset('javascript', 'isotope');
        $this->requireAsset('javascript', 'lazyload');

    }

    public function getGallery($b,$controller = null,$choice = array()) {

      if (!$controller) $controller = $this;
      $view = View::getInstance();

      $choice = array_merge(array(
                        'type' => 'file_manager_detail',
                        'wrapperTag' => 'div',
                        'itemTag' => 'div',
                        'AddInnerDiv' => true,
                        'topicAttributeKeyHandle' => 'project_topics',
                        'alternativeDateAttributeHandle' => 'date',
                        'hideEditMode' => true,
                        'user' => false,
                        'topics' => false,
                        'forcePopup' => false,
                        'slider' => false,
                        'additionalWrapperClasses' => array(),
                        'additionalItemClasses' => array()),
                        $choice);

        $options =  $this->getOptionsJson();
        $c = Page::getCurrentPage();

        // Files
        $fIDs = $this->getFilesDetails(false,false);
        $files = array_filter(array_map(array($this,'getFileFromFileID') , $fIDs));
        $vars['fIDs'] = $fIDs;
        $vars['selectedFilesets'] = $this->getSelectedFilesets();
        $vars['files'] = $files;
        $vars['options'] = $options;
        // print_r($files); exit();
        $this->generatePlaceHolderFromArray($files);

        // Lightbox
        if($options->lightbox == 'lightbox'):
            $this->requireAsset('javascript', 'fancybox');
            $this->requireAsset('css', 'fancybox');
        elseif($options->lightbox == 'intense'):
            $this->requireAsset('javascript', 'intense');
        endif;

        // Tags
        $tagsObject = $this->getFilesTags($files);
        $vars['tagsObject'] = $tagsObject;

        $db = Loader::db();

        $galleryHasImage = false;

        $vars = array();
        $nh = Loader::helper('navigation');
        $vars['th'] = $th = Loader::helper('text');
        $vars['dh'] = $dh = Core::make('helper/date');
        $type = \Concrete\Core\File\Image\Thumbnail\Type\Type::getByHandle($choice['type']);

        $isCarousel = $options->galleryType == 'carousel';
        $isMasonry = $options->galleryType == 'masonry';
        $isStaticGrid = !$isMasonry && !$isCarousel;

        $vars['column_class'] = 'col-mcl-' . intval(12 / $options->galleryColumns);
        $vars['$masonryWrapperAttributes'] = 'data-gridsizer=".' . $vars['column_class'] . '" data-bid="' . $b->getBlockID() . '"';
        $vars['gap'] = $options->galleryGap ? 'with-gap' : 'no-gap';

        // Item classes
        if(!$isCarousel) $itemClasses[] = $vars['column_class'];
        $itemClasses[] = 'item';
        if ($isMasonry) $itemClasses[] = 'masonry-item';

        foreach ($files as $key => $file) :
            if(!is_object($file)) continue;

            // variables
            $file->details = array();
            $file->details['imageColumn'] = $file->getAttribute('gallery_columns') ? $file->getAttribute('gallery_columns') : $options->galleryColumns;
            $file->details['placeHolderUrl'] = $controller->getBlockURL() . "/images/placeholders/placeholder-{$file->getAttribute('width')}-{$file->getAttribute('height')}.png";
            $file->details['retinaThumbnailUrl'] = $file->getThumbnailURL($type->getDoubledVersion());
            $file->details['fullUrl'] = $this->getImageLink($file,$options);
            $tags = isset($tagsObject->fileTags[$file->getFileID()]) ? implode(' ',$tagsObject->fileTags[$file->getFileID()]) : '';
            $file->details['to'] = 'href="' . $fullUrl . '"' . ($options->lightbox ? 'data-fancybox-group="easy-gallery-' . $b->getBlockID . '" data-image="' . $fullUrl . '"'  . ($options->lightboxTitle ? 'title="' . $file->getTitle() : '') . ($options->lightboxDescription ? $file->getDescription() : '') : '') . '"';
            // Item classes
            $itemClassesTemp = $itemClasses;
            $itemClassesTemp[] = $key % 2 == 1 ? 'pair' : 'impair';
            $itemClassesTemp[] = $tags;

            $file->details['itemOpenTag'] = (($key%$options->galleryColumns == 0 && $isStaticGrid) ? ('<div class="row' . ($options->galleryGap ? '' : ' no-gap') . '">') : '') .
                                            '<' . $choice['itemTag'] . ' class="' .implode(' ',  array_merge($itemClassesTemp,$choice['additionalItemClasses'])) . '"' .
                                            'style="' . ($options->galleryGap && $isCarousel ? 'margin:0 15px;' : '') . 'width:' . (100 / $options->galleryColumns) . '%' . '"' .
                                            '>' . ($choice['AddInnerDiv'] ? '<div class="inner">' : '');
            $file->details['itemCloseTag'] = ($choice['AddInnerDiv'] ? '</div>' : '') . '</' . $choice['itemTag'] . '>' . (($key%$options->galleryColumns == ($options->galleryColumns) - 1 || ($key == count($files)-1)) && $isStaticGrid ? '</div><!-- .row -->' : '');


        endforeach;
            $vars['files'] = $files;
            // carousels
            if ($isCarousel) :
            $slick = new StdClass();
            $slick->slidesToShow = $options->galleryColumns;
            $slick->slidesToScroll = $options->galleryColumns;
            $slick->margin = $options->galleryGap ? 30 : 0;
            $slick->dots = (bool)$o->carousel_dots;
            $slick->arrows = (bool)$o->carousel_arrows;
            $slick->infinite = (bool)$o->carousel_infinite;
            $slick->speed = (int)$o->carousel_speed;
            $slick->centerMode = (bool)$o->carousel_centerMode;
            $slick->variableWidth = (bool)$o->carousel_variableWidth;
            $slick->adaptiveHeight = (bool)$o->carousel_adaptiveHeight;
            $slick->autoplay = (bool)$o->carousel_autoplay;
            $slick->autoplaySpeed = (int)$o->carousel_autoplaySpeed;
            $vars['slick'] = $slick;
            endif;

            /***** Block related ****/
            $templateName = $b->getBlockFilename();
            $blockTypeHandle = str_replace('_', '-', $b->getBlockTypeHandle());
            $templateCleanName = str_replace('_', '-', substr(substr( $templateName, 0, strlen( $templateName ) -4 ),10)); // Retire le '.php' et 'supermint_'

            // Wrapper classes
            $wrapperClasses[] = 'mcl-' . $blockTypeHandle; // mcl-easy-gallery
            $wrapperClasses[] =  $blockTypeHandle . '-' . $templateCleanName; //-> easy-gallery-portfolio
            $wrapperClasses[] = $templateCleanName; // -> portfolio
            if ($isCarousel) 	$wrapperClasses[] = 'slick-wrapper ';
            if ($isMasonry) 	$wrapperClasses[] = 'masonry-wrapper';
            if($options->lightbox) $wrapperClasses[] = 'clickable';
            $wrapperClasses[] = 'wrapper-'. $options->galleryColumns . '-column';
            // $wrapperClasses[] = 'row';
            $wrapperClasses[] = $vars['gap'];
            // Wrapper attributes
            $wrapperAtrtribute[] = 'data-bid="' . $b->getBlockID() . '"';
            if ($isMasonry) $wrapperAtrtribute[] = 'data-gridsizer=".' . $vars['column_class'] . '"';
            if ($isCarousel) $wrapperAtrtribute[] = 'data-slick=\'' . json_encode($slick) . '\'';
            // Finally, wrapper html
            $vars['wrapperOpenTag'] = '<' . $choice['wrapperTag'] . ' class="' . implode(' ', array_merge($wrapperClasses,$choice['additionalWrapperClasses'])) . '" ' . implode(' ', $wrapperAtrtribute) . ' id="easy-gallery-' . $b->getBlockID() . '">';
            $vars['wrapperCloseTag'] = '</' . $choice['wrapperTag'] . '><!-- end .' . $blockTypeHandle . '-' . $templateCleanName . ' -->';
            // Item classes
            if(!$isCarousel) $itemClasses[] = $vars['column_class'];
            $itemClasses[] = 'item';
            if ($isMasonry) $itemClasses[] = 'masonry-item';
            // itemTag
            $itemAttributes = array();
            if($isCarousel) $itemAttributes[] = $options->galleryGap ? 'style="margin:0 15px"' : '';


            if ($c->isEditMode() && $choice['hideEditMode']) :
            echo '<div class="ccm-edit-mode-disabled-item">';
            echo '<p style="padding: 40px 0px 40px 0px;">' .
              '[ ' . $blockTypeHandle . ' ] ' .
              '<strong>' .
              ucwords($templateCleanName) .
              ($isCarousel ? t(' carousel') : '') .
              ($isMasonry ? t(' masonry') : '') .
              ($isStaticGrid ? t(' static grid') : '') .
              '</strong>' .
              t(' with ') .
              $options->galleryColumns .
              t(' columns and ') .
              ($options->galleryGap ? t(' regular Gap ') : t('no Gap ')) .
              t(' disabled in edit mode.') .
              '</p>';
            echo '</div>';
            endif;

      if ($isMasonry) Loader::PackageElement("view/sortable",'easy_image_gallery', array('options'=>$options,'tagsObject'=>$tagsObject,'bID'=>$b->getBlockID(),'styleObject'=>$styleObject));

      return $vars;

    }

    public function getFileSetList () {
        $fs = new FileSetList();
        return $fs->get();
    }

    public function composer() {
        $this->setAssetEdit();
    }

    public function isValueEmpty() {
        if ($this->fIDs)
            return false;
        else
            return true;
    }

    public function setAssetEdit () {

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
        $this->requireAsset('javascript','easy-gallery-edit');
        $this->requireAsset('css','easy-gallery-edit');
    }

    public function save ($args)
    {
        $options = $args;
        unset($options['fID']);
        unset($options['internal_link_cid']);

        // Vu que je n'arrive pas encore a sauver en ajax l'attribut cID du lien
        // (meme si dans le filemanager la fenetre attribut y arrive)
        // je boucle et sauve pour chaque fichier

        if(is_array($args['internal_link_cid'])) :
          $ak = FileAttributeKey::getByHandle('internal_link_cid');
          if (is_object($ak)) :
            foreach ($args['internal_link_cid'] as $fID => $valueArray) :
              $f = File::getByID($fID);
              if(is_object($f)) :
                $fv = $f->getVersionToModify();
                $fv->setAttribute($ak,$valueArray[0]);
              endif;
            endforeach;
          endif;
        endif;


        $fsIDs = array();
        if (is_array($args['fID'])):
          $args['fIDs'] = implode(',', array_unique($args['fID']));
          // Now extract Filset ID and save it in Options
          foreach ($args['fID'] as $k => $value) :
            if(strpos($value,'fsID') === 0 ):
              $fsID = (int)substr($value,4);
              //Le tableau des filesets
              $fsIDs[] = $fsID;
              // le meme tableau avec les ficheirs dans l'odre à l'intérieur (pour sauver l'ordre dans les fs)
              $filesetIDAndFiles[$fsID][] = $args['uniqueFID'][$k];
            endif;
          endforeach;
          $options['fsIDs'] =  array_values(array_unique($fsIDs));
        endif;

        // Maintenant on sauve l'ordre des fichiers dans chaque set
        foreach ($filesetIDAndFiles as $fsID => $arrayOffID):
          $set = \Concrete\Core\File\Set\Set::getByID($fsID);
          $set->updateFileSetDisplayOrder($arrayOffID);
        endforeach;

        if (!is_numeric($options['fancyOverlayAlpha']) || $options['fancyOverlayAlpha'] > 1 || $options['fancyOverlayAlpha'] < 0) $options['fancyOverlayAlpha'] = .9;
        $args['options'] = json_encode($options);
        parent::save($args);
    }

    function getImageLink($f,$options) {
      if (!$options->lightbox) :
        if ($f->getAttribute('link_type')):
          $link_type = str_replace('<br/>', '', $f->getAttribute('link_type','display'));
          switch ($link_type) {
            case 'Page':
              $internal_link = Page::getByID($f->getAttribute('internal_link_cid'), 'ACTIVE');
              $fullUrl = (is_object($internal_link) && $internal_link->getCollectionID()) ? $internal_link->getCollectionLink() : false;
              break;
            case 'URL':
              $external_link_url = $f->getAttribute('external_link_url');
              $fullUrl = $external_link_url ? $external_link_url : false;
              break;
            default:
              $fullUrl = false;
          }
        endif;
      else :
        $fullUrl = $f->getRelativePath();
      endif;

      return $fullUrl;
    }

    public function getFilesTags ($files) {
      $tagsObject = new StdClass();
      $tagsObject->tags = $tagsObject->fileTags = array();
      $ak = FileAttributeKey::getByHandle('image_tag');
      $db = Loader::db();

      foreach ($files as $key => $file):
      		if ($tags = $file->getAttribute('image_tag')) :
      				foreach($tags->getSelectedOptions() as $value) :
                  $result = $value->getSelectAttributeOptionDisplayValue();
      						$handle = preg_replace('/\s*/', '', strtolower($result));

      						$tagsObject->fileTags[$file->getFileID()][] =  $handle ;
                  $tagsObject->fileTagsName[$file->getFileID()][] =  $result;
      						$tagsObject->tags[$handle] = $result;
      				endforeach;
      		endif ;
      endforeach;
      return $tagsObject;
    }

    function hex2rgb($hex) {
       $hex = str_replace("#", "", $hex);

       if(strlen($hex) == 3) {
          $r = hexdec(substr($hex,0,1).substr($hex,0,1));
          $g = hexdec(substr($hex,1,1).substr($hex,1,1));
          $b = hexdec(substr($hex,2,1).substr($hex,2,1));
       } else {
          $r = hexdec(substr($hex,0,2));
          $g = hexdec(substr($hex,2,2));
          $b = hexdec(substr($hex,4,2));
       }
       $rgb = array($r, $g, $b);
       return implode(",", $rgb); // returns the rgb values separated by commas
       // return $rgb; // returns an array with the rgb values
    }

    function generatePlaceHolderFromArray ($array) {

        $placeholderMaxSize = 600;

        if (!is_object($array[0])) :
            $files = $this->getFilesDetails($array);
        else :
            $files = $array;
        endif;

        foreach ($files as $key => $f) :
            if(!is_object($f)) continue;
            $w = $f->getAttribute('width');
            $h = $f->getAttribute('height');
            $new_width = $placeholderMaxSize;
            $new_height = floor( $h * ( $placeholderMaxSize / $w ) );

            $placeholderFile =  __DIR__ . "/images/placeholders/placeholder-$w-$h.png";
            if (file_exists($placeholderFile)) continue;
            $img = imagecreatetruecolor($new_width,$new_height);
            imagesavealpha($img, true);

            // Fill the image with transparent color
            $color = imagecolorallocatealpha($img,0x00,0x00,0x00,110);
            imagefill($img, 0, 0, $color);

            // Save the image to file.png
            imagepng($img,$placeholderFile);

            // Destroy image
            imagedestroy($img);
        endforeach;
    }
}
