<?php defined('C5_EXECUTE') or die("Access Denied.");
if ((count($tagsObject->tags) && $options->filtering) ||($options->textFiltering)) ) :
	// All is in echo to preserve the no-spce for display:inline-block
	echo '<ul class="filter-set zero hlist clearfix" data-filter="filter" id="filter-set-' . $bID . '">';
	if (count($tagsObject->tags) && $options->filtering) :
	  echo '<li><a href="#show-all" data-option-value="*" class="btn btn-primary">' . t('show all') .'</a></li>';
	  foreach ($tagsObject->tags as $handle => $tag):
	  	echo '<li><a class="btn btn-default" href="#' . $handle . '" data-filter=".' . $handle . '">' . $tag . '</a></li>';
		endforeach;
	endif;
	if ($options->textFiltering):
		echo '<li class="search-filter-wrapper"><input type="text" class="search-filter" id="quicksearch-' . $bID . '" placeholder="' . t('Search on Gallery') . '" /></li>';
	endif;
	echo '</ul>';
endif;?>
