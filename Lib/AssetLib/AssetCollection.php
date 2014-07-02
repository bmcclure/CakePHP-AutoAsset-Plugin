<?php
namespace AssetLib;

use AssetLib\Asset;
use AssetLib\Asset\AssetInterface;

/**
 * Represents any basic collection of AssetInterface objects
 */
class AssetCollection {
    /**
     * @var array
     */
    protected $assets;

    /**
     * @param array $assets
     */
    public function __construct($assets = []) {
        $this->assets = (array) $assets;
    }

    /**
     * @param AssetInterface $asset
     */
    public function add(AssetInterface $asset) {
        $this->assets[] = $asset;
    }

    /**
     * @param AssetInterface $asset
     * @param int $position
     */
    public function insert(AssetInterface $asset, $position = 0) {
        array_splice($this->assets, $position, 0, [$asset]);
    }

    /**
     * @param AssetInterface $existingAsset
     * @param AssetInterface $asset
     */
    public function insertAfter(AssetInterface $existingAsset, AssetInterface $asset) {
        $pos = array_search($existingAsset, $this->assets);

        if ($pos === false) {
            $this->insert($asset);

            return;
        }

        array_splice($this->assets, $pos + 1, 0, [$asset]);
    }

    /**
     * @param AssetInterface $existingAsset
     * @param AssetInterface $asset
     */
    public function insertBefore(AssetInterface $existingAsset, AssetInterface $asset) {
        $pos = array_search($existingAsset, $this->assets);

        if ($pos === false) {
            $this->insert($asset);

            return;
        }

        array_splice($this->assets, $pos, 0, [$asset]);
    }

    /**
     * @param AssetInterface $asset
     */
    public function remove(AssetInterface $asset) {
        $pos = array_search($asset, $this->assets);

        if ($pos !== false) {
            array_splice($this->assets, $pos, count($this->assets), array_slice($this->assets, $pos + 1));
        }
    }

    /**
     * @param AssetInterface $oldAsset
     * @param AssetInterface $asset
     */
    public function replace(AssetInterface $oldAsset, AssetInterface $asset) {
        $pos = array_search($oldAsset, $this->assets);

        if ($pos === false) {
            $this->insert($asset);

            return;
        }

        $this->assets[$pos] = $asset;
    }

    /**
     * @return array
     */
    public function getAssets() {
        return $this->assets;
    }
}
