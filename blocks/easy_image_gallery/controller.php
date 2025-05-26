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
        if(strpos($value,'fsID') === 0 ): // Le fID commence par "fsID" DOnc on va extraire les images
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

    public function view() {
        $time_start = microtime(true);
        $options =  $this->getOptionsJson();

        // Files
        $fIDs = $this->getFilesDetails(false,false);
        $files = array_filter(array_map(array($this,'getFileFromFileID') , $fIDs));
        $this->set('fIDs', $fIDs);
        $this->set('selectedFilesets', $this->getSelectedFilesets());
        $this->set('files',$files );
        $this->set('options', $options );
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
        $tagsObject = new stdClass();
        $tags = $tagsObject->fileTags = array();
        $ak = FileAttributeKey::getByHandle('image_tag');

        $db = Loader::db();

        foreach ($files as $file):
            if(!is_object($file)) continue;
            if ($file->getAttribute('image_tag')) :

                $v = array($file->getFileID(), $file->getFileVersionID(), $ak->getAttributeKeyID());
                $avID = $db->GetOne("SELECT avID FROM FileAttributeValues WHERE fID = ? AND fvID = ? AND akID = ?", $v);
                if (!$avID) continue;

                $query = $db->GetAll("
                    SELECT opt.value
                    FROM atSelectOptions opt,
                    atSelectOptionsSelected sel

                    WHERE sel.avID = ?
                    AND sel.atSelectOptionID = opt.ID",$avID);


                foreach($query as $opt) {
                    $handle = preg_replace('/\s*/', '', strtolower($opt['value']));
                    // var_dump($opt);
                    $tagsObject->fileTags[$file->getFileID()][] = $handle;
                    $tagsObject->tags[$handle] = $opt['value'];
                }
                // echo "---------------\n";

            endif ;
        endforeach;
        // die();
        $time_end = microtime(true);
        // $this->set('tags', array_unique($tags));
        $this->set('tagsObject', $tagsObject);
        $this->set('tags_processing_time', ($time_end - $time_start)/60);

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
        if (is_array($filesetIDAndFiles)):
          foreach ($filesetIDAndFiles as $fsID => $arrayOffID):
            $set = \Concrete\Core\File\Set\Set::getByID($fsID);
            $set->updateFileSetDisplayOrder($arrayOffID);
          endforeach;
        endif;


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
