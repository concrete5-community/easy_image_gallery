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
    <?php
    $view->inc('elements/search.php');
    $view->inc('elements/sortable.php');
    ?>
    <div class="clear"></div>
</div>
<div class="easy-gallery easy-gallery-masonry<?= $options->lightbox ? ' clickable' : '' ?>" id="easy-gallery-<?= $bID ?>">
    <div class="e-col-<?= $options->galleryColumns ?> grid-sizer"></div>
    <?php
    foreach ($files as $file) {
        $fileVersion = $file->getApprovedVersion();
        $placeHolderUrl = $controller->getPlaceholderUrl($fileVersion);
        $imageColumn = ((int) $fileVersion->getAttribute('gallery_columns')) ?: $options->galleryColumns;
        $retinaThumbnailUrl = $type ? $fileVersion->getThumbnailURL($type->getDoubledVersion()) : '';
        $fullUrl = $controller->getImageLink($file, $options);
        $tags = isset($tagsObject->fileTags[$file->getFileID()]) ? implode(' ', $tagsObject->fileTags[$file->getFileID()]) : '';
        ?>
        <div class="img masonry-item-collapsed masonry-item e-col-<?= $imageColumn ?> <?= $tags ?>">
            <?php
            if ($fullUrl) {
                ?>
                <a
                    href="<?= $fullUrl ?>"
                    <?php
                    if ($options->lightbox) {
                        ?>
                        data-image="<?= $fullUrl ?>"
                        data-fancybox-group="gallery-<?= $bID ?>"
                        <?php
                        if ($options->lightboxTitle) {
                            ?>
                            title="<?= $fileVersion->getTitle() ?><?= $options->lightboxDescription ? " {$fileVersion->getDescription()}" :'' ?>"
                            <?php
                        }
                    }
                    ?>
                >
                <?php
            }
            ?>
            <img src="<?= $placeHolderUrl ?>" data-original="<?= $retinaThumbnailUrl ?>" alt="<?= $fileVersion->getTitle() ?>" />
            <div class="info-wrap">
                <div class="info">
                    <div>
                        <?php
                        if ($options->displayDate) {
                            ?>
                            <p class="date"><?= date($options->dateFormat, $file->getDateAdded()->getTimestamp() ) ?></p>
                            <?php
                        }
                        if ($options->galleryTitle && $fileVersion->getTitle()) {
                            ?>
                            <h5><?= $fileVersion->getTitle() ?></h5>
                            <?php
                        }
                        if ($options->galleryDescription && $fileVersion->getDescription()) {
                            ?>
                            <p><small><?= $fileVersion->getDescription() ?></small></p>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
            if ($fullUrl) {
                ?>
                </a>
                <?php
            }
            ?>
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
