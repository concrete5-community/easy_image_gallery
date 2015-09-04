<?php
defined('C5_EXECUTE') or die("Access Denied.");

?><script>
if (window.devicePixelRatio >= 2) {
    // Retina foo
} 
$(document).ready(function(){
<?php if($options->lightbox == 'lightbox') : ?>
    $('#easy-gallery-<?php echo $bID?> a').fancybox({mouseWheel:false, nextClick:true, helpers : {title : {type : 'inside'}}});
<?php elseif ($options->lightbox == 'intense') : ?>
    $('#easy-gallery-<?php echo $bID?> a').click(function(e){e.preventDefault()});
    Intense($('#easy-gallery-<?php echo $bID?> a'));
<?php endif ?>

});
<?php if ($options->preloadImages) : ?>    
$(window).load(function(){
    var preloadImg = function (arrayOfImages) {
        $(arrayOfImages).each(function(){
            $('<img/>')[0].src = this;
        });
    }
    var arrayOfImages = new Array();
    $("#easy-gallery-<?php echo $bID?>").find('.img.intense').each(function(){arrayOfImages.push($(this).data('image'))});
    preloadImg (arrayOfImages);
});
<?php endif ?> 
</script>