<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var int $bID
 * @var Concrete\Package\EasyImageGallery\Block\EasyImageGallery\Controller $controller
 * @var Concrete\Package\EasyImageGallery\Options $options
 */

?>
<style>
body .fancybox-overlay {
    background-color: <?= $options->getFancyOverlayCSSColor() ?>;
}
#easy-gallery-<?= $bID ?> .masonry-item-collapsed .info * {
    color: <?= $options->hoverTitleColor ?>;
}
#easy-gallery-<?= $bID ?> .masonry-item-collapsed .info p {
    border-color: <?= $options->hoverTitleColor ?>;
}
#easy-gallery-<?= $bID ?> .masonry-item-collapsed {
    background-color: <?= $options->hoverColor ?>;
}
</style>
