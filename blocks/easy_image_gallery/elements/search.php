<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\EasyImageGallery\Options $options
 */

if ($options->textFiltering) {
    ?>
    <input type="text" id="quicksearch" placeholder="<?= t('Search on Title') ?>" />
    <?php
}
