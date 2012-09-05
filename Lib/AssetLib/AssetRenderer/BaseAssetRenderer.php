<?php
require_once 'AssetRenderer.php';
require_once dirname(dirname(__FILE__)).DS.'Error/Exception/AssetTypeUnsupportedException.php';
//App::uses('AssetRenderer', 'AutoAsset.Lib/AssetLib/AssetRenderer');
//App::uses('AssetTypeUnsupportedException', 'AutoAsset.Lib/AssetLib/Error/Exception');

/**
 * The base AssetRenderer that others inherit from
 */
abstract class BaseAssetRenderer implements AssetRenderer {
    /**
     * @var array
     */
    protected $assetTypes;

    /**
     * @param array $assetTypes
     */
    protected function __construct($assetTypes = array()) {
        $this->assetTypes = $assetTypes;
    }

    /**
     * @param $assetType
     * @return bool
     */
    public function supports($assetType) {
        return array_key_exists($assetType, $this->assetTypes);
    }

    /**
     * @param $assetType
     * @throws AssetTypeUnsupportedException
     */
    protected function validateType($assetType) {
        if (!$this->supports($assetType)) {
            throw new AssetTypeUnsupportedException(array('type' => $assetType, 'class' => get_called_class()));
        }
    }

    protected function getTypeOfAsset(AssetInterface $asset) {
        return Inflector::variable(substr(get_class($asset), 0, -5));
    }

    /**
     * @param AssetBlock $assetBlock
     * @return string
     */
    public function renderBlock(AssetBlock $assetBlock) {
        $output = "";

        foreach ($assetBlock->getAssets() as $asset) {
            $output .= $this->render($asset);
        }

        return $output;
    }

    /**
     * @param AssetCollection $assetCollection
     * @return string
     */
    public function renderCollection(AssetCollection $assetCollection) {
        $output = "";

        foreach ($assetCollection->getAssets() as $asset) {
            $output .= $this->render($asset);
        }

        return $output;
    }

    /**
     * @param AssetInterface $asset
     * @return mixed
     * @throws AssetTypeUsupportedException
     */
    public function render(AssetInterface $asset) {

        $this->validateType($this->getTypeOfAsset($asset));

        return $this->_renderAsset($asset);
    }

    /**
     * @abstract
     * @param AssetInterface $asset
     * @return mixed
     */
    protected abstract function _renderAsset(AssetInterface $asset);
}
?>