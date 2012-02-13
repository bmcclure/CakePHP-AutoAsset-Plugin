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
		'asyncLess' => null, // (null, string, array)
		'requiredJs' => null, // (null, string, array)
		'requiredCss' => null, // (null, string, array)
		'requiredLess' => null, // (null, string, array)
		'globals' => null, // (null, associative array)
		'meta' => null, // (null, array of arrays matching the three parameters allowed by HtmlHelper->meta())
		'earlyMeta' => null, // (same as meta, but meant to be output at the top of the page)
		'controllersPath' => 'controllers', // (string)
		'scriptJs' => '/auto_asset/js/script.min', // (null, string)
		'cssJs' => '/auto_asset/js/css', // (null, string)
		'namespaceJs' => '/auto_asset/js/namespace', // (null, string)
		'urlJs' => '/auto_asset/js/url', // (null, string)
		'lessLib' => '/auto_asset/js/less-1.2.1.min', // (null, string)
		'lessJs' => '/auto_asset/js/less', // (null, string)
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
	
	protected $controllerLess = true;

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
		if (!is_array($this->settings['globals'])) {
			$this->settings['globals'] = (array)$this->settings['globals'];
		}
		$this->settings['globals'][$name] = $value;
	}
	
	public function js($path, $async = false) {
		$setting = ($async) ? 'asyncJs' : 'requiredJs';
		
		if (!is_array($this->settings[$setting])) {
			$this->settings[$setting] = (array)$this->settings[$setting];
		}
		$this->settings[$setting][] = $path;
	}
	
	public function css($path, $async = false) {
		$setting = ($async) ? 'asyncCss' : 'requiredCss';
		
		if (!is_array($this->settings[$setting])) {
			$this->settings[$setting] = (array)$this->settings[$setting];
		}
		$this->settings[$setting][] = $path;
	}
	
	public function less($path, $async = false) {
		$setting = ($async) ? 'asyncLess' : 'requiredLess';
		
		if (!is_array($this->settings[$setting])) {
			$this->settings[$setting] = (array)$this->settings[$setting];
		}
		$this->settings[$setting][] = $path;
	}
	
	public function meta($type, $url = null, $options = array(), $early = false) {
		if (isset($options['early'])) {
			$early = $options['early'];
			
			unset($options['early']);
		}

		$setting = ($early) ? 'earlyMeta' : 'meta';
		
		if (!is_array($this->settings[$setting])) {
			$this->settings[$setting] = (array)$this->settings[$setting];
		}
		
		if (is_array($type)) {
			$this->settings[$setting][] = array($type, $url, $options);
			
			return;
		}
		
		$types = array(
			'author' => array('name' => 'author', 'link' => ''),
			'viewport' => array('name' => 'viewport', 'content' => ''),
			'sitemap' => array('type' => 'application/xml', 'rel' => 'sitemap', 'title' => Inflector::humanize($type), 'link' => ''),
			'search' => array('type' => 'application/opensearchdescription+xml', 'rel' => 'search', 'title' => Inflector::humanize($type), 'link' => $url),
			'application-name' => array('name' => 'application-name', 'content' => $url),
			'msapplication-tooltip' => array('name' => 'msapplication-tooltip', 'content' => $url),
			'msapplication-starturl' => array('name' => 'msapplication-starturl', 'content' => $url),
			'msapplication-task' => array('name' => 'msapplication-task', 'content' => 'name=%s;action-uri=%s;icon-uri=%s'),
			'canonical' => array('rel' => 'canonical', 'link' => $url),
			'shortlink' => array('rel' => 'shortlink', 'link' => $url),
			'pingback' => array('rel' => 'pingback', 'link' => $url),
			'imagetoolbar' => array('http-equiv' => 'imagetoolbar', 'content' => $url),
			'robots' => array('name' => 'robots', 'content' => ''),
			'dns-prefetch' => array('rel' => 'dns-prefetch', 'link' => $url),
		);
		
		switch ($type) {
			case 'author':
				$types['author']['link'] = (empty($url)) ? '/humans.txt' : $url;
				break;
			case 'viewport':
				$types['viewport']['content'] = (empty($url)) ? 'width=device-width, initial-scale=1' : $url;
				break;
			case 'sitemap':
				$types['sitemap']['link'] = (empty($url)) ? '/sitemap.xml' : $url;
				break;
			case 'search':
				$types['search']['link'] = (empty($url)) ? '/opensearch.xml' : $url;
				break;
			case 'robots':
				$types['robots']['content'] = (empty($url)) ? 'noindex' : $url;
				break;
			case 'msapplication-task':
				$name = '';
				if (isset($options['name'])) {
					$name = $options['name'];
					unset($options['name']);
				}
				
				$action = '';
				if (isset($url)) {
					$action = $url;
				} elseif (isset($options['action'])) {
					$ation = $options['action'];
					unset($options['action']);
				} elseif (isset($options['action-uri'])) {
					$action = $options['action-uri'];
					unset($options['action-uri']);
				}
				
				$icon = '';
				if (isset($options['icon'])) {
					$icon = $options['icon'];
					unset($options['icon']);
				} elseif (isset($options['icon-uri'])) {
					$icon = $options['icon-uri'];
					unset($options['icon-uri']);
				}
				
				$types['msapplication-task']['content'] = sprintf($types['msapplication-task']['content'], $name, $action, $icon);
				break;
			case 'og':
				if (is_array($url)) {
					foreach ($url as $key => $val) {
						$this->meta(array('property' => "og:$key", 'content' => $val), null, $options, $early);
					}
					return;
				}
				
				if (is_string($options)) {
					$type = array('property' => "og:$url", 'content' => $options);
					$url = null;
					$options = array();
				} elseif (is_string($url)) {
					$content = '';
					if (isset($options['content'])) {
						$content = $options['content'];
						unset($options['content']);
					}
					
					$type = array('property' => "og:$url", 'content' => $content);
					$url = null;
				}
				break;
			default:
				if ((strlen($type) > 3) && (substr($type, 0, 3) == 'og:')) {
					$types[$type] = array('property' => $type, 'content' => $url);
				}
				break;
		}
		
		if (array_key_exists($type, $types)) {
			$type = $types[$type];
			$url = null;
		}
		
		$this->settings[$setting][] = array($type, $url, $options);
	}
	
	protected function _mergeSetting($setting, $value = null) {
		if (!is_array($this->settings[$setting])) {
			$this->settings[$setting] = empty($this->settings[$setting]) ? array() : (array)$this->settings[$setting];
		}
		
		$this->settings[$setting] = array_merge($this->settings[$setting], (array)$value);
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
			$requiredLess = array();
			$asyncCss = $this->controllerCss ? $this->_getValidFiles($controllerPaths, 'css') : array();
			$asyncLess = $this->controllerLess ? $this->_getValidFiles($controllerPaths, 'css', '', '.less') : array();
			$asyncJs = $this->controllerJs ? $this->_getValidFiles($controllerPaths, 'js') : array();
		} else {
			$requiredCss = $this->_getValidFiles($this->settings['requiredCss'], 'css');
			$requiredLess = $this->_getValidFiles($this->settings['requiredLess'], 'css', '', '.less');
			$requiredJs = array_merge($this->_requiredJs(), $this->_getValidFiles($this->settings['requiredJs'], 'js'));

			$acss = (array) $this->settings['asyncCss'];
			if ($this->controllerCss) {
				$acss[] = $controllerPaths;
			}
			
			$aless = (array)$this->settings['asyncLess'];
			if ($this->controllerLess) {
				$aless[] = $controllerPaths;
			}

			$ajs = (array) $this->settings['asyncJs'];
			if ($this->controllerJs) {
				$ajs[] = $controllerPaths;
			}

			$asyncCss = $this->_getValidFiles($acss, 'css');
			$asyncLess = $this->_getValidFiles($aless, 'css', '', '.less');
			$asyncJs = $this->_getValidFiles($ajs, 'js');
		}

		$assets = array(
			'css' => array(
				'required' => $requiredCss,
				'async' => $asyncCss,
			),
			'less' => array(
				'required' => $requiredLess,
				'async' => $asyncLess,
			),
			'js' => array(
				'required' => $requiredJs,
				'async' => $asyncJs,
			)
		);
		
		foreach (array('globals', 'earlyMeta', 'meta') as $setting) {
			if (!empty($this->settings[$setting])) {
				$assets[$setting] = $this->settings[$setting];
			}
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
		
		// Tack on a trailing slash if there isn't one there already
		if (substr($this->settings['controllersPath'], strlen($this->settings['controllersPath'])) != DS) {
			$this->settings['controllersPath'] .= DS;
		}
		
		// TODO: Check that /js/$controllersPath exists or set $this->controllerJs to false
		// TODO: Check that /css/$controllersPath exists or set $this->controllerCss to false
	}

	/**
	 * returns the internal required JS merged with the configured prerequisite JS files
	 */
	private function _requiredJs() {
		$appRequired = array(
			$this->settings['lessLib'],
			$this->settings['namespaceJs'],
			$this->settings['scriptJs'],
			$this->settings['cssJs'],
			$this->settings['lessJs'],
			$this->settings['urlJs'],
			
		);

		$required = array();

		foreach ($appRequired as $path) {
			if (empty($path)) {
				continue;
			}
			
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
	private function _getValidFiles($files, $fileType = 'js', $path = '', $ext = null) {
		$result = array();

		foreach ((array) $files as $file) {
			if (is_array($file)) {
				$result = array_merge($result, $this->_getValidFiles($file, $fileType, $path, $ext));
				
				continue;
			}

			if ($this->_isAbsoluteUrl($file)) {
				$result[] = $file;

				continue;
			}

			$file = $path . $file;
			
			if (is_null($ext)) {
				$ext = ".$fileType";
			}
			
			$ext = (substr($file, strlen($file) - strlen($ext) - 1) == $ext) ? '' : $ext;
			
			if ($this->_isValid(WWW_ROOT . $fileType . DS . $file . $ext)) {
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