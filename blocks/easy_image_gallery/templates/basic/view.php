<?php defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
$galleryHasImage = false;
$type = \Concrete\Core\File\Image\Thumbnail\Type\Type::getByHandle('file_manager_detail');

if (is_array($files) && count($files)) :
?>
    <div class="easy-gallery easy-gallery-boxes easy-gallery-boxes-basic <?php if($options->lightbox) : ?>clickable<? endif?>" id="easy-gallery-<?php echo $bID?>">
        <div class="b-col-<?php echo $options->galleryColumns?> grid-sizer"></div>
    <?php foreach ($files as $key => $f) :
        // $f = File::getByID($fID);
        if(!is_object($f)) continue;
        $galleryHasImage = true;
        $thumbnailUrl = $f->getThumbnailURL($type->getBaseVersion());
        $imageColumn = $f->getAttribute('gallery_columns') ? $f->getAttribute('gallery_columns') : $options->galleryColumns;
        $retinaThumbnailUrl = $f->getThumbnailURL($type->getDoubledVersion());
        if (!$options->lightbox) :
          $internal_link = Page::getByID($f->getAttribute('internal_link_cid'), 'ACTIVE');
          $external_link_url = $f->getAttribute('external_link_url');
          $fullUrl = $internal_link ? $internal_link->getCollectionLink() : ($external_link_url ? $external_link_url : false) ;
        else :
          $fullUrl = $f->getRelativePath();
        endif;
        $ratio = $f->getAttribute('image_ratio');
        $w = intval($f->getAttribute('width'));
        $h = intval($f->getAttribute('height'));
        $ratio = $w > $h ? 'horizontal' : ($w == $h ? 'square' : 'vertical');
        ?>
        <?php if ($key%$options->galleryColumns == 0) : ?><div class="row"><?php endif ?>
        <div class="box-wrap b-col-<?php echo $options->galleryColumns?> gutter">
            <a class="img <?php echo $ratio ?>"
                style="background-image:url(<?php echo $retinaThumbnailUrl ?>); background-image: -webkit-image-set(url(<?php echo $thumbnailUrl ?>) 1x, url(<?php echo $retinaThumbnailUrl ?>) 2x);"
                href="<?php echo $fullUrl ?>"
                 <?php if($options->lightbox) : ?>
                data-image="<?php echo $fullUrl ?>"
                data-fancybox-group="gallery-<?php echo $bID ?>"
                <?php if($options->lightboxTitle) : ?> title="<?php echo $f->getTitle() ?> <?php if($options->lightboxDescription &&  $f->getDescription()) echo " - " . $f->getDescription(); ?>" <?php endif ?>
                <?php endif ?>
                >

            </a>
            <div class="loader"><i class="fa fa-circle-o-notch fa-spin"></i></div>

            <div class="info">
                <?php if($options->displayDate) : ?><p class="date"><?php echo date($options->dateFormat,$f->getDateAdded()->getTimestamp() )?></p><?php endif ?>
                <?php if($options->galleryTitle) : ?><p class="title"><?php echo $f->getTitle() ?></p><?php endif ?>
                <?php if($options->galleryDescription) : ?><p><small><?php echo $f->getDescription() ?></small></p><?php endif ?>
            </div>
        </div>
       <?php if ( $key%$options->galleryColumns == ($options->galleryColumns) - 1 || ($key == count($fIDs)-1) ) : ?></div><?php endif ?>
    <?php endforeach ?>
        <div class="clear" style="clear:both"></div>
    </div><!-- .easy-gallery -->

    <?php if($galleryHasImage) : ?>
    <?php $this->inc('elements/javascript.php') ?>
    <?php $this->inc('elements/css.php') ?>
    <?php endif ?>
<?php endif ?>
