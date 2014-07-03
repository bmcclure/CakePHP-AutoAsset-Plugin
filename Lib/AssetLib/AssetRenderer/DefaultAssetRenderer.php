<?php
namespace AssetLib\AssetRenderer;

use AssetLib\Asset\AssetInterface;
use AssetLib\Asset\CssAsset;
use AssetLib\Asset\JsAsset;
use AssetLib\Asset\JsGlobalAsset;
use AssetLib\Asset\MetaTagAsset;
use AssetLib\Error\Exception\AssetTypeUnsupportedException;
use AssetLib\Error\Exception\HelperMethodNotFoundException;

/**
 * The default AssetRenderer which uses provided CakePHP helpers to render assets
 */
class DefaultAssetRenderer extends BaseAssetRenderer {
    /**
     * @var array
     */
    protected $assetTypeDefaults = [
        'className' => '',
        'plugin' => 'AutoAsset',
        'helper' => 'Html',
    ];

    /**
     * @var array
     */
    protected $helpers;

    /**
     * @var array
     */
    protected $assetTypes = [
        'css' => [],
        'js' => [],
        'jsGlobal' => [],
        'metaTag' => [],
    ];

    protected $knownTypes = ['Css', 'Js', 'JsGlobal', 'MetaTag'];

    /**
     * @param array $helpers
     * @param array $assetTypes
     */
    public function __construct($helpers = [], $assetTypes = []) {
        $this->helpers = $helpers;

        $this->assetTypes = array_merge($this->_flipAndFillIfNeeded($this->assetTypes),
            $this->_flipAndFillIfNeeded((array) $assetTypes));

        foreach ($this->assetTypes as $type => $options) {
            $this->assetTypes[$type] = (array) $options + (array) $this->assetTypeDefaults;

            if (empty($this->assetTypes[$type]['className'])) {
                $this->assetTypes[$type]['className'] = \Inflector::camelize($type) . "Asset";
            }
        }
    }

    /**
     * @param $array
     *
     * @return array
     */protected function _flipAndFillIfNeeded($array) {
        if (!(bool)count(array_filter(array_keys($array), 'is_string'))) {
            $array = array_fill_keys($array, []);
        }

        return $array;
    }

    /**
     * @param AssetInterface $asset
     *
     * @return mixed
     * @throws AssetTypeUnsupportedException
     */
    protected function _renderAsset(AssetInterface $asset) {
        foreach ($this->knownTypes as $type) {
            if (!is_a($asset, "AssetLib\\Asset\\{$type}Asset")) {
                continue;
            }

            $function = "render$type";

            if (!method_exists($this, $function)) {
                break;
            }

            return $this->$function($asset);
        }

        throw new AssetTypeUnsupportedException();
    }

    /**
     * @param $typeKey
     * @param $helperMethod
     * @param array $helperParams
     *
     * @throws \AssetLib\Error\Exception\HelperMethodNotFoundException
     * @internal param \AssetLib\Asset\AssetInterface $asset
     * @internal param $assetMethod
     * @internal param array $helperOptions
     *
     * @return mixed
     */
    protected function _renderType($typeKey, $helperMethod, $helperParams = []) {
        $helper = $this->helpers[$this->assetTypes[$typeKey]['helper']];

        if (!is_a($helper, 'Helper') || !method_exists($helper, $helperMethod)) {
            throw new HelperMethodNotFoundException();
        }

        return call_user_func_array([$helper, $helperMethod], $helperParams);
    }

    /**
     * @param CssAsset $asset
     *
     * @return mixed
     * @throws HelperMethodNotFoundException
     */
    public function renderCss(CssAsset $asset) {
        $options = ['inline' => true] + $asset->getOptions();

        $params = [$asset->getPath(), $asset->getRel(), $options];

        return $this->_renderType('css', 'css', $params);
    }

    /**
     * @param JsAsset $asset
     *
     * @return mixed
     * @throws HelperMethodNotFoundException
     */
    public function renderJs(JsAsset $asset) {
        $options = ['inline' => true] + $asset->getOptions();

        $params = [$asset->getPath(), $options];

        return $this->_renderType('js', 'script', $params);
    }



    /**
     * @param JsGlobalAsset $asset
     *
     * @return mixed
     * @throws HelperMethodNotFoundException
     */
    public function renderJsGlobal(JsGlobalAsset $asset) {
        $options = ['inline' => true] + $asset->getOptions();

        $params = [$asset->getString(), ['inline' => true] + $asset->getOptions()];

        return $this->_renderType('jsGlobal', 'scriptBlock', $params);
    }

    /**
     * @param MetaTagAsset $asset
     *
     * @return mixed
     * @throws HelperMethodNotFoundException
     */
    public function renderMetaTag(MetaTagAsset $asset) {
        $numberOfValues = $asset->numberOfValues();

        $result = [];

        for ($i = 0; $i < $numberOfValues; $i++) {
            $params = [$asset->getType($i), $asset->getUrl($i), (array)$asset->getOptions($i)];

            $result[] = $this->_renderType('metaTag', 'meta', $params);
        }

        return implode("\n", $result);
    }
}
