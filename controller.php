<?php
namespace Concrete\Package\EasyImageGallery;

defined('C5_EXECUTE') or die('Access Denied.');
use \Concrete\Core\Block\BlockType\BlockType;

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Route;
use Events;
use Loader;

use Concrete\Package\EasyImageGallery\Src\Helper\MclInstaller;

class Controller extends \Concrete\Core\Package\Package {

    protected $pkgHandle = 'easy_image_gallery';
    protected $appVersionRequired = '5.8';
    protected $pkgVersion = '1.4.2';
    protected $pkg;

    public function getPackageDescription() {
        return t("Easy Image made gallery easy for your client");
    }

    public function getPackageName() {
        return t("Easy Image Gallery");
    }

    public function on_start() {

        $this->registerRoutes();
        $this->registerAssets();
    }

    public function registerAssets()
    {
        $al = AssetList::getInstance();
        $al->register( 'javascript', 'knob', 'blocks/easy_image_gallery/javascript/build/jquery.knob.js', array('version' => '1.2.11', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );
        $al->register( 'javascript', 'easy-gallery-edit', 'blocks/easy_image_gallery/javascript/build/block-edit.js', array('version' => '1', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );
        $al->register( 'css', 'easy-gallery-edit', 'blocks/easy_image_gallery/stylesheet/block-edit.css', array('version' => '1', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );
        $al->register( 'css', 'easy-gallery-view', 'blocks/easy_image_gallery/stylesheet/block-view.css', array('version' => '1', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );

        // View items
        $al->register( 'javascript', 'intense', 'blocks/easy_image_gallery/javascript/build/intense.js', array('version' => '1', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );
        $al->register( 'javascript', 'fancybox', 'blocks/easy_image_gallery/javascript/build/jquery.fancybox.pack.js', array('version' => '2.1.5', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );
        $al->register( 'javascript', 'masonry', 'blocks/easy_image_gallery/javascript/build/masonry.pkgd.min.js', array('version' => '3.1.4', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );
        $al->register( 'javascript', 'imagesloaded', 'blocks/easy_image_gallery/javascript/build/imagesloaded.pkgd.min.js', array('version' => '3.1.4', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );
        $al->register( 'javascript', 'isotope', 'blocks/easy_image_gallery/javascript/build/isotope.pkgd.min.js', array('version' => '3.1.4', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );
        $al->register( 'javascript', 'lazyload', 'blocks/easy_image_gallery/javascript/build/jquery.lazyload.min.js', array('version' => '1.9.1', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );

        $al->register( 'css', 'fancybox', 'blocks/easy_image_gallery/stylesheet/jquery.fancybox.css', array('version' => '2.1.5', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => true, 'combine' => true), $this );

    }
    public function registerRoutes()
    {
        Route::register('/easyimagegallery/tools/savefield','\Concrete\Package\EasyImageGallery\Controller\Tools\EasyImageGalleryTools::save');
        Route::register('/easyimagegallery/tools/getfilesetimages','\Concrete\Package\EasyImageGallery\Controller\Tools\EasyImageGalleryTools::getFileSetImage');
        Route::register('/easyimagegallery/tools/getfiledetailsjson','\Concrete\Package\EasyImageGallery\Controller\Tools\EasyImageGalleryTools::getFileDetailsJson');


    }

    public function install() {

    // Get the package object
        $this->pkg = parent::install();

    // Installing
        $this->installOrUpgrade();

    }


    private function installOrUpgrade() {
        $ci = new MclInstaller($this->pkg);
        $ci->importContentFile($this->getPackagePath() . '/config/install/base/blocktypes.xml');
        $ci->importContentFile($this->getPackagePath() . '/config/install/base/attributes.xml');
    }

    public function upgrade () {
        $this->pkg = $this;

        $this->installOrUpgrade();
        parent::upgrade();
    }

}
