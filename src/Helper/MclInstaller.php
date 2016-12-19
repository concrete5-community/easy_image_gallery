<?php
namespace Concrete\Package\EasyImageGallery\Src\Helper;

use Concrete\Core\Backup\ContentImporter;

defined('C5_EXECUTE') or die(_("Access Denied."));

use Page;
use Package;
use SinglePage;
use PageType;
use BlockType;
use Block;
use PageTheme;
use Loader;
use Core;
use PageTemplate;
use CollectionAttributeKey;
use FileAttributeKey;
use UserAttributeKey;
use \Concrete\Core\Block\BlockType\Set as BlockTypeSet;
use \Concrete\Core\Attribute\Type as AttributeType;
use \Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;

class MclInstaller extends ContentImporter
{

}
