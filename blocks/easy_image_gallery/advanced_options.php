<?php defined('C5_EXECUTE') or die("Access Denied.");
// In Composer conditions, it seems that the 'options' variables are overide after the inc() function
// So i need to set some variable here... Don't know why ?
$options = $controller->getOptionsJson();
?>
<div id="options" class="">
    <div class="row">
        <div id="options-content" class="ccm-ui col-md-12 options-content">                             
            <div class="form-group">
                <?php echo $form->label($view->field('galleryColumns'), t('Number of Columns')); ?>
                <?php  echo $form->select($view->field('galleryColumns'), array(2 => '2', 3 => '3', 4 => '4', 6 => '6'), $options->galleryColumns); ?>            
            </div>        
            <div class="form-group">
                <?php echo $form->label($view->field('lightbox'), t('Lightbox')); ?>
                <?php  echo $form->select($view->field('lightbox'), array('0' => t('None'), 'intense' => t('Full Screen'), 'lightbox' => 'Simple Lightbox'  ), $options->lightbox); ?>
            </div>
            <div class="form-group">
                <?php echo $form->label('galleryTitle', t('Display Title')); ?>
                <input type="radio" name="<?php echo $view->field('galleryTitle')?>" value="1" <?php echo $options->galleryTitle == 1 ? 'checked' : '' ?>> <?php echo t('Yes') ?>
                <input type="radio" name="<?php echo $view->field('galleryTitle')?>" value="0" <?php echo $options->galleryTitle == 0 ? 'checked' : '' ?>> <?php echo t('No') ?>
                <small><?php echo t('(On some Templates)') ?></small>
            </div>
            <div class="form-group">
                <?php echo $form->label('galleryDescription', t('Display Description')); ?>
                <input type="radio" name="<?php echo $view->field('galleryDescription')?>" value="1" <?php echo $options->galleryDescription == 1 ? 'checked' : '' ?>> <?php echo t('Yes') ?>
                <input type="radio" name="<?php echo $view->field('galleryDescription')?>" value="0" <?php echo $options->galleryDescription == 0 ? 'checked' : '' ?>> <?php echo t('No') ?>
                <small><?php echo t('(On some Templates)') ?></small>
            </div>
            <div class="form-group">                
                <?php echo $form->label('displayDate', t('Display Date')); ?>
                <input type="radio" name="<?php echo $view->field('displayDate')?>" value="1" <?php echo $options->displayDate == 1 ? 'checked' : '' ?>> <?php echo t('Yes') ?>
                <input type="radio" name="<?php echo $view->field('displayDate')?>" value="0" <?php echo $options->displayDate == 0 ? 'checked' : '' ?>> <?php echo t('No') ?>
            </div>
            <hr>
            <div class="form-group">                
                <?php echo $form->label('filtering', t('Display Tags filtering')); ?>
                <input type="radio" name="<?php echo $view->field('filtering')?>" value="1" <?php echo $options->filtering == 1 ? 'checked' : '' ?>> <?php echo t('Yes') ?>
                <input type="radio" name="<?php echo $view->field('filtering')?>" value="0" <?php echo $options->filtering == 0 ? 'checked' : '' ?>> <?php echo t('No') ?>
                <small><?php echo t('(Attribute "Tags" must be filled on at least one image)') ?></small>
            </div>
            <div class="form-group">                
                <?php echo $form->label('filtering', t('Display Text filtering')); ?>
                <input type="radio" name="<?php echo $view->field('textFiltering')?>" value="1" <?php echo $options->textFiltering == 1 ? 'checked' : '' ?>> <?php echo t('Yes') ?>
                <input type="radio" name="<?php echo $view->field('textFiltering')?>" value="0" <?php echo $options->textFiltering == 0 ? 'checked' : '' ?>> <?php echo t('No') ?>
            </div>
            <hr>
            <div class="form-group">
                <?php echo $form->label('lightboxTitle', t('Display Title in Lightbox')); ?>
                <input type="radio" name="<?php echo $view->field('lightboxTitle')?>" value="1" <?php echo $options->lightboxTitle == 1 ? 'checked' : '' ?>> <?php echo t('Yes') ?>
                <input type="radio" name="<?php echo $view->field('lightboxTitle')?>" value="0" <?php echo $options->lightboxTitle == 0 ? 'checked' : '' ?>> <?php echo t('No') ?>
            </div>
            <div class="form-group">
                <?php echo $form->label('lightboxDescription', t('Display Description in Lightbox (Only with full screen)')); ?>
                <input type="radio" name="<?php echo $view->field('lightboxDescription')?>" value="1" <?php echo $options->lightboxDescription == 1 ? 'checked' : '' ?>> <?php echo t('Yes') ?>
                <input type="radio" name="<?php echo $view->field('lightboxDescription')?>" value="0" <?php echo $options->lightboxDescription == 0 ? 'checked' : '' ?>> <?php echo t('No') ?>
            </div>
            <hr>
            <button class="btn btn-primary easy_image_options_close" type="button" id=""><?php echo t('Close')?></button>   
        </div>
    </div>
</div>

<div id="advanced-options" class="">
    <div class="row">
        <div id="advanced-options-content" class="ccm-ui col-md-12 options-content">                             
            <div class="form-group">                
                <?php echo $form->label('preloadImages', t('Preload full image')); ?>
                <input type="radio" name="<?php echo $view->field('preloadImages')?>" value="1" <?php echo $options->preloadImages == 1 ? 'checked' : '' ?>> <?php echo t('Yes') ?>
                <input type="radio" name="<?php echo $view->field('preloadImages')?>" value="0" <?php echo $options->preloadImages == 0 ? 'checked' : '' ?>> <?php echo t('No') ?>
            </div>
            <div class="form-group">
                <?php echo $form->label('fancyOverlay', t('Lightbox overlay color')); ?>
                <?php $col = new Concrete\Core\Form\Service\Widget\Color(); $col->output('fancyOverlay',$options->fancyOverlay,array('preferredFormat'=>'rgba')) ?>
            </div>
            <div class="form-group">
                <?php echo $form->label('fancyOverlayAlpha', t('Lightbox overlay opacity (from 0 to 1)')); ?>
                <?php echo $form->text('fancyOverlayAlpha',$options->fancyOverlayAlpha) ?>
            </div>
            <div class="form-group">
                <?php echo $form->label('hoverColor', t('Hover Image Color')); ?><small><?php echo t('(On some Templates)') ?></small>
                <?php $col = new Concrete\Core\Form\Service\Widget\Color(); $col->output('hoverColor',$options->hoverColor,array('preferredFormat'=>'rgba')) ?>
            </div>
            <div class="form-group">
                <?php echo $form->label('hoverTitleColor', t('Hover Image Title Color')); ?><small><?php echo t('(On some Templates)') ?></small>
                <?php $col = new Concrete\Core\Form\Service\Widget\Color(); $col->output('hoverTitleColor',$options->hoverTitleColor,array('preferredFormat'=>'rgba')) ?>
            </div>
          
            <div class="form-group">
                <?php echo $form->label('dateFormat', t('Date Format (PHP date format)')); ?>
                <?php echo $form->text('dateFormat',$options->dateFormat) ?>
            </div>                     
            
            <hr>
            <button class="btn btn-primary easy_image_options_close" type="button" id=""><?php echo t('Close')?></button>   
        </div>
    </div>
</div>
