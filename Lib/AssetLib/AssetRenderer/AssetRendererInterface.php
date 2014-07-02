<?php
namespace AssetLib\AssetRenderer;

use AssetLib\AssetBlock;
use AssetLib\AssetCollection;
use AssetLib\Asset\AssetInterface;

/**
 * Renders assets (or entire asset collections or asset blocks) for display
 */
interface AssetRendererInterface {
    /**
     * @abstract
     *
     * @param AssetBlock $assetBlock
     *
     * @return mixed
     */
    public function renderBlock(AssetBlock $assetBlock);

    /**
     * @abstract
     *
     * @param AssetCollection $assetCollection
     *
     * @return mixed
     */
    public function renderCollection(AssetCollection $assetCollection);

    /**
     * @abstract
     *
     * @param AssetInterface $asset
     *
     * @return mixed
     */
    public function render(AssetInterface $asset);
}
