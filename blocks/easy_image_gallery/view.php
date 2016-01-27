<?php
defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
extract ($controller->getGallery($b, $this));
if (!$c->isEditMode()) :
  echo $wrapperOpenTag;
  foreach ($files as $key => $file): extract($file->details);
  	echo $itemOpenTag;?>
    <?php if($fullUrl) : ?><a <?php echo $to ?>><?php endif ?>
        <img src="<?php echo $placeHolderUrl ?>" data-original="<?php echo $retinaThumbnailUrl ?>" alt="<?php echo $file->getTitle() ?>">
    <?php if($fullUrl) : ?></a><?php endif ?>
    <div class="info">
        <?php if($options->displayDate) : ?><p class="date"><?php echo date($options->dateFormat,$f->getDateAdded()->getTimestamp() )?></p><?php endif ?>
        <?php if($options->galleryTitle) : ?><p class="title"><?php echo $f->getTitle() ?></p><?php endif ?>
        <?php if($options->galleryDescription) : ?><p><small><?php echo $f->getDescription() ?></small></p><?php endif ?>
    </div>
    <?php echo $itemCloseTag ?>
  <?php endforeach ?>
  <?php $this->inc('elements/javascript.php') ?>
  <?php $this->inc('elements/masonry.php') ?>
  <?php $this->inc('elements/css.php') ?>
<?php endif ?>
