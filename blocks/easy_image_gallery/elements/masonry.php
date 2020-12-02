<?php defined('C5_EXECUTE') or die("Access Denied.")?>
<script> 
try{Typekit.load();}catch(e){}
$(document).ready(function(){
    var $win = $(window),
        $imgs = $("#easy-gallery-<?php echo $bID?> img"),
        $container = $("#easy-gallery-<?php echo $bID?>"),
        masonryOptions = {columnWidth: '.grid-sizer'},
        // quick search regex
        qsRegex = false; 
        // If a div.gutter-sizer is present, we add it to the option, otherwise the plugin doesn't work
        if ($("#easy-gallery-<?php echo $bID?> .gutter-sizer").size()) masonryOptions.gutter = '.gutter-sizer';

        $container.imagesLoaded(function(){
            $isotope = $container.isotope({ masonry: masonryOptions,
        						itemSelector: '.masonry-item'
        						}
                        );
    	    $isotope.isotope('on', 'layoutComplete', function (items) {
    	        loadVisible($imgs, 'lazylazy');
    	    });

    	    $win.on('scroll', function () {
    	        loadVisible($imgs, 'lazylazy');
                $isotope.isotope('layout');
    	    });

    	    $imgs.lazyload({
    	        effect: "fadeIn",
    	        failure_limit: Math.max($imgs.length - 1, 0),
    	        event: 'lazylazy'
    	    });
            $isotope.isotope('layout');
    });

    function loadVisible($els, trigger) {
        $els.filter(function () {
            var rect = this.getBoundingClientRect();
            return rect.top >= 0 && rect.top <= window.innerHeight;
        }).trigger(trigger);
    }

  // use value of search field to filter
    var $quicksearch = $('#quicksearch').keyup(debounce(searchFilter));
      
    function searchFilter() {
        qsRegex = new RegExp($quicksearch.val(), 'gi');
        $container.isotope({
            filter: function () {
                return qsRegex ? $(this).text().match(qsRegex) : true;
            }
        });
    }    

    // debounce so filtering doesn't happen every millisecond
    function debounce( fn, threshold ) {
      var timeout;
      return function debounced() {
        if ( timeout ) {
          clearTimeout( timeout );
        }
        function delayed() {
          fn();
          timeout = null;
        }
        timeout = setTimeout( delayed, threshold || 100 );
      }
    }


    $('#filter-set-<?php echo $bID?>').on('click', 'a', function(e) {e.preventDefault();var filterValue = $(this).attr('data-filter');$container.isotope({ filter: filterValue })});    
});
</script>
