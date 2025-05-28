<?php

namespace Concrete\Package\EasyImageGallery;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @readonly
 */
final class Tags
{
    /**
     * Array keys: the file ID, array values: the tag handles
     *
     * @var array[]
     */
    public $fileTags = [];

    /**
     * Array keys: the tag handle, array values: the tag name
     *
     * @var array
     */
    public $tags = [];
}
