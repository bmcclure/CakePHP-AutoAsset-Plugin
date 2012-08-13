<?php
App::uses('AssetCollection', 'AutoAsset.Lib/AssetLib');
App::uses('AssetInterface', 'AutoAsset.Lib/AssetLib/Asset');

/**
 * Represents a block of assets to be output in a region of a site
 */
class AssetBlock {
    /**
     * @var AssetCollection
     */
    protected $assets;

    /**
     * @var
     */
    protected $renderer;

    /**
     * @var
     */
    protected $conditional;

    /**
     * @param string $renderer
     * @param array $conditional
     */
    public function __construct($renderer = 'default', $conditional = array()) {
        $this->assets = new AssetCollection();

        $this->renderer = $renderer;

        $this->conditional = $conditional;
    }

    /**
     * @param AssetInterface $asset
     */
    public function add(AssetInterface $asset) {
        $this->assets->add($asset);
    }

    /**
     * @param AssetInterface $asset
     */
    public function remove(AssetInterface $asset) {
        $this->assets->remove($asset);
    }

    /**
     * @return AssetCollection
     */
    public function getCollection() {
        return $this->assets;
    }

    /**
     * @return array
     */
    public function getAssets() {
        return $this->assets->getAssets();
    }

    public function getRenderer() {
        return $this->renderer;
    }

    public function getConditional() {
        return $this->conditional;
    }
}
?>