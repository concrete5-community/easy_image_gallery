<?php
defined('C5_EXECUTE') or die("Access Denied.")

?><style>
    body .fancybox-overlay{background-color: rgba(<?php echo $this->controller->hex2rgb($options->fancyOverlay) ?>,<?php echo $options->fancyOverlayAlpha ?>);}
    #easy-gallery-<?php echo $bID?> .masonry-item-collapsed .info * {color:<?php echo $options->hoverTitleColor ?> } 	
    #easy-gallery-<?php echo $bID?> .masonry-item-collapsed .info p {border-color:<?php echo $options->hoverTitleColor ?> } 	
    #easy-gallery-<?php echo $bID?> .masonry-item-collapsed {background-color:<?php echo $options->hoverColor ?> } 	
</style>