<?php
require_once dirname(dirname(__FILE__)).DS.'AssetBlock.php';
require_once dirname(dirname(__FILE__)).DS.'AssetCollection.php';
require_once dirname(dirname(__FILE__)).DS.'Asset/AssetInterface.php';
//App::uses('AssetBlock', 'AutoAsset.Lib/AssetLib');
//App::uses('AssetCollection', 'AutoAsset.Lib/AssetLib');
//App::uses('AssetInterface', 'AutoAsset.Lib/AssetLib/Asset');

/**
 * Renders assets (or entire asset collections or asset blocks) for display
 */
interface AssetRenderer {
    /**
     * @abstract
     * @param AssetBlock $assetBlock
     * @return mixed
     */
    public function renderBlock(AssetBlock $assetBlock);

    /**
     * @abstract
     * @param AssetCollection $assetCollection
     * @return mixed
     */
    public function renderCollection(AssetCollection $assetCollection);

    /**
     * @abstract
     * @param AssetInterface $asset
     * @return mixed
     */
    public function render(AssetInterface $asset);
}
?>