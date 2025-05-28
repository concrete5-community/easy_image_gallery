<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var int $bID
 * @var Concrete\Package\EasyImageGallery\Options $options
 * @var Concrete\Package\EasyImageGallery\Tags $tagsObject
 */

if ($tagsObject->tags !== [] && $options->filtering) {
    ?>
    <ul class="filter-set" data-filter="filter" id="filter-set-<?= $bID ?>">
        <li><a href="#show-all" data-option-value="*" class="selected rounded"><?= t('show all') ?></a></li>
        <?php
        foreach ($tagsObject->tags as $handle => $tag) {
            ?>
            <li><a href="#<?= h($handle) ?>" data-filter=".<?= h($handle) ?>"><?= h($tag) ?></a></li>
            <?php
        }
        ?>
    </ul>
    <?php
}
