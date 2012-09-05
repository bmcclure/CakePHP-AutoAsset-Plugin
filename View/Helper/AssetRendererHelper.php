<?php
App::uses('AppHelper', 'View/Helper');
App::uses('AssetRenderer', 'AutoAsset.Lib/AssetLib/AssetRenderer');
App::uses('BaseAssetRenderer', 'AutoAsset.Lib/AssetLib/AssetRenderer');
App::uses('DefaultAssetRenderer', 'AutoAsset.AssetLib/AssetRenderer');
App::uses('AssetRendererNotFoundException', 'AutoAsset.AssetLib/Error/Exception');
App::uses('AssetBlockNotFoundException', 'AutoAsset.AssetLib/Error/Exception');
App::uses('AssetTypeUnsupportedException', 'AutoAsset.AssetLib/Error/Exception');
App::uses('HelperMethodNotFoundException', 'AutoAsset.AssetLib/Error/Exception');
App::uses('MissingAssetClassException', 'AutoAsset.AssetLib/Error/Exception');

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
    
    public function render($name = null) {
    	if (is_a($name, 'AssetBlock')) {
    		return $this->renderBlock($name);
    	}
    	
    	if (empty($name)) {
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

            $output .= $this->renderBlock($block);

            $this->blocksRendered[] = $blockName;
        }
        
        return $output;
    }

    /**
     * @param null $name
     * @return string
     * @throws AssetRendererNotFoundException
     */
    public function renderBlock(AssetBlock $block, AssetRenderer $renderer = null) {
    	if (is_null($renderer)) {
    		$renderer = $block->getRenderer();
    	}
    	
    	if (is_string($renderer)) {
    		$renderer = $this->renderers[$renderer];
    	}
    	
    	if (!is_a($renderer, 'AssetRenderer')) {
            throw new AssetRendererNotFoundException(array('renderer' => $block->getRenderer()));
        }
    	
    	return $renderer->renderBlock($block);
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
            list($plugin, $helperName) = pluginSplit($name);
            $helperObjects[$helperName] = $this->loadHelper($name);
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
                if (array_search($type, $asyncTypes) !== false) {
                    $assetTypes[$type] = array('helper' => 'AsyncAsset');
                    unset($assetTypes[$idx]);
                }
            }
        }

        $helperObjects = array();
        foreach ($helpers as $name) {
            list($plugin, $helperName) = pluginSplit($name);
            $helperObjects[$helperName] = $this->loadHelper($name);
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
}
?>