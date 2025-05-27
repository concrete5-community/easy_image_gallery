<?php

namespace Concrete\Package\EasyImageGallery\Controller\Tools;

use Concrete\Core\Attribute\Category\FileCategory;
use Concrete\Core\Controller\Controller as RouteController;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\EditResponse as FileEditResponse;
use Concrete\Core\File\File;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\Permission\Checker;

class EasyImageGalleryTools extends RouteController
{
    public function save()
    {
        $fID = (int) $this->request->get('fID');
        $file = $fID > 0 ? File::getByID(fID) : null;
        if (!$file) {
            throw new UserMessageException(t('File not found.'));
        }
        $fp = new Checker($file);
        if (!$fp->canEditFileProperties()) {
            throw new UserMessageException(t('Access Denied.'));
        }
        $fv = $file->getVersionToModify();
        $name = $this->request->get('name');
        $value = $this->request->get('value');
        switch ($this->request->get('name')) {
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
        $this->app->make('helper/ajax')->sendResult($sr);
    }

    public function getFileSetImage()
    {
        $files = [];
        $fsID = (int) $this->request->get('fsID');
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
        $this->app->make('helper/ajax')->sendResult($files);
    }

    /**
     * @param \Concrete\Core\Entity\File\File|null $f
     */
    public function getFileThumbnailUrl($f = null)
    {
        if (!$f) {
            $fID = (int) $this->request->get('fID');
            $f = $fID > 0 ? File::getByID($fID) : null;
        }
        $fv = $f ? $f->getApprovedVersion() : null;
        if ($fv) {
            $type = \Concrete\Core\File\Image\Thumbnail\Type\Type::getByHandle('file_manager_detail');
            if ($type != null) {
                return $fv->getThumbnailURL($type->getBaseVersion());
            }
        }

        return false;
    }

    /**
     * @param \Concrete\Core\Entity\File\File|null $f
     * @param string|int $origin
     */
    public function getFileDetails($f = null, $origin = 'file')
    {
        if (!$f) {
            $fID = (int) $this->request->get('fID');
            $f = $fID > 0 ? File::getByID($fID) : null;
        }
        $fv = $f ? $f->getVersionToModify() : null;
        if (!$fv) {
            return false;
        }
        $to = $fv->getTypeObject();
        $o = $fv->getJSONObject();
        // We add the Generic type as number to avoid translating issues
        $o->generic_type = $to->getGenericType();
        // Value fro the image link
        $o->internal_link_cid = $f->getAttribute('internal_link_cid') ? $f->getAttribute('internal_link_cid') : false;
        $o->external_link_url = $f->getAttribute('external_link_url') ? $f->getAttribute('external_link_url') : false;
        $o->link_type = str_replace('<br/>', '', $f->getAttribute('link_type', 'display'));
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

    public function getFileDetailsJson()
    {
        $this->app->make('helper/ajax')->sendResult($this->getFileDetails());
    }
}
