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
<div class="easy-gallery easy-gallery-masonry <?php if ($options->lightbox): ?>clickable<? endif?>" id="easy-gallery-<?php echo $bID?>">
    <div class="e-col-<?php echo $options->galleryColumns?> grid-sizer"></div>
<?php foreach ($files as $key => $f) :
    $galleryHasImage = true;
    $placeHolderUrl = $this->getBlockURL() . "/images/placeholders/placeholder-{$f->getAttribute('width')}-{$f->getAttribute('height')}.png";
    $imageColumn = $f->getAttribute('gallery_columns') ? $f->getAttribute('gallery_columns') : $options->galleryColumns;
    $retinaThumbnailUrl = $f->getThumbnailURL($type->getDoubledVersion());
    $fullUrl = $view->controller->getImageLink($f,$options);
    $tags = isset($tagsObject->fileTags[$f->getFileID()]) ? implode(' ',$tagsObject->fileTags[$f->getFileID()]) : '';
    ?>
    <div class="masonry-item e-col-<?php echo $imageColumn ?> <?php echo $tags ?>">
        <img src="<?php echo $placeHolderUrl ?>" data-original="<?php echo $retinaThumbnailUrl ?>" alt="<?php echo $f->getTitle() ?>">
        <figure class="curtain">
          <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/331810/sample77.jpg" alt="sample77"/>
          <?php if (($options->galleryTitle && $f->getTitle()) || ($options->galleryDescription && $f->getDescription())) ?>
          <figcaption>
            <?php if($options->galleryTitle && $f->getTitle()) : ?><h2><?php echo $f->getTitle() ?></h2><?php endif ?>
            <?php if($options->galleryDescription && $f->getDescription()) : ?><p><?php echo $f->getDescription() ?></p><?php endif ?>
          </figcaption>
        <?php endif ?>
          <?php if($fullUrl) : ?><a href="<?php echo $fullUrl ?>" <?php if($options->lightbox) : ?>data-image="<?php echo $fullUrl ?>" data-fancybox-group="gallery-<?php echo $bID ?>" <?php if($options->lightboxTitle) : ?> title="<?php echo $f->getTitle() ?><?php if($options->lightboxDescription) : ?> <?php echo $f->getDescription() ?><?php endif ?>"<?php endif ?><?php endif ?>></a><?php endif ?>
        </figure>
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
