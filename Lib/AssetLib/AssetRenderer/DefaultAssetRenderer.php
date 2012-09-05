<?php
require_once 'BaseAssetRenderer.php';
require_once dirname(dirname(__FILE__)).DS.'Error/Exception/HelperMethodNotFoundException.php';

//App::uses('BaseAssetRenderer', 'AutoAsset.Lib/AssetLib/AssetRenderer');
//App::uses('HelperMethodNotFoundException', 'AutoAsset.Lib/AssetLib/Error/Exception');

/**
 * The default AssetRenderer which uses provided CakePHP helpers to render assets
 */
class DefaultAssetRenderer extends BaseAssetRenderer {
    /**
     * @var array
     */
    protected $assetTypeDefaults = array(
        'className' => '',
        'plugin' => 'AutoAsset',
        'helper' => 'Html',
    );

    /**
     * @var array
     */
    protected $helpers;

    /**
     * @var array
     */
    protected $assetTypes = array(
        'css' => array(),
        'js' => array(),
        'jsGlobal' => array(),
        'metaTag' => array(),
    );

    /**
     * @param array $helpers
     * @param array $assetTypes
     */
    public function __construct($helpers = array(), $assetTypes = array()) {
        $this->helpers = $helpers;

        $assetTypes = (array) $assetTypes;

        foreach ($this->assetTypes as $idx => $assetType) {
            if (is_int($idx)) {
                $this->assetTypes[$assetType] = array();
                unset($this->assetTypes[$idx]);
            }
        }

        foreach ($assetTypes as $idx => $assetType) {
            if (is_int($idx)) {
                $assetTypes[$assetType] = array();
                unset($assetTypes[$idx]);
            }
        }

        $this->assetTypes = array_merge($this->assetTypes, $assetTypes);

        foreach ($this->assetTypes as $type => $options) {
            $this->assetTypes[$type] = array_merge((array) $this->assetTypeDefaults, (array) $options);

            if (empty($definition['className'])) {
                $this->assetTypes[$type]['className'] = Inflector::camelize($type)."Asset";
            }
        }
    }

    /**
     * @param AssetInterface $asset
     * @return mixed
     * @throws AssetTypeUsupportedException
     */
    protected function _renderAsset(AssetInterface $asset) {
        if (is_a($asset, 'CssAsset')) {
            return $this->renderCss($asset);
        } elseif (is_a($asset, 'JsAsset')) {
            return $this->renderJs($asset);
        } elseif (is_a($asset, 'JsGlobalAsset')) {
            return $this->renderJsGlobal($asset);
        } elseif (is_a($asset, 'MetaTagAsset')) {
            return $this->renderMetaTag($asset);
        }

        throw new AssetTypeUnsupportedException();
    }

    /**
     * @param CssAsset $asset
     * @return mixed
     * @throws HelperMethodNotFoundException
     */
    public function renderCss(CssAsset $asset) {
        $helper = $this->helpers[$this->assetTypes['css']['helper']];

        if (!is_a($helper, 'Helper') || !method_exists($helper, 'css')) {
            throw new HelperMethodNotFoundException();
        }

        return $helper->css($asset->getPath(), $asset->getRel(), array('inline' => TRUE));
    }

    /**
     * @param JsAsset $asset
     * @return mixed
     * @throws HelperMethodNotFoundException
     */
    public function renderJs(JsAsset $asset) {
        $helper = $this->helpers[$this->assetTypes['js']['helper']];

        if (!is_a($helper, 'Helper') || !method_exists($helper, 'script')) {
            throw new HelperMethodNotFoundException();
        }

        return $helper->script($asset->getPath(), array('inline' => TRUE));
    }

    /**
     * @param JsGlobalAsset $asset
     * @return mixed
     * @throws HelperMethodNotFoundException
     */
    public function renderJsGlobal(JsGlobalAsset $asset) {
        $helper = $this->helpers[$this->assetTypes['jsGlobal']['helper']];

        if (!is_a($helper, 'Helper') || !method_exists($helper, 'scriptBlock')) {
            throw new HelperMethodNotFoundException();
        }

        return $helper->scriptBlock($asset->getString(), array('inline' => TRUE));
    }

    /**
     * @param MetaTagAsset $asset
     * @return mixed
     * @throws HelperMethodNotFoundException
     */
    public function renderMetaTag(MetaTagAsset $asset) {
        $helper = $this->helpers[$this->assetTypes['metaTag']['helper']];

        if (!is_a($helper, 'Helper') || !method_exists($helper, 'scriptBlock')) {
            throw new HelperMethodNotFoundException();
        }

        $numberOfValues = $asset->numberOfValues();

        $result = "";

        for ($i = 0; $i < $numberOfValues; $i++) {
            if (!empty($result)) {
                $result .= "\n";
            }

            $result .= $helper->meta($asset->getType($i), $asset->getUrl($i), (array) $asset->getOptions($i));
        }

        return $result;
    }
}
?>