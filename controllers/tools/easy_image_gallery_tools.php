<?php
namespace Concrete\Package\EasyImageGallery\Controller\Tools;

use URL;
use File;
use FileSet;
use Permissions;
use \Concrete\Core\File\EditResponse as FileEditResponse;
use \Concrete\Core\Controller\Controller as RouteController;
use FileAttributeKey;
use Loader;
use Core;
use stdClass;

class EasyImageGalleryTools extends RouteController
{
    public function save()
    {
    $this->file = File::getByID($_REQUEST['fID']);
     $fp = new Permissions($this->file);
        if ($fp->canEditFileProperties()) {
            $fv = $this->file->getVersionToModify();
            $value = $_REQUEST['value'];
            switch($_REQUEST['name']) {
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
                    $ak = FileAttributeKey::getByHandle($_REQUEST['name']);
                    if (is_object($ak)) :
                      $fv->setAttribute($ak,$value);
                    endif;
                  break;
            }

            $sr = new FileEditResponse();
            $sr->setFile($this->file);
            $sr->setMessage(t('File updated successfully.'));
            $sr->setAdditionalDataAttribute('value', $value);
            $sr->setAdditionalDataAttribute('name', $_REQUEST['name']);
            $sr->outputJSON();


        } else {
            throw new Exception(t('Access Denied.'));
        }
        die();
    }

    public function getFileSetImage () {
        // $fs = new SetList()->get();
        // Loader::helper('ajax')->sendResult(
        // die('coucou');
        $fs = FileSet::getByID(intval($_REQUEST['fsID']));
        if (is_object($fs)) $fsf = $fs->getFiles();
        // print_r($fsf);
        if (count($fsf)) :
            foreach ($fsf as $key => $f) :
                $fd = $this->getFileDetails($f,$_REQUEST['fsID']);
                if ($fd) $files[] = $fd;
            endforeach;
            Loader::helper('ajax')->sendResult($files);
        endif;

    }

    public function getFileThumbnailUrl ($f = NULL) {
        if(!$f && $_REQUEST['fID'])
            $f = File::getByID($_REQUEST['fID']);

        $type = \Concrete\Core\File\Image\Thumbnail\Type\Type::getByHandle('file_manager_detail');
        if($type != NULL) {
            return $f->getThumbnailURL($type->getBaseVersion());
        }
        return false;
    }

    public function getFileDetails ($f = NULL, $origin = 'file') {
      // origin peut être "file" pour une simpleimage
      // ou numérique si l'image est extraite d'un FS
        if(!$f && $_REQUEST['fID'])
            $f = File::getByID($_REQUEST['fID']);

        if(!is_object($f)) return false;
        $fv = $f->getVersionToModify();
        $to = $fv->getTypeObject();
        $o  = $fv->getJSONObject();
        // We add the Generic type as number to avoid translating issues
        $o->generic_type = $to->getGenericType();
        // Value fro the image link
        $o->internal_link_cid = $f->getAttribute('internal_link_cid') ? $f->getAttribute('internal_link_cid') : false;
        $o->external_link_url = $f->getAttribute('external_link_url') ? $f->getAttribute('external_link_url') : false;
        $o->link_type = str_replace('<br/>', '', $f->getAttribute('link_type','display'));

        if (is_numeric($origin)) {
          $fs = FileSet::getByID($origin);
          if (!is_object($fs)) :
            $o->originType = 'file';
            $o->fsID = false;
          endif;
          $o->originType = 'fileset';
          $o->fsID = $origin;
          $o->filesetName = $fs->getFileSetName();
        } else {
          $o->originType = 'file';
          $o->fsID = false;
        }
        // $o->urlInline = $this->getFileThumbnailUrl($f);
        // $o->title = $f->getTitle();
        // $o->description = $f->getDescription();
        // $o->fID = $f->getFileID();
        // $o->type = $fv->getGenericTypeText();
        // $o->generic_type = $fv->getGenericType();

        return $o;
    }

    public function getFileDetailsJson () {
         Loader::helper('ajax')->sendResult($this->getFileDetails());
    }

}
