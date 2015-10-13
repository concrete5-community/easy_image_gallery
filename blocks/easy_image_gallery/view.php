<?php defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
$galleryHasImage = false;
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
    <div class="easy-gallery easy-gallery-boxes <?php if($options->lightbox) : ?>clickable<? endif?>" id="easy-gallery-<?php echo $bID?>" data-gutter="1">
        <div class="b-col-<?php echo $options->galleryColumns?> grid-sizer"></div>
        <div class="gutter-sizer"></div>
    <?php foreach ($files as $key => $f) :
        $galleryHasImage = true;
        $imageColumn = $f->getAttribute('gallery_columns') ? $f->getAttribute('gallery_columns') : $options->galleryColumns;
        $placeHolderUrl = $this->getBlockURL() . "/images/placeholders/placeholder-{$f->getAttribute('width')}-{$f->getAttribute('height')}.png";
        $retinaThumbnailUrl = $f->getThumbnailURL($type->getDoubledVersion());
        $fullUrl = $view->controller->getImageLink($f,$options);
        $tags = isset($tagsObject->fileTags[$f->getFileID()]) ? implode(' ',$tagsObject->fileTags[$f->getFileID()]) : '';

        ?>
        <div class="box-wrap masonry-item b-col-<?php echo $imageColumn ?> <?php echo $tags ?>">
            <?php if($fullUrl) : ?><a href="<?php echo $fullUrl ?>" <?php if ($options->lightbox) : ?> data-fancybox-group="easy-gallery-<?php echo $bID?>" data-image="<?php echo $fullUrl ?>" <?php if($options->lightboxTitle) : ?> title="<?php echo $f->getTitle() ?><?php if($options->lightboxDescription) : ?> <?php echo $f->getDescription() ?><?php endif ?>"<?php endif ?> <?php endif ?>><?php endif ?>
                <img src="<?php echo $placeHolderUrl ?>" data-original="<?php echo $retinaThumbnailUrl ?>" alt="<?php echo $f->getTitle() ?>">
            <?php if($fullUrl) : ?></a><?php endif ?>
            <div class="info">
                <?php if($options->displayDate) : ?><p class="date"><?php echo date($options->dateFormat,$f->getDateAdded()->getTimestamp() )?></p><?php endif ?>
                <?php if($options->galleryTitle) : ?><p class="title"><?php echo $f->getTitle() ?></p><?php endif ?>
                <?php if($options->galleryDescription) : ?><p><small><?php echo $f->getDescription() ?></small></p><?php endif ?>
            </div>
        </div>
    <?php endforeach ?>
        <div class="clear" style="clear:both"></div>
    </div><!-- .easy-gallery -->

    <?php if($galleryHasImage) : ?>
    <?php $this->inc('elements/javascript.php') ?>
    <?php $this->inc('elements/masonry.php') ?>
    <?php $this->inc('elements/css.php') ?>
    <?php endif ?>
<?php endif ?>
