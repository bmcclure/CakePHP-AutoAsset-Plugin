<?php
/**
 * AssetGatherer Component
 *
 * Gathers valid CSS and JS files from the server
 *  and organizes them into an array for use with
 *  the AssetLoader Helper.
 */
class AssetGathererComponent extends Component {

	/**
	 * Configurable settings for the component
	 *
	 * asyncJs: The JavaScript files to load asynchronously (after required files)
	 * asyncCss: The CSS files to load asynchronously (after required files)
	 * requiredJs: The JavaScript prerequisites to load in the Head of the document
	 * requiredCss: The CSS prerequisites to load in the Head of the document
	 * controllersPath: The path under /css and /js that will contain your controller/action files
	 * scriptJs: The path to the version of script.js to load (or null to not load script.js)
	 * cssJs: The path to the version of css.js to load (or null to not load css.js)
	 * namespaceJs: The path to the version of namespace.js to load (or null to not load namespace.js)
	 * urlJs: The path to the version of url.js to load (or null to not load url.js)
	 */
	public $settings = array(
		'asyncJs' => 'bootstrap', // (null, string, array)
		'asyncCss' => null, // (null, string, array)
		'requiredJs' => null, // (null, string, array)
		'requiredCss' => null, // (null, string, array)
		'globals' => null, // (null, associative array)
		'controllersPath' => 'controllers', // (string)
		'scriptJs' => '/auto_asset/js/script.min', // (null, string)
		'cssJs' => '/auto_asset/js/css', // (null, string)
		'namespaceJs' => '/auto_asset/js/namespace', // (null, string)
		'urlJs' => '/auto_asset/js/url', // (null, string)
		'theme' => null // (null, string)
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
	 * Whether or not to include controller/action CSS files
	 */
	protected $controllerCss = true;

	/**
	 * Whether or not to include controller/action JS files
	 */
	protected $controllerJs = true;

	/**
	 * Overrides base class constructor, sets properties and merges supplied user settings.
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$settings = array_merge($this->settings, (array)$settings);

		$this->controller = $collection->getController();
		$this->request = $this->controller->request;

		$this->_verifyControllersPath();
		parent::__construct($collection, $settings);
	}

	/**
	 * Changes the path to controller/action files after the component has already been initialized
	 */
	public function resetControllersPath($path) {
		$this->settings['controllersPath'] = $path;

		$this->_verifyControllersPath();
	}

	public function setGlobal($name, $value = null) {
		$this->settings['globals'][$name] = $value;
	}

	/**
	 * Returns an array of all async and required JS and CSS to be loaded with the current page.
	 *
	 * For standard requests, this includes all required JS and CSS, and all async JS and CSS files,
	 *  as well as the Controller and Action JS and CSS files, if they exist.
	 *
	 * For Ajax requests, only Controller and Action CSS and JS files are included (since the rest
	 *  is likely already loaded from the previous non-Ajax request).
	 *
	 * TODO: Add a way to tell the function to load all assets even if it is an Ajax request
	 */
	public function getAssets() {
		$controller = Inflector::underscore($this->controller->params['controller']);
		$action = Inflector::underscore($this->controller->params['action']);

		$controllerPaths = array(
			$this->settings['controllersPath'].DS.$controller,
			$this->settings['controllersPath'].DS.$controller.DS.$action,
		);

		if ($this->request->is('ajax')) {
			$requiredCss = array();
			$requiredJs = array();
			$asyncCss = $this->controllerCss ? $this->_getValidFiles($controllerPaths, 'css') : array();
			$asyncJs = $this->controllerJs ? $this->_getValidFiles($controllerPaths, 'js') : array();
		} else {
			$requiredCss = $this->_getValidFiles($this->settings['requiredCss'], 'css');
			$requiredJs = array_merge($this->_requiredJs(), $this->_getValidFiles($this->settings['requiredJs'], 'js'));

			$acss = array($this->settings['asyncCss']);
			if ($this->controllerCss) {
				$acss[] = $controllerPaths;
			}

			$ajs = array($this->settings['asyncJs']);
			if ($this->controllerJs) {
				$ajs[] = $controllerPaths;
			}

			$asyncCss = $this->_getValidFiles($acss, 'css');
			$asyncJs = $this->_getValidFiles($ajs, 'js');

		}

		$assets = array(
			'css' => array(
				'required' => $requiredCss,
				'async' => $asyncCss,
			),
			'js' => array(
				'required' => $requiredJs,
				'async' => $asyncJs,
			)
		);

		if (!is_null($this->settings['globals'])) {
			$assets['globals'] = $this->settings['globals'];
		}

		return $assets;
	}

	/**
	 * Validates and standardizes the provided controllersPath setting for use with the component
	 */
	private function _verifyControllersPath() {
		if (empty($this->settings['controllersPath'])) {
			$this->controllerCss = false;
			$this->controllerJs = false;
			return;
		}

		if (substr($this->settings['controllersPath'], strlen($this->settings['controllersPath'])) != DS) {
			$this->settings['controllersPath'] .= DS;
		}

		// Check that /js/$controllersPath exists or set $this->controllerJs to false
		// Check that /css/$controllersPath exists or set $this->controllerCss to false
	}

	/**
	 * returns the internal required JS merged with the configured prerequisite JS files
	 */
	private function _requiredJs() {
		$appRequired = array(
			$this->settings['namespaceJs'],
			$this->settings['scriptJs'],
			$this->settings['cssJs'],
			$this->settings['urlJs']
		);

		$required = array();

		foreach ($appRequired as $path) {
			$required[] = $path;
		}

		return $required;
	}

	/**
	 * Traverses a (multi-dimensional) array of files and includes all valid
	 *  paths in a single-dimensional array which is returned.
	 *
	 * $files can be:
	 * - A single path (string)
	 * - An array of paths
	 * - A multi-dimensional array of paths
	 * - A mixed array of paths and arrays of paths
	 *
	 * The returned array will always be single-dimensional, and will only contain paths
	 *  from $files which actually exist on the server.
	 */
	private function _getValidFiles($files, $fileType = 'js', $path = '') {
		$result = array();

		$themePrefix = is_null($this->settings['theme'])?'':'theme'.DS.$this->settings['theme'].DS;

		if (!is_array($files)) {
			return ($this->_isAbsoluteUrl($files))
				? ($this->_isValid(WWW_ROOT . $themePrefix . $fileType . DS . $files.'.'.$fileType))
					? array(str_replace('\\', '/', $path.$files))
					: array()
				: array();
		}

		foreach ($files as $file) {
			if (is_array($file)) {
				$result = array_merge($result, $this->_getValidFiles($file, $fileType, $path));
				continue;
			}

			if ($this->_isAbsoluteUrl($file)) {
				$result[] = $file;

				continue;
			}

			$file = $path . $file;
			if ($this->_isValid(WWW_ROOT . $themePrefix . $fileType . DS . $file . '.' . $fileType)) {
				$file = str_replace('\\', '/', $file);

				$result[] = $file;
			}
		}

		return $result;
	}

	private function _isAbsoluteUrl($path) {
		if ((substr($path, 0, 7) != 'http://') && (substr($path, 0, 8) != 'https://')) {
			return false;
		}

		return true;
	}

	private function _isValid($path) {
		if ($this->_isAbsoluteUrl($path)) {
			return true;
		}
		return file_exists($path);
	}
}
?>