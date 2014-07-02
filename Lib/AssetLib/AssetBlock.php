<?php
namespace AssetLib;

use AssetLib\Asset;
use AssetLib\Asset\AssetInterface;

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

    protected $ignoreTypes;

    /**
     * @param string $renderer
     * @param array $conditional
     * @param array $ignoreTypes
     */
    public function __construct($renderer = 'default', $conditional = [], $ignoreTypes = []) {
        $this->assets = new AssetCollection();

        $this->renderer = $renderer;

        $this->conditional = $conditional;

        $this->ignoreTypes = $ignoreTypes;
    }

    /**
     * @return array
     */
    public function getIgnoreTypes() {
        return $this->ignoreTypes;
    }

    /**
     * @param array $ignoreTypes
     */
    public function setIgnoreTypes(array $ignoreTypes = []) {
        $this->ignoreTypes = $ignoreTypes;
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
     * @param AssetCollection $collection
     */
    public function setCollection(AssetCollection $collection) {
        $this->assets = $collection;
    }

    /**
     * @return array
     */
    public function getAssets() {
        return $this->assets->getAssets();
    }

    /**
     * @return string
     */
    public function getRenderer() {
        return $this->renderer;
    }

    /**
     * @return array
     */
    public function getConditional() {
        return $this->conditional;
    }
}
