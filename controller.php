<?php

namespace Concrete\Package\EasyImageGallery;

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Package\Package;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package
{
    protected $pkgHandle = 'easy_image_gallery';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$appVersionRequired
     */
    protected $appVersionRequired = '8.5.2';

    protected $pkgVersion = '1.4.2';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageName()
     */
    public function getPackageName()
    {
        return t('Easy Image Gallery');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageDescription()
     */
    public function getPackageDescription()
    {
        return t('Easy Image made gallery easy for your client');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::install()
     */
    public function install()
    {
        parent::install();
        $this->installContentFile('config/install.xml');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::upgrade()
     */
    public function upgrade()
    {
        parent::upgrade();
        $this->installContentFile('config/install.xml');
    }

    public function on_start()
    {
        $this->registerAssets();
    }

    private function registerAssets()
    {
        $al = AssetList::getInstance();
        $al->register(
            'javascript',
            'knob',
            'blocks/easy_image_gallery/javascript/build/jquery.knob.js',
            [
                'version' => '1.2.11',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );
        $al->register(
            'css',
            'easy-gallery-edit',
            'blocks/easy_image_gallery/stylesheet/block-edit.css',
            [
                'version' => '1',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );
        $al->register(
            'css',
            'easy-gallery-view',
            'blocks/easy_image_gallery/stylesheet/block-view.css',
            [
                'version' => '1',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );
        // View items
        $al->register(
            'javascript',
            'intense',
            'blocks/easy_image_gallery/javascript/build/intense.js',
            [
                'version' => '1',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );
        $al->register(
            'javascript',
            'fancybox',
            'blocks/easy_image_gallery/javascript/build/jquery.fancybox.pack.js',
            [
                'version' => '2.1.5',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );
        $al->register(
            'javascript',
            'masonry',
            'blocks/easy_image_gallery/javascript/build/masonry.pkgd.min.js',
            [
                'version' => '3.1.4',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );
        $al->register(
            'javascript',
            'imagesloaded',
            'blocks/easy_image_gallery/javascript/build/imagesloaded.pkgd.min.js',
            [
                'version' => '3.1.4',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );
        $al->register(
            'javascript',
            'isotope',
            'blocks/easy_image_gallery/javascript/build/isotope.pkgd.min.js',
            [
                'version' => '3.1.4',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );
        $al->register(
            'javascript',
            'lazyload',
            'blocks/easy_image_gallery/javascript/build/jquery.lazyload.min.js',
            [
                'version' => '1.9.1',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );

        $al->register(
            'css',
            'fancybox',
            'blocks/easy_image_gallery/stylesheet/jquery.fancybox.css',
            [
                'version' => '2.1.5',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );
    }
}
