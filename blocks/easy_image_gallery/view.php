<?php

use Concrete\Core\File\Image\Thumbnail\Type\Type as ThumbnailType;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Block\View\BlockView $view
 * @var Concrete\Package\EasyImageGallery\Block\EasyImageGallery\Controller $controller
 * @var int $bID
 *
 * @var bool $editMode
 * @var Concrete\Core\Localization\Localization|null $localization set only if $editMode is true
 * @var Concrete\Core\Entity\File\File[] $files
 * @var Concrete\Package\EasyImageGallery\Options $options
 * @var Concrete\Package\EasyImageGallery\Tags $tagsObject
 */

if ($editMode) {
    $localization->withContext($localization::CONTEXT_UI, static function() {
        ?>
        <div class="ccm-edit-mode-disabled-item" style="padding: 40px 0px 40px 0px">
            <?= t('Easy Gallery disabled in edit mode.') ?>
        </div>
        <?php
    });
    return;
}
if ($files === []) {
    return;
}
$type = ThumbnailType::getByHandle('file_manager_detail');
?>
<div class="filtering">
    <?php $view->inc('elements/search.php') ?>
    <?php $view->inc('elements/sortable.php') ?>
    <div class="clear"></div>
</div>
<div class="easy-gallery easy-gallery-boxes<?= $options->lightbox ? ' clickable' : '' ?>" id="easy-gallery-<?= $bID ?>" data-gutter="1">
    <div class="b-col-<?= $options->galleryColumns ?> grid-sizer"></div>
    <div class="gutter-sizer"></div>
    <?php
    foreach ($files as $file) {
        $fileVersion = $file->getApprovedVersion();
        $imageColumn = ((int) $fileVersion->getAttribute('gallery_columns')) ?: $options->galleryColumns;
        $placeHolderUrl = $controller->getPlaceholderUrl($fileVersion);
        $retinaThumbnailUrl = $type ? $fileVersion->getThumbnailURL($type->getDoubledVersion()) : '';
        $fullUrl = $controller->getImageLink($file, $options);
        $tags = isset($tagsObject->fileTags[$file->getFileID()]) ? implode(' ', $tagsObject->fileTags[$file->getFileID()]) : '';
        ?>
        <div class="box-wrap masonry-item b-col-<?= $imageColumn ?> <?= $tags ?>">
            <?php
            if ($fullUrl) {
                ?>
                <a href="<?= $fullUrl ?>"
                    <?php
                    if ($options->lightbox) {
                        ?>
                        data-fancybox-group="easy-gallery-<?= $bID ?>" data-image="<?= $fullUrl ?>"
                        <?php
                        if ($options->lightboxTitle) {
                            ?>
                            title="<?= $fileVersion->getTitle() ?><?= $options->lightboxDescription ? $fileVersion->getDescription() : '' ?>"
                            <?php
                        }
                    }
                    ?>
                >
                <?php
            }
            ?>
            <img src="<?= $placeHolderUrl ?>" data-original="<?= $retinaThumbnailUrl ?>" alt="<?= $fileVersion->getTitle() ?>" />
            <?php
            if ($fullUrl) {
                ?>
                </a>
                <?php
            }
            ?>
            <div class="info">
                <?php
                if ($options->displayDate) {
                    ?>
                    <p class="date"><?= date($options->dateFormat, $file->getDateAdded()->getTimestamp()) ?></p>
                    <?php
                }
                if ($options->galleryTitle) {
                    ?>
                    <p class="title"><?= $fileVersion->getTitle() ?></p>
                    <?php
                }
                if ($options->galleryDescription) {
                    ?>
                    <p><small><?= $fileVersion->getDescription() ?></small></p>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="clear" style="clear:both"></div>
</div>
<?php
$view->inc('elements/javascript.php');
$view->inc('elements/masonry.php');
$view->inc('elements/css.php');
