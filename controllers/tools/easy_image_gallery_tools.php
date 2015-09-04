<?php 
namespace Concrete\Package\EasyImageGallery\Controller\Tools;

use URL;
use File;
use FileSet;
use Permissions;
use \Concrete\Core\File\EditResponse as FileEditResponse;
use \Concrete\Core\Controller\Controller as RouteController;

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
            }

            $sr = new FileEditResponse();
            $sr->setFile($this->file);
            $sr->setMessage(t('File updated successfully.'));
            $sr->setAdditionalDataAttribute('value', $value);
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
                $fd = $this->getFileDetails($f); 
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

    public function getFileDetails ($f = NULL) {
        if(!$f && $_REQUEST['fID']) 
            $f = File::getByID($_REQUEST['fID']);

        $o = new stdClass;
        if(!is_object($f)) return false;
        $o->urlInline = $this->getFileThumbnailUrl($f);
        $o->title = $f->getTitle();
        $o->description = $f->getDescription();
        $o->fID = $f->getFileID();
        $o->type = $f->getVersionToModify()->getGenericTypeText();

        return $o;
    }

    public function getFileDetailsJson () {
         Loader::helper('ajax')->sendResult($this->getFileDetails());
    }

}
