<?php
use AssetLib\Asset\AssetInterface;
use AssetLib\AssetBlock;
use AssetLib\AssetCollection;
use AssetLib\AssetRenderer\AssetRendererInterface;
use AssetLib\AssetRenderer\DefaultAssetRenderer;
use AssetLib\Error\Exception\AssetRendererNotFoundException;

App::uses('AppHelper', 'View/Helper');

/**
 * AssetLoader Helper
 *
 * Assists in outputting prerequisite (required) and asynchronous (lazy-loaded)
 *  CSS and JavaScript assets with the help of the HtmlHelper.
 *
 * @property HelperCollection $Helpers
 * @property HtmlHelper $Html
 */
class AssetRendererHelper extends AppHelper {
    /**
     * View helpers required by this helper
     */
    public $helpers = ['Html', 'AutoAsset.AsyncAsset'];

    /**
     * @var array
     */
    public $settings = [
        'assetsVar' => 'assets',
        'helpers' => ['Html', 'AutoAsset.AsyncAsset'],
        'assetTypes' => ['js', 'css', 'jsGlobal', 'metaTag'],
        'asyncTypes' => ['js', 'css'],
    ];

    /**
     * @var array
     */
    protected $renderers = [];

    /**
     * @var array
     */
    protected $blocksRendered = [];

    /**
     * @var array
     */
    protected $assets = [];

    /**
     * Constructor.
     *
     * @access public
     */
    function __construct(View $View, $settings = []) {
        parent::__construct($View, (array)$settings);

        $this->settings = (array)$settings + $this->settings;

        if (isset($View->viewVars[$this->settings['assetsVar']])) {
            $this->assets = $View->viewVars[$this->settings['assetsVar']];

            unset($View->viewVars[$this->settings['assetsVar']]);
        }

        $this->setDefaultRenderer($this->settings['helpers'], $this->settings['assetTypes']);

        $this->setAsyncRenderer();
    }

    /**
     * Render one or more asset blocks. Leave $name null to render all asset blocks that have not been rendered.
     *
     * @param null $name
     *
     * @return string
     */
    public function render($name = null) {
        if (is_a($name, 'AssetLib\AssetBlock')) {
            return $this->renderBlock($name);
        }

        if (empty($name)) {
            $name = array_keys($this->assets);
        }

        $output = [];

        foreach ((array)$name as $blockName) {
            if (array_search($blockName, $this->blocksRendered) !== false) {
                continue;
            }

            /** @var AssetBlock $block */
            $block = $this->assets[$blockName];

            if (!is_a($block, 'AssetLib\AssetBlock')) {
                continue;
            }

            $output[] = $this->renderBlock($block);

            $this->blocksRendered[] = $blockName;
        }

        return implode("\n", $output);
    }

    /**
     * Renders an AssetBlock object
     *
     * @param AssetLib\AssetBlock $block
     * @param AssetRendererInterface $renderer
     *
     * @throws AssetRendererNotFoundException
     * @internal param null $name
     * @return string
     */
    public function renderBlock(AssetBlock $block, AssetRendererInterface $renderer = null) {
        if (is_null($renderer)) {
            $renderer = $block->getRenderer();
        }

        if (is_string($renderer)) {
            $renderer = $this->renderers[$renderer];
        }

        if (!is_a($renderer, 'AssetLib\AssetRenderer\AssetRendererInterface')) {
            throw new AssetRendererNotFoundException(['renderer' => $block->getRenderer()]);
        }

        return $renderer->renderBlock($block);
    }

    /**
     * Renders an AssetCollection object
     *
     * @param AssetCollection $collection
     * @param AssetRendererInterface $renderer
     *
     * @return mixed
     */
    public function renderCollection(AssetCollection $collection, AssetRendererInterface $renderer) {
        return $renderer->renderCollection($collection);
    }

    /**
     * Renders a single Asset object
     *
     * @param AssetInterface $asset
     * @param AssetRendererInterface $renderer
     *
     * @return mixed
     */
    public function renderAsset(AssetInterface $asset, AssetRendererInterface $renderer) {
        return $renderer->render($asset);
    }

    /**
     * Sets an available AssetRenderer for use.
     *
     * @param $name
     * @param AssetRendererInterface $renderer
     */
    public function setRenderer($name, AssetRendererInterface $renderer) {
        $this->renderers[$name] = $renderer;
    }

    /**
     * Removes an AssetRenderer from the list of available renderers.
     *
     * @param $name
     */
    public function removeRenderer($name) {
        unset($this->renderers[$name]);
    }

    /**
     * @param null $helpers
     * @param null $assetTypes
     */
    public function setDefaultRenderer($helpers = null, $assetTypes = null) {
        if ($helpers == null) {
            $helpers = ['Html'];
        }

        if ($assetTypes == null) {
            $assetTypes = $this->settings['assetTypes'];
        }

        // Load required helpers
        $helperObjects = [];
        foreach ($helpers as $name) {
            list($plugin, $helperName) = pluginSplit($name);
            $helperObjects[$helperName] = $this->loadHelper($name);
        }

        $this->setRenderer('default', new DefaultAssetRenderer($helperObjects, $assetTypes));
    }

    /**
     * @param null $helpers
     * @param null $assetTypes
     */
    public function setAsyncRenderer($helpers = null, $assetTypes = null) {
        if ($helpers == null) {
            $helpers = ['Html', 'AutoAsset.AsyncAsset'];
        }

        if ($assetTypes == null) {
            $assetTypes = $this->settings['assetTypes'];
            $asyncTypes = $this->settings['asyncTypes'];
            foreach ($assetTypes as $idx => $type) {
                if (array_search($type, $asyncTypes) !== false) {
                    $assetTypes[$type] = ['helper' => 'AsyncAsset'];
                    unset($assetTypes[$idx]);
                }
            }
        }

        $helperObjects = [];
        foreach ($helpers as $name) {
            list($plugin, $helperName) = pluginSplit($name);
            $helperObjects[$helperName] = $this->loadHelper($name);
        }

        $this->setRenderer('async', new DefaultAssetRenderer($helperObjects, $assetTypes));
    }

    /**
     * Loads a helper to make it available for use by an AssetRenderer
     *
     * @param $name
     *
     * @return Helper|mixed
     */
    protected function loadHelper($name) {
        list($plugin, $helperName) = pluginSplit($name);
        if (!isset($this->$helperName)) {
            $helper = $this->_View->loadHelper($name);
        } else {
            $helper = $this->$helperName;
        }

        return $helper;
    }
}
