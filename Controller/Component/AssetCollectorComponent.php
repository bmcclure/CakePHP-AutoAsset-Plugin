<?php
use AssetLib\Asset\CssAsset;
use AssetLib\Asset\JsAsset;
use AssetLib\Asset\JsGlobalAsset;
use AssetLib\Asset\MetaTagAsset;
use AssetLib\AssetBlock;

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
    private $blockDefaults = [
        'renderer' => 'default',
        'ignoreTypes' => ['ajax'],
        'conditional' => []
    ];

    /**
     * @var array
     */
    private $blocks = [
        'headTop',
        'head',
        'headBottom',
        'bodyTop',
        'body',
        'bodyBottom' => [
            'renderer' => 'async',
            'ignoreTypes' => [],
        ],
        'ie' => [
            'conditional' => ['lt IE 9'],
        ],
    ];

    /**
     * @var AssetBlock[]
     */
    private $assets = [];

    /**
     * @var array
     */
    private $jsHelpers = [
        'script' => '/auto_asset/js/script.min',
        'css' => '/auto_asset/js/css',
        'namespace' => '/auto_asset/js/namespace',
        'url' => '/auto_asset/js/url',
    ];

    /**
     * Configurable settings for the component
     *
     * blocks: an array of asset block names and settings
     * assets: an array of asset block names with arrays underneath them keyed such as 'js', 'css', etc.
     * globals: an array of asset block names with keyed arrays of global JS variables underneath.
     */
    protected $defaults = [
        'jsHelpersBlock' => 'headTop',
        'controllersPath' => 'controllers',
        'controllersBlock' => 'head',
        'theme' => '',
        'assetsVar' => 'assets',
    ];

    /**
     * Whether or not to include controller/action CSS files
     */
    public $controllerAssets = [
        'css' => true,
        'js' => true,
    ];

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
    public function __construct(ComponentCollection $collection, $settings = []) {
        $settings = $settings + $this->defaults;

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

    /**
     * Create (or overwrite) an asset block of the given game
     *
     * @param $name
     * @param array $settings
     */
    public function block($name, $settings = []) {
        $this->blocks[$name] = (array) $settings + $this->blockDefaults;

        $this->replaceAssetBlock($name, $this->createBlock($this->blocks[$name]));
    }

    /**
     * Replaces an existing asset block if one exists, preserving the internal asset collection.
     * It ane existing block does not exist, it simply sets the new block.
     *
     * @param $name
     * @param AssetBlock $block
     */
    public function replaceAssetBlock($name, AssetBlock $block) {
        if (isset($this->assets[$name])) {
            $assets = $this->assets[$name]->getCollection();

            $block->setCollection($assets);
        }

        $this->assets[$name] = $block;
    }

    /**
     * Creates a new AssetBlock object with the provided settings (which should contain 'renderer' and 'conditional'
     *
     * @param $settings
     *
     * @return AssetBlock
     */
    protected function createBlock($settings) {
        return new AssetBlock($settings['renderer'], $settings['conditional']);
    }


    /**
     * Gets an asset block ready for use. This converts an array of assets into an AssetBlock if needed.
     *
     * @param $name
     *
     * @return bool
     */
    protected function prepareBlock($name) {
        if (!empty($this->assets[$name]) && is_a($this->assets[$name], 'AssetLib\AssetBlock')) {
            return true;
        }

        if (!empty($this->blocks[$name])) {
            $this->assets[$name] = $this->createBlock($this->blocks[$name]);

            return true;
        }

        $block = new AssetBlock();
        $block->setIgnoreTypes($this->blocks[$name]['ignoreTypes']);

        $this->assets[$name] = $block;

        return true;
    }

    /**
     * Changes the path of the theme after the component has already been initialized
     */
    public function setThemeName($name) {
        $this->settings['theme'] = 'theme' . DS . $name . DS;

        $this->_setupControllersPaths();
    }

    /**
     * Changes the path to controller/action files after the component has already been initialized
     */
    public function resetControllersPath($path) {
        $this->settings['controllersPath'] = $path;

        $this->_setupControllersPaths();
    }

    /**
     * @param $name
     * @param mixed $value
     * @param string $block
     * @param array $options
     */
    public function jsGlobal($name, $value = null, $block = 'headTop', $options = []) {
        if (is_array($name) && is_array($value)) {
            // We have an array of names and a corresponding array of values
            $i = 0;
            foreach ($name as $key) {
                $this->_jsGlobalSingle($key, $value[$i], $block, $options);
                $i++;
            }
        } elseif (is_array($name)) {
            if (is_null($value)) {
                // We have an associative array of names and values
                foreach ($name as $key => $val) {
                    $this->_jsGlobalSingle($key, $val, $block, $options);
                }
            } else {
                // We have an array of values and a single key for them all
                foreach ($name as $key) {
                    $this->_jsGlobalSingle($key, $value, $block, $options);
                }
            }
        } else {
            // We have a single name/value pair
            $this->_jsGlobalSingle($name, $value, $block, $options);
        }
    }

    /**
     * @param $name
     * @param $value
     * @param $block
     * @param array $options
     */
    protected function _jsGlobalSingle($name, $value, $block, $options = []) {
        $this->_addAssetToBlock(new JsGlobalAsset($name, $value, $options), $block);
    }

    /**
     * @param $path
     * @param string $block
     * @param array $options
     */
    public function js($path, $block = 'bodyBottom', $options = []) {
        foreach ((array)$path as $file) {
            $this->_addAssetToBlock(new JsAsset($file, $options), $block);
        }
    }

    /**
     * @param $path
     * @param string $rel
     * @param string $media
     * @param string $block
     * @param array $options
     */
    public function css($path, $rel = 'stylesheet', $media = 'screen', $block = 'head', $options = []) {
        foreach ((array)$path as $file) {
            $this->_addAssetToBlock(new CssAsset($file, $rel, $media, $options), $block);
        }
    }

    /**
     * @param $type
     * @param null $url
     * @param array $options
     * @param string $block
     */
    public function meta($type, $url = null, $options = [], $block = 'head') {
        $this->_addAssetToBlock(new MetaTagAsset($type, $url, $options), $block);
    }

    /**
     * @param $asset
     * @param $block
     */
    protected function _addAssetToBlock($asset, $block) {
        $this->prepareBlock($block);

        $this->assets[$block]->add($asset);
    }

    /**
     * @return array
     */
    public function getAssets() {
        $assets = [];

        /**
         * @var AssetBlock $block
         */
        foreach ($this->assets as $name => $block) {
            if ($this->shouldIgnore($block)) {
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
     * @param AssetBlock $block
     *
     * @return bool
     */
    protected function shouldIgnore(AssetBlock $block) {
        foreach ($block->getIgnoreTypes() as $ignoreType) {
            if ($this->request->is($ignoreType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $settings
     */
    protected function _parseSettings($settings = []) {
        $recursiveSettings = ['blockDefaults', 'jsHelpers', 'controllerAssets'];
        $stringSettings = ['jsHelpersBlock', 'theme', 'controllersPath', 'controllersBlock', 'assetsVar'];

        foreach ($recursiveSettings as $setting) {
            if (!empty($settings[$setting])) {
                $this->$setting = Hash::merge($this->$setting, $settings[$setting]);
            }
        }

        foreach ($stringSettings as $setting) {
            if (isset($settings[$setting]) || !empty($settings[$setting])) {
                $this->settings[$setting] = $settings[$setting];
            }
        }

        foreach ($this->blocks as $name => $options) {
            if (is_int($name)) {
                $this->blocks[(string)$options] = $this->blockDefaults;
                unset($this->blocks[$name]);
            } else {
                $this->blocks[$name] = (array) $options + $this->blockDefaults;
            }
        }

        if (!empty($settings['blocks'])) {
            foreach ($settings['blocks'] as $block => $options) {
                if (is_int($block)) {
                    $this->blocks[(string)$options] = $this->blockDefaults;
                } else {
                    $this->blocks[$block] = (array) $options + $this->blockDefaults;
                }
            }
        }

        foreach (array_keys($this->blocks) as $name) {
            $this->prepareBlock($name);
        }

        if (!empty($settings['assets'])) {
            foreach ((array)$settings['assets'] as $block => $types) {
                foreach ($types as $type => $assets) {
                    $this->_setupAssets($block, $type, $assets);
                }
            }
        }
    }

    /**
     * @param $block
     * @param $type
     * @param $assets
     */
    private function _setupAssets($block, $type, $assets) {
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
                    $asset = (array)$asset;
                    $metaType = $asset[0];
                    $url = (isset($asset[1])) ? $asset[1] : null;
                    $options = (isset($asset[2])) ? $asset[2] : [];

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

    /**
     * Validates and standardizes the provided controllersPath setting for use with the component
     */
    private function _setupControllersPaths() {
        if (empty($this->settings['controllersPath'])) {
            $this->_disableAssets();

            return;
        }

        $this->_sanitizePaths();

        foreach ($this->controllerAssets as $type => $enabled) {
            if ($enabled && (!$this->_controllerPathExists($type))) {
                $this->_disableAssets($type);
            }
        }
    }

    /**
     * @param $type
     *
     * @return bool
     */
    private function _controllerPathExists($type) {
        return file_exists($this->_controllerBasePath($type, true));
    }

    /**
     * @param array $types
     */
    private function _disableAssets($types = []) {
        $assets = (empty($types)) ? array_keys($this->controllerAssets) : (array)$types;

        foreach ($assets as $type) {
            $this->controllerAssets[$type] = false;
        }
    }

    private function _sanitizePaths() {
        // Tack on a trailing slash if there isn't one there already
        foreach (['controllersPath', 'theme'] as $path) {
            if (substr($this->settings[$path], strlen($this->settings[$path]) - 1) != DS) {
                $this->settings[$path] .= DS;
            }
        }
    }

    /**
     *
     */
    protected function _setupControllerAssets() {
        foreach (array_keys($this->controllerAssets, true) as $type) {
            $this->_setupControllerAssetType($type);
        }
    }

    /**
     * @param $type
     */
    protected function _setupControllerAssetType($type) {
        $knownTypes = ['js', 'css'];

        if (!in_array($type, $knownTypes)) {
            return;
        }

        // Include controller file for this type if it exists
        if (file_exists($this->_controllerPathForType($type, false, true))) {
            $this->$type($this->_controllerPathForType($type, false, false));
        }

        // Include action file for this type if it exists
        if (file_exists($this->_controllerPathForType($type, true, true))) {
            $this->$type($this->_controllerPathForType($type, true, false));
        }
    }

    /**
     * @param $type
     * @param bool $includeAction
     * @param bool $absolute
     *
     * @return string
     */
    protected function _controllerPathForType($type, $includeAction = false, $absolute = false) {
        $controller = Inflector::underscore($this->request->params['controller']);

        $path = $this->_controllerBasePath($type, $absolute) . $controller;

        if ($includeAction) {
            $action = Inflector::underscore($this->request->params['action']);
            $path .= DS . $action;
        }

        if ($absolute) {
            $path .= ".$type";
        }

        return $path;
    }

    /**
     * @param $type
     * @param bool $absolute
     *
     * @return string
     */
    protected function _controllerBasePath($type, $absolute = false) {
        $path = '';

        if ($absolute) {
            $path = substr(WWW_ROOT, 0, strlen(WWW_ROOT) - 1);

            if (empty($this->settings['themePath'])) {
                $path .= DS;
            }
        }

        if (!empty($this->settings['themePath'])) {
            $path .= DS . $this->settings['themePath'] . $type . DS;
        }

        $path .= $this->settings['controllersPath'];

        return $path;
    }

    /**
     * Set up JS helpers and globals in the configured block.
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

        $globals = [
            '$css.path' => '/css/',
            '$script.path' => '/js/'
        ];

        foreach ($globals as $name => $val) {
            $collection->insert(new JsGlobalAsset($name, $val));
        }
    }
}

?>