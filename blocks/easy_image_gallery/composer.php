<?php
defined('C5_EXECUTE') or die("Access Denied.");

$this->inc('form_setup_html.php', array('view' => $view,
										'fIDs' => $controller->getFilesIds(),
										'fDetails' => $controller->getFilesDetails($controller->getFilesIds()),
										'fileSets' => $controller->getFileSetList(),
										'isComposer' => true
										));
?>

<style>
	.ccm-inline-toolbar.ccm-ui.easy-image-toolbar {
		opacity: 1;
	}
	.easy-image-toolbar .ccm-inline-toolbar-button-save,
	.easy-image-toolbar .ccm-inline-toolbar-button-cancel {
		display: none;
	}
</style>										