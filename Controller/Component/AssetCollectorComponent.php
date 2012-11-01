<?php
App::uses('AssetInterface', 'AutoAsset.Lib/AssetLib/Asset');
App::uses('BaseAsset', 'AutoAsset.Lib/AssetLib/Asset');
App::uses('FileAsset', 'AutoAsset.Lib/AssetLib/Asset');
App::uses('ValueAsset', 'AutoAsset.Lib/AssetLib/Asset');
App::uses('CssAsset', 'AutoAsset.Lib/AssetLib/Asset');
App::uses('JsAsset', 'AutoAsset.Lib/AssetLib/Asset');
App::uses('JsGlobalAsset', 'AutoAsset.Lib/AssetLib/Asset');
App::uses('MetaTagAsset', 'AutoAsset.Lib/AssetLib/Asset');
App::uses('AssetCollection', 'AutoAsset.Lib/AssetLib');
App::uses('AssetBlock', 'AutoAsset.Lib/AssetLib');
App::uses('AssetBlockNotFoundException', 'AutoAsset.AssetLib/Error/Exception');
App::uses('AssetTypeUnsupportedException', 'AutoAsset.AssetLib/Error/Exception');
App::uses('MissingAssetClassException', 'AutoAsset.AssetLib/Error/Exception');

/**
 * AssetGatherer Component
 *
 * Gathers valid CSS and JS files from the server
 *  and organizes them into an array for use with
 *  the AssetLoader Helper.
 */
class AssetCollectorComponent extends Component {
    /**
     * @var array
     *
     * The default settings for asset blocks
     */
    private $blockDefaults = array(
        'renderer' => 'default',
        'ignoreTypes' => array('ajax'),
        'conditional' => array()
    );

    /**
     * @var array
     */
    private $blocks = array(
        'headTop',
        'head',
        'headBottom',
        'bodyTop',
        'body',
        'bodyBottom' => array(
            'renderer' => 'async',
            'ignoreTypes' => array(),
        ),
        'ie' => array(
            'conditional' => array('lt IE 9'),
        ),
    );

    /**
     * @var array
     */
    private $assets = array();

    /**
     * @var array
     */
    private $jsHelpers = array(
        'script' => '/auto_asset/js/script.min',
        'css' => '/auto_asset/js/css',
        'namespace' => '/auto_asset/js/namespace',
        'url' => '/auto_asset/js/url',
    );

	/**
	 * Configurable settings for the component
	 *
	 * blocks: an array of asset block names and settings
     * assets: an array of asset block names with arrays underneath them keyed such as 'js', 'css', etc.
     * globals: an array of asset block names with keyed arrays of global JS variables underneath.
	 */
    protected $defaults = array(
        'jsHelpersBlock' => 'headTop',
        'controllersPath' => 'controllers',
        'theme' => '',
        'controllersBlock' => 'head',
        'assetsVar' => 'assets',
    );

    /**
     * Whether or not to include controller/action CSS files
     */
    public $controllerAssets = array(
        'css' => TRUE,
        'js' => TRUE,
    );

	/**
	 * A reference to the current Controller
	 */
	protected $controller;

	/**
	 * A reference to the current CakeRequest
	 */
	protected $request;

	/**
	 * Overrides base class constructor, sets properties and merges supplied user settings.
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
        $settings = am($this->defaults, $settings);

        $this->_parseSettings($settings);

		$this->controller = $collection->getController();
		$this->request = $this->controller->request;

		$this->_setupControllersPaths();

		parent::__construct($collection, $settings);
	}

    /**
     * @param Controller $controller
     */
    public function beforeRender(Controller $controller) {
        $controller->set($this->settings['assetsVar'], $this->getAssets());
    }

    public function block($name, $settings = array()) {
        $this->blocks[$name] = array_merge($this->blockDefaults, $settings);

        $this->replaceAssetBlock($name, $this->createBlock($this->blocks[$name]));
    }

    public function replaceAssetBlock($name, AssetBlock $block) {
        if (isset($this->assets[$name])) {
            $assets = $this->assets[$name]->getCollection();

            $block->setCollection($assets);
        }

        $this->assets[$name] = $block;
    }

    protected function createBlock($settings) {
        return new AssetBlock($settings['renderer'], $settings['conditional']);
    }


    /**
     * @param $name
     * @return bool
     */
    protected function prepareBlock($name) {
        if (!empty($this->assets[$name]) && is_a($this->assets[$name], 'AssetBlock')) {
            return TRUE;
        }

        if (!empty($this->blocks[$name])) {
            $this->assets[$name] = $this->createBlock($this->blocks[$name]);
            return TRUE;
        }

        $block = new AssetBlock();
        $block->setIgnoreTypes($this->blocks[$name]['ignoreTypes']);

        $this->assets[$name] = $block;
        return TRUE;
    }

	/**
	 * Changes the path to controller/action files after the component has already been initialized
	 */
	public function setThemeName($name) {
		$this->settings['theme'] = 'theme'.DS.$name.DS;

		$this->_setupControllersPaths();
	}

    /**
     * @param $name
     * @param null $value
     * @param string $block
     */
    public function jsGlobal($name, $value = NULL, $block = 'headTop') {
        $this->prepareBlock($block);
        
        if (is_array($name) && is_array($value)) {
        	$i = 0;
        	foreach ($name as $key) {
        		$this->assets[$block]->add(new JsGlobalAsset($key, $value[$i]));
        		
        		$i++;
        	}
        } elseif (is_array($name)) {
        	foreach ($name as $key => $val) {
        		$this->assets[$block]->add(new JsGlobalAsset($key, $val));
        	}
        } else {
        	$this->assets[$block]->add(new JsGlobalAsset($name, $value));
        }
	}

    /**
     * @param $path
     * @param string $block
     */
    public function js($path, $block = 'bodyBottom') {
        $this->prepareBlock($block);
        
        foreach ((array) $path as $file) {
        	$this->assets[$block]->add(new JsAsset($file));
        }
    }

    /**
     * @param $path
     * @param string $rel
     * @param string $block
     */
    public function css($path, $rel = 'stylesheet', $media = 'screen', $block = 'head') {
        $this->prepareBlock($block);

		foreach ((array) $path as $file) {
			$this->assets[$block]->add(new CssAsset($file, $rel, $media));
		}
    }

    /**
     * @param $type
     * @param null $url
     * @param array $options
     * @param string $block
     */
    public function meta($type, $url = NULL, $options = array(), $block = 'head') {
        $this->prepareBlock($block);

        $this->assets[$block]->add(new MetaTagAsset($type, $url, $options));
	}

    /**
     * @return array
     */
    public function getAssets() {
        $assets = array();

        /**
         * @var AssetBlock $block
         */
        foreach ($this->assets as $name => $block) {
            $ignore = FALSE;
            foreach ($block->getIgnoreTypes() as $ignoreType) {
                if ($this->request->is($ignoreType)) {
                    $ignore = TRUE;
                    break;
                }
            }

            if ($ignore) {
                continue;
            }

            if ($name == $this->settings['controllersBlock']) {
                $this->_setupControllerAssets();
            }

            if ($name == $this->settings['jsHelpersBlock']) {
                $this->_setupJsHelpers();
            }

            $assets[$name] = $block;
        }

		return $assets;
	}

    /**
     * @param array $settings
     */
    protected function _parseSettings($settings = array()) {
        foreach (array('blockDefaults', 'jsHelpers', 'controllerAssets') as $setting) {
            if (!empty($settings[$setting])) {
                $this->$setting = Set::merge($this->$setting, $settings[$setting]);
            }
        }

        foreach (array('jsHelpersBlock', 'theme', 'controllersPath', 'controllersBlock', 'assetsVar') as $setting) {
            if (isset($settings[$setting]) || !empty($settings[$setting])) {
                $this->settings[$setting] = $settings[$setting];
            }
        }

        foreach ($this->blocks as $name => $options) {
            if (is_int($name)) {
                $this->blocks[(string) $options] = $this->blockDefaults;
                unset($this->blocks[$name]);
            } else {
                $this->blocks[$name] = array_merge($this->blockDefaults, $options);
            }
        }

        if (!empty($settings['blocks'])) {
            foreach ($settings['blocks'] as $block => $options) {
                if (is_int($block)) {
                    $this->blocks[(string) $options] = $this->blockDefaults;
                } else {
                    $this->blocks[$block] = array_merge($this->blockDefaults, $options);
                }

            }
        }

        foreach (array_keys($this->blocks) as $name) {
            $this->prepareBlock($name);
        }

        if (!empty($settings['assets'])) {
            foreach ((array) $settings['assets'] as $block => $types) {
                foreach ($types as $type => $assets) {
                    switch ($type) {
                        case 'css':
                            foreach ((array)$assets as $media => $files) {
                                foreach ((array)$files as $file) {
                                    $this->css($file, 'stylesheet', $media, $block);
                                }
                            }
                            break;
                        case 'js':
                            foreach ((array)$assets as $file) {
                                $this->js($file, $block);
                            }
                            break;
                        case 'jsGlobal':
                            foreach ((array)$assets as $key => $value) {
                                $this->jsGlobal($key, $value, $block);
                            }
                            break;
                        case 'metaTag':
                            foreach ((array)$assets as $asset) {
                                $asset = (array) $asset;
                                $metaType = $asset[0];
                                $url = (isset($asset[1])) ? $asset[1] : NULL;
                                $options = (isset($asset[2])) ? $asset[2] : array();

                                $this->meta($metaType, $url, $options, $block);
                            }
                            break;
                        default:
                            if (method_exists($this, $type)) {
                                foreach ((array)$assets as $asset) {
                                    $this->$type($asset);
                                }
                            }
                            break;
                    }
                }
            }
        }
    }

	/**
	 * Validates and standardizes the provided controllersPath setting for use with the component
	 */
	private function _setupControllersPaths() {
		if (empty($this->settings['controllersPath'])) {
            foreach ($this->controllerAssets as $type => $enabled) {
                $this->controllerAssets[$type] = FALSE;
            }
			
			return;
		}

        $controllersPath = $this->settings['controllersPath'];
		$theme = $this->settings['theme'];
		
		// Tack on a trailing slash if there isn't one there already
		if (substr($controllersPath, strlen($controllersPath)) != DS) {
			$this->settings['controllersPath'] .= DS;
		}

        foreach ($this->controllerAssets as $type => $enabled) {
            if ($enabled && (!file_exists(substr(WWW_ROOT,0,strlen(WWW_ROOT)-1).DS.$this->settings['theme'].$type.DS.$this->settings['controllersPath']))) {
                $this->controllerAssets[$type] = FALSE;
            }
        }
	}

    /**
     *
     */
    protected function _setupControllerAssets() {
        foreach (array_keys($this->controllerAssets, TRUE) as $type) {
            $this->_setupControllerAssetType($type);
        }
    }

    /**
     * @param $type
     */
    protected function _setupControllerAssetType($type) {
        $controller = Inflector::underscore($this->request->params['controller']);
        $action = Inflector::underscore($this->request->params['action']);

        switch ($type) {
            case 'css':
                if (file_exists(substr(WWW_ROOT,0,strlen(WWW_ROOT)-1).DS.$this->settings['theme'].$type.DS.$this->settings['controllersPath'].$controller.'.css')) {
                    $this->$type($this->settings['controllersPath'].$controller);
                }
                if (file_exists(substr(WWW_ROOT,0,strlen(WWW_ROOT)-1).DS.$this->settings['theme'].$type.DS.$this->settings['controllersPath'].$controller.DS.$action.'.css')) {
                    $this->$type($this->settings['controllersPath'].$controller.DS.$action);
                }
                break;
            case 'js':
                if (file_exists(substr(WWW_ROOT,0,strlen(WWW_ROOT)-1).DS.$this->settings['theme'].$type.DS.$this->settings['controllersPath'].$controller.'.js')) {
                    $this->$type(DS.$this->settings['theme'].$type.DS.$this->settings['controllersPath'].$controller.'.js');
                }
                if (file_exists(substr(WWW_ROOT,0,strlen(WWW_ROOT)-1).DS.$this->settings['theme'].$type.DS.$this->settings['controllersPath'].$controller.DS.$action.'.js')) {
                    $this->$type(DS.$this->settings['theme'].$type.DS.$this->settings['controllersPath'].$controller.DS.$action.'.js');
                }
                break;
        }
    }

    /**
     *
     */
    protected function _setupJsHelpers() {
        /**
         * @var AssetBlock $block
         */
        $block = $this->assets[$this->settings['jsHelpersBlock']];

        $collection = $block->getCollection();

        foreach (array_reverse($this->jsHelpers) as $path) {
            $collection->insert(new JsAsset($path));
        }
        
        $globals = array(
        	'$css.path' => '/css/', 
        	'$script.path' => '/js/'
    	);
        
        foreach ($globals as $name => $val) {
        	$collection->insert(new JsGlobalAsset($name, $val));
        }
    }
}
?>