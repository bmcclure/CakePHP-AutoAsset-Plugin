<?php
namespace AssetLib\AssetRenderer;

use AssetLib\AssetBlock;
use AssetLib\AssetCollection;
use AssetLib\Asset\AssetInterface;
use AssetLib\Error\Exception\AssetTypeUnsupportedException;

/**
 * The base AssetRenderer that others inherit from
 */
abstract class BaseAssetRenderer implements AssetRendererInterface {
    /**
     * @var array
     */
    protected $assetTypes;

    /**
     * @param array $assetTypes
     */
    protected function __construct($assetTypes = []) {
        $this->assetTypes = $assetTypes;
    }

    /**
     * @param $assetType
     *
     * @return bool
     */
    public function supports($assetType) {
        return array_key_exists($assetType, $this->assetTypes);
    }

    /**
     * @param $assetType
     *
     * @throws AssetTypeUnsupportedException
     */
    protected function validateType($assetType) {
        if (!$this->supports($assetType)) {
            throw new AssetTypeUnsupportedException(['type' => $assetType, 'class' => get_called_class()]);
        }
    }

    /**
     * @param AssetInterface $asset
     *
     * @return mixed
     */
    protected function getTypeOfAsset(AssetInterface $asset) {
        return $asset->getAssetType();
    }

    /**
     * @param AssetBlock $assetBlock
     *
     * @return string
     */
    public function renderBlock(AssetBlock $assetBlock) {
        return $this->_renderAssets($assetBlock->getAssets());
    }

    /**
     * @param AssetCollection $assetCollection
     *
     * @return string
     */
    public function renderCollection(AssetCollection $assetCollection) {
        return $this->_renderAssets($assetCollection->getAssets());
    }

    /**
     * @param array $assets
     *
     * @return string
     */
    protected function _renderAssets(array $assets) {
        $output = [];

        foreach ($assets as $asset) {
            $output[] = $this->render($asset);
        }

        return implode("\n", $output);
    }

    /**
     * @param AssetInterface $asset
     *
     * @return mixed
     * @throws AssetTypeUnsupportedException
     */
    public function render(AssetInterface $asset) {
        $this->validateType($this->getTypeOfAsset($asset));

        return $this->_renderAsset($asset);
    }

    /**
     * @abstract
     *
     * @param AssetInterface $asset
     *
     * @return mixed
     */
    protected abstract function _renderAsset(AssetInterface $asset);
}

?>