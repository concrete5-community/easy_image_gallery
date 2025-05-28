<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Block\View\BlockView $view
 * @var Concrete\Package\EasyImageGallery\Block\EasyImageGallery\Controller $controller
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Form\Service\Widget\Color $colorWidget
 *
 * @var Concrete\Package\EasyImageGallery\Options $options
 * @var Concrete\Core\File\Set\Set[] $fileSets
 * @var array $fDetails
 * @var bool $isComposer
 */

?>
<div id="options">
    <div class="row">
        <div id="options-content" class="ccm-ui col-md-12 options-content">
            <div class="form-group">
                <?= $form->label($view->field('galleryColumns'), t('Number of Columns')) ?>
                <?= $form->select($view->field('galleryColumns'), ['2' => '2', '3' => '3', '4' => '4', '6' => '6'], (string) $options->galleryColumns) ?>
            </div>
            <div class="form-group">
                <?= $form->label($view->field('lightbox'), t('Lightbox')) ?>
                <?= $form->select($view->field('lightbox'), ['0' => t('None'), 'intense' => t('Full Screen'), 'lightbox' => 'Simple Lightbox'], $options->lightbox) ?>
            </div>
            <div class="form-group">
                <?= $form->label('galleryTitle', t('Display Title')) ?>
                <input type="radio" name="<?= $view->field('galleryTitle') ?>" value="1" <?= $options->galleryTitle ? 'checked' : '' ?>> <?= t('Yes') ?>
                <input type="radio" name="<?= $view->field('galleryTitle') ?>" value="0" <?= $options->galleryTitle ? '' : 'checked' ?>> <?= t('No') ?>
                <small><?= t('(On some Templates)') ?></small>
            </div>
            <div class="form-group">
                <?= $form->label('galleryDescription', t('Display Description')) ?>
                <input type="radio" name="<?= $view->field('galleryDescription') ?>" value="1" <?= $options->galleryDescription ? 'checked' : '' ?>> <?= t('Yes') ?>
                <input type="radio" name="<?= $view->field('galleryDescription') ?>" value="0" <?= $options->galleryDescription ? '' : 'checked' ?>> <?= t('No') ?>
                <small><?= t('(On some Templates)') ?></small>
            </div>
            <div class="form-group">
                <?= $form->label('displayDate', t('Display Date')) ?>
                <input type="radio" name="<?= $view->field('displayDate') ?>" value="1" <?= $options->displayDate ? 'checked' : '' ?>> <?= t('Yes') ?>
                <input type="radio" name="<?= $view->field('displayDate') ?>" value="0" <?= $options->displayDate ? '' : 'checked' ?>> <?= t('No') ?>
            </div>
            <hr>
            <div class="form-group">
                <?= $form->label('filtering', t('Display Tags filtering')) ?>
                <input type="radio" name="<?= $view->field('filtering') ?>" value="1" <?= $options->filtering ? 'checked' : '' ?>> <?= t('Yes') ?>
                <input type="radio" name="<?= $view->field('filtering') ?>" value="0" <?= $options->filtering ? '' : 'checked' ?>> <?= t('No') ?>
                <small><?= t('(Attribute "Tags" must be filled on at least one image)') ?></small>
            </div>
            <div class="form-group">
                <?= $form->label('filtering', t('Display Text filtering')) ?>
                <input type="radio" name="<?= $view->field('textFiltering') ?>" value="1" <?= $options->textFiltering ? 'checked' : '' ?>> <?= t('Yes') ?>
                <input type="radio" name="<?= $view->field('textFiltering') ?>" value="0" <?= $options->textFiltering ? '' : 'checked' ?>> <?= t('No') ?>
            </div>
            <hr>
            <div class="form-group">
                <?= $form->label('lightboxTitle', t('Display Title in Lightbox')) ?>
                <input type="radio" name="<?= $view->field('lightboxTitle') ?>" value="1" <?= $options->lightboxTitle ? 'checked' : '' ?>> <?= t('Yes') ?>
                <input type="radio" name="<?= $view->field('lightboxTitle') ?>" value="0" <?= $options->lightboxTitle ? '' : 'checked' ?>> <?= t('No') ?>
            </div>
            <div class="form-group">
                <?= $form->label('lightboxDescription', t('Display Description in Lightbox (Only with full screen)')) ?>
                <input type="radio" name="<?= $view->field('lightboxDescription') ?>" value="1" <?= $options->lightboxDescription ? 'checked' : '' ?>> <?= t('Yes') ?>
                <input type="radio" name="<?= $view->field('lightboxDescription') ?>" value="0" <?= $options->lightboxDescription ? '' : 'checked' ?>> <?= t('No') ?>
            </div>
            <hr>
            <button class="btn btn-primary easy_image_options_close" type="button"><?= t('Close') ?></button>
        </div>
    </div>
</div>

<div id="advanced-options">
    <div class="row">
        <div id="advanced-options-content" class="ccm-ui col-md-12 options-content">
            <div class="form-group">
                <?= $form->label('preloadImages', t('Preload full image')) ?>
                <input type="radio" name="<?= $view->field('preloadImages') ?>" value="1" <?= $options->preloadImages ? 'checked' : '' ?>> <?= t('Yes') ?>
                <input type="radio" name="<?= $view->field('preloadImages') ?>" value="0" <?= $options->preloadImages ? '' : 'checked' ?>> <?= t('No') ?>
            </div>
            <div class="form-group">
                <?= $form->label('fancyOverlay', t('Lightbox overlay color')) ?>
                <?php $colorWidget->output('fancyOverlay', $options->fancyOverlay, ['preferredFormat' => 'rgba']) ?>
            </div>
            <div class="form-group">
                <?= $form->label('fancyOverlayAlpha', t('Lightbox overlay opacity (from 0 to 1)')) ?>
                <?= $form->number('fancyOverlayAlpha', $options->fancyOverlayAlpha, ['min' => '0', 'max' => '1', 'step' => '0.001']) ?>
            </div>
            <div class="form-group">
                <?= $form->label('hoverColor', t('Hover Image Color')) ?><small><?= t('(On some Templates)') ?></small>
                <?php $colorWidget->output('hoverColor', $options->hoverColor, ['preferredFormat'=>'rgba']) ?>
            </div>
            <div class="form-group">
                <?= $form->label('hoverTitleColor', t('Hover Image Title Color')) ?><small><?= t('(On some Templates)') ?></small>
                <?php $colorWidget->output('hoverTitleColor', $options->hoverTitleColor, ['preferredFormat' => 'rgba']) ?>
            </div>
            <div class="form-group">
                <?= $form->label('dateFormat', t('Date Format (PHP date format)')) ?>
                <?= $form->text('dateFormat', $options->dateFormat) ?>
            </div>
            <hr>
            <button class="btn btn-primary easy_image_options_close" type="button"><?= t('Close') ?></button>
        </div>
    </div>
</div>
