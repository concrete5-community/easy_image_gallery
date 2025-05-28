<?php
defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var int $bID
 * @var Concrete\Package\EasyImageGallery\Options $options
 */

?>
<script>
$(document).ready(function() {
    <?php
    switch ($options->lightbox) {
        case 'lightbox':
            ?>
            $('#easy-gallery-<?= $bID ?> a').fancybox({
                mouseWheel:false,
                nextClick:true,
                helpers: {
                    title: {
                        type : 'inside',
                    },
                },
            });
            <?php
            break;
        case 'intense':
            ?>
            $('#easy-gallery-<?= $bID ?> a').click(function (e) {
                e.preventDefault();
            });
            Intense($('#easy-gallery-<?= $bID ?> a'));
            <?php
            break;
    }
    ?>
});
<?php
if ($options->preloadImages) {
    ?>
    $(window).load(function() {
        var preloadImg = function (arrayOfImages) {
            $(arrayOfImages).each(function() {
                $('<img/>')[0].src = this;
            });
        }
        var arrayOfImages = [];
        $('#easy-gallery-<?= $bID ?>').find('.img.intense').each(function () {
            arrayOfImages.push($(this).data('image'));
        });
        preloadImg(arrayOfImages);
    });
    <?php
}
?>
</script>
