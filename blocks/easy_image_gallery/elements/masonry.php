<?php
defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var int $bID
 */

?>
<script>
try {
    Typekit.load();
} catch(e) {
}
$(document).ready(function () {
    var $win = $(window),
        $imgs = $('#easy-gallery-<?= $bID ?> img'),
        $container = $('#easy-gallery-<?= $bID ?>'),
        masonryOptions = {columnWidth: '.grid-sizer'};

    if ($('#easy-gallery-<?= $bID ?> .gutter-sizer').length) {
        masonryOptions.gutter = '.gutter-sizer';
    }
    $container.imagesLoaded(function () {
        $isotope = $container.isotope({
            masonry: masonryOptions,
            itemSelector: '.masonry-item',
        });
        $isotope.isotope('on', 'layoutComplete', function (items) {
            loadVisible($imgs, 'lazylazy');
        });
        $win.on('scroll', function () {
            loadVisible($imgs, 'lazylazy');
        });
        $imgs.lazyload({
            effect: 'fadeIn',
            failure_limit: Math.max($imgs.length - 1, 0),
            event: 'lazylazy',
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
        var qsRegex = new RegExp($quicksearch.val(), 'gi');
        $container.isotope({
            filter: function () {
                return qsRegex ? $(this).text().match(qsRegex) : true;
            },
        });
    }

    function debounce(fn, threshold) {
        var timeout;
        return function () {
            if (timeout) {
                clearTimeout(timeout);
            }
            function delayed() {
                fn();
                timeout = null;
            }
            timeout = setTimeout(delayed, threshold || 100);
        }
    }

    $('#filter-set-<?= $bID ?>').on('click', 'a', function(e) {
        e.preventDefault();
        var filterValue = $(this).attr('data-filter');
        $container.isotope({filter: filterValue});
    });
});
</script>