<?php defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
if ($c->isEditMode()) : ?>
    <div class="ccm-edit-mode-disabled-item" style="width: <?php echo $width; ?>; height: <?php echo $height; ?>">
        <div style="padding: 40px 0px 40px 0px"><?php echo t('Easy Gallery disabled in edit mode.')?></div>
    </div>
<?php
elseif (is_array($files) && count($files)) :
$type = \Concrete\Core\File\Image\Thumbnail\Type\Type::getByHandle('file_manager_detail');
?>
    <div class="filtering">
    <?php $this->inc('elements/search.php') ?>
    <?php $this->inc('elements/sortable.php') ?>
    <div class="clear"></div>
    </div>
<div class="easy-gallery easy-gallery-masonry <?php if ($options->lightbox): ?>clickable<?php endif?>" id="easy-gallery-<?php echo $bID?>">
    <div class="e-col-<?php echo $options->galleryColumns?> grid-sizer"></div>
<?php foreach ($files as $key => $f) :
    $galleryHasImage = true;
    $placeHolderUrl = $this->getBlockURL() . "/images/placeholders/placeholder-{$f->getAttribute('width')}-{$f->getAttribute('height')}.png";
    $imageColumn = $f->getAttribute('gallery_columns') ? $f->getAttribute('gallery_columns') : $options->galleryColumns;
    $retinaThumbnailUrl = $f->getThumbnailURL($type->getDoubledVersion());
    $fullUrl = $view->controller->getImageLink($f,$options);
    $tags = isset($tagsObject->fileTags[$f->getFileID()]) ? implode(' ',$tagsObject->fileTags[$f->getFileID()]) : '';
    ?>
    <div class="img masonry-item-collapsed masonry-item e-col-<?php echo $imageColumn ?> <?php echo $tags ?>">
        <?php if($fullUrl) : ?>
          <a href="<?php echo $fullUrl ?>"
            <?php if($options->lightbox) : ?>data-image="<?php echo $fullUrl ?>" data-fancybox-group="gallery-<?php echo $bID ?>" <?php if($options->lightboxTitle) : ?> title="<?php echo $f->getTitle() ?><?php if($options->lightboxDescription) : ?> <?php echo $f->getDescription() ?><?php endif ?>"<?php endif ?><?php endif ?>><?php endif ?>
            <img src="<?php echo $placeHolderUrl ?>" data-original="<?php echo $retinaThumbnailUrl ?>" alt="<?php echo $f->getTitle() ?>">
            <div class="info-wrap">
                <div class="info">
                    <div>
                        <?php if($options->displayDate) : ?><p class="date"><?php echo date($options->dateFormat,$f->getDateAdded()->getTimestamp() )?></p><?php endif ?>
                        <?php if($options->galleryTitle && $f->getTitle()) : ?><h5><?php echo $f->getTitle() ?></h5><?php endif ?>
                        <?php if($options->galleryDescription && $f->getDescription()) : ?><p><small><?php echo $f->getDescription() ?></small></p><?php endif ?>
                    </div>
                </div>
            </div>
        <?php if($fullUrl) : ?></a><?php endif ?>
    </div>
<?php endforeach ?>
    <div class="clear" style="clear:both"></div>
</div><!-- .easy-gallery -->

<?php if($galleryHasImage) : ?>

<?php $this->inc('elements/javascript.php') ?>
<?php $this->inc('elements/masonry.php') ?>
<?php $this->inc('elements/css.php') ?>

<?php endif // galleryHasImage ?>
<?php endif // isEditMode & is_array ?>
