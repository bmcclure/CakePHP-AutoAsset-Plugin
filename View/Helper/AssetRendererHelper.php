<?php
App::uses('AppHelper', 'View/Helper');
App::uses('DefaultAssetRenderer', 'AutoAsset.Lib/AssetLib/AssetRenderer');
App::uses('AssetRendererNotFoundException', 'AutoAsset.Lib/AssetLib/Error/Exception');

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
	public $helpers = array('Html', 'AutoAsset.AsyncAsset');

    /**
     * @var array
     */
    public $settings = array(
		'assetsVar' => 'assets',
        'helpers' => array('Html', 'AutoAsset.AsyncAsset'),
        'assetTypes' => array('js', 'css', 'jsGlobal', 'metaTag'),
        'asyncTypes' => array('js', 'css'),
	);

    /**
     * @var array
     */
    protected $renderers = array();

    /**
     * @var array
     */
    protected $blocksRendered = array();

    /**
     * @var array
     */
    protected $assets = array();

/**
 * Constructor.
 *
 * @access public
 */
    function __construct(View $View, $settings = array()) {
        parent::__construct($View, (array)$settings);

		$this->settings = array_merge($this->settings, (array) $settings);

        if (isset($View->viewVars[$this->settings['assetsVar']])) {
            $this->assets = $View->viewVars[$this->settings['assetsVar']];
        }

        $this->setDefaultRenderer($this->settings['helpers'], $this->settings['assetTypes']);
        $this->setAsyncRenderer();
    }

    /**
     * @param null $name
     * @return string
     * @throws AssetRendererNotFoundException
     */
    public function renderBlock($name = NULL) {
        if ($name == NULL) {
            $name = array_keys($this->assets);
        }

        $output = "";

        foreach ((array) $name as $blockName) {
            if (array_search($blockName, $this->blocksRendered) !== FALSE) {
                continue;
            }

            /**
             * @var AssetBlock $block
             */
            $block = $this->assets[$blockName];
            if (!is_a($block, 'AssetBlock')) {
                continue;
            }

            /**
             * @var AssetRenderer $renderer
             */
            $renderer = $this->renderers[$block->getRenderer()];

            if (!is_a($renderer, 'AssetRenderer')) {
                throw new AssetRendererNotFoundException(array('renderer' => $block->getRenderer()));
            }

            $output .= $renderer->renderBlock($block);

            $this->blocksRendered[] = $blockName;
        }

        return $output;
    }

    /**
     * @param AssetCollection $collection
     * @param AssetRenderer $renderer
     * @return mixed
     */
    public function renderCollection(AssetCollection $collection, AssetRenderer $renderer) {
        return $renderer->renderCollection($collection);
    }

    /**
     * @param AssetInterface $asset
     * @param AssetRenderer $renderer
     * @return mixed
     */
    public function renderAsset(AssetInterface $asset, AssetRenderer $renderer) {
        return $renderer->render($asset);
    }

    /**
     * @param $name
     * @param AssetRenderer $renderer
     */
    public function setRenderer($name, AssetRenderer $renderer) {
        $this->renderers[$name] = $renderer;
    }

    /**
     * @param $name
     */
    public function removeRenderer($name) {
        unset($this->renderers[$name]);
    }

    /**
     * @param null $helpers
     * @param null $assetTypes
     */
    public function setDefaultRenderer($helpers = NULL, $assetTypes = NULL) {
        if ($helpers == NULL) {
            $helpers = array('Html');
        }

        if ($assetTypes == NULL) {
            $assetTypes = $this->settings['assetTypes'];
        }

        $helperObjects = array();
        foreach ($helpers as $name) {
            $helperObjects[$name] = $this->loadHelper($name);
        }

        $this->setRenderer('default', new DefaultAssetRenderer($helperObjects, $assetTypes));
    }

    public function setAsyncRenderer($helpers = NULL, $assetTypes = NULL) {
        if ($helpers == NULL) {
            $helpers = array('Html', 'AutoAsset.AsyncAsset');
        }

        if ($assetTypes == NULL) {
            $assetTypes = $this->settings['assetTypes'];
            $asyncTypes = $this->settings['asyncTypes'];
            foreach ($assetTypes as $idx => $type) {
                if (array_key_exists($type, $asyncTypes)) {
                    $assetTypes[$type] = array('helper' => 'AsyncAsset');
                    unset($assetTypes[$idx]);
                }
            }
        }

        $helperObjects = array();
        foreach ($helpers as $name) {
            $helperObjects[$name] = $this->loadHelper($name);
        }

        $this->setRenderer('async', new DefaultAssetRenderer($helperObjects, $assetTypes));
    }

    protected function loadHelper($name) {
        list($plugin, $helperName) = pluginSplit($name);
        if (!isset($this->$helperName)) {
            $helper = $this->_View->loadHelper($name);
        } else {
            $helper = $this->$helperName;
        }

        return $helper;
    }

	/**
	 * Returns the HTML output to lazy-load the configured Javascript and Css
	 *
	 * Also includes the required CSS and JS if it has not already been output with the
	 *  required($assets) function, since they need to appear before the async assets.
	 */
	public function load() {
		$output = "\$css.path = '/css/';\n";
		$output .= "\$script.path = '/js/';\n";

		$output = $this->Html->scriptBlock($output, array('inline' => TRUE));

		return $output;
	}
}
?>