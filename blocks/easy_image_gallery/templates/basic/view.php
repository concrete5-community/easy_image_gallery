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
<div class="easy-gallery easy-gallery-boxes easy-gallery-boxes-basic<?= $options->lightbox ? ' clickable' : '' ?>" id="easy-gallery-<?= $bID ?>">
    <div class="b-col-<?= $options->galleryColumns ?> grid-sizer"></div>
    <?php
    foreach ($files as $index => $file) {
        $fileVersion = $file->getApprovedVersion();
        $thumbnailUrl = $type ? $fileVersion->getThumbnailURL($type->getBaseVersion()) : '';
        $retinaThumbnailUrl = $type ? $fileVersion->getThumbnailURL($type->getDoubledVersion()) : '';
        $fullUrl = $controller->getImageLink($file, $options);
        $tag = $fullUrl || $options->lightbox ? 'a' : 'span';
        $w = (int) $fileVersion->getAttribute('width');
        $h = (int) $fileVersion->getAttribute('height');
        $ratio = $w > $h ? 'horizontal' : ($w === $h ? 'square' : 'vertical');
        if ($index % $options->galleryColumns === 0) {
            ?>
            <div class="row">
            <?php
        }
        ?>
        <div class="box-wrap b-col-<?= $options->galleryColumns ?> gutter">
            <<?= $tag ?>
                class="img <?= $ratio ?>"
                style="background-image:url(<?= $retinaThumbnailUrl ?>); background-image: -webkit-image-set(url(<?= $thumbnailUrl ?>) 1x, url(<?= $retinaThumbnailUrl ?>) 2x);"
                <?php
                if ($fullUrl) {
                    ?>
                    href="<?= $fullUrl ?>"
                    <?php
                }
                if ($options->lightbox) {
                    ?>
                    data-image="<?= $fullUrl ?>"
                    data-fancybox-group="gallery-<?= $bID ?>"
                    <?php
                    if ($options->lightboxTitle) {
                        ?>
                        title="<?= $fileVersion->getTitle() ?> <?= $options->lightboxDescription &&  $fileVersion->getDescription() ? " - {$fileVersion->getDescription()}" : '' ?>"
                        <?php
                    }
                }
                ?>
            ></<?= $tag ?>>
            <div class="loader"><i class="fa fa-circle-o-notch fa-spin"></i></div>
            <div class="info">
                <?php
                if ($options->displayDate) {
                    ?>
                    <p class="date"><?= date($options->dateFormat, $file->getDateAdded()->getTimestamp() ) ?></p>
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
        if ($index % $options->galleryColumns === $options->galleryColumns - 1 || $index === count($files) - 1) {
            ?>
            </div>
            <?php
        }
    }
    ?>
    <div class="clear" style="clear:both"></div>
</div>
<?php
$view->inc('elements/javascript.php');
$view->inc('elements/css.php');
