<?php
class AssetGathererComponent extends Component {
	public $settings = array(
		'mainJs' => 'main',
		'mainCss' => 'main',
		'requiredJs' => array(),
		'requiredCss' => array(),
		'controllersPath' => 'controllers',
		'includeNamespaceJs' => '/auto_asset/js/namespace',
		'includeScriptJs' => '/auto_asset/js/script.min',
		'includeCssJs' => '/auto_asset/js/css',
		'includeUrlJs' => '/auto_asset/js/url',
	);

	protected $controller = null;

	protected $request = null;

	public function __construct(ComponentCollection $collection, $settings = array()) {
		$settings = array_merge($this->settings, (array)$settings);

		if (isset($settings['controllersPath']) && !empty($settings['controllersPath'])) {
			if (substr($settings['controllersPath'], strlen($settings['controllersPath'])) != DS) {
				$settings['controllersPath'] .= DS;
			}
		}

		$this->controller = $collection->getController();
		$this->request = $this->controller->request;

		parent::__construct($collection, $settings);
	}

	public function getAssets() {
		extract($this->settings);

		$controller = Inflector::underscore($this->controller->params['controller']);
		$action = Inflector::underscore($this->controller->params['action']);

		if ($this->request->isAjax()) {
			$assets = array(
				'css' => array('async' => $this->_getValidFiles(array(
					$controllersPath.DS.$controller,
					$controllersPath.DS.$controller.DS.$action,
				), 'css')),
				'js' => array('async' => $this->_getValidFiles(array(
					$controllersPath.DS.$controller,
					$controllersPath.DS.$controller.DS.$action,
				), 'js')),
			);
		} else {
			$required = array();
			if ($includeNamespaceJs) {
				$required[] = $includeNamespaceJs;
			}
			if ($includeScriptJs) {
				$required[] = $includeScriptJs;
			}
			if ($includeCssJs) {
				$required[] = $includeCssJs;
			}
			if ($includeUrlJs) {
				$required[] = $includeUrlJs;
			}

			$requiredJs = array_merge($requiredJs, $required);

			$assets = array(
				'css' => array(
					'required' => $requiredCss,
					'async' => $this->_getValidFiles(array(
						$mainCss,
						$controllersPath.DS.$controller,
						$controllersPath.DS.$controller.DS.$action,
					), 'css'),
				),
				'js' => array(
					'required' => $requiredJs,
					'async' => $this->_getValidFiles(array(
						$mainJs,
						$controllersPath.DS.$controller,
						$controllersPath.DS.$controller.DS.$action,
					), 'js')
				),
			);
		}

		return $assets;
	}

	private function _getValidFiles($files, $fileType = 'js', $path = '') {
		$result = array();

		if (!is_array($files)) {
			return file_exists(WWW_ROOT . $fileType . DS . $file)
			 ? array(str_replace('\\', '/', $path.$file.'.'.$fileType))
			 : array();
		}

		foreach ($files as $file) {
			if (is_array($file)) {
				$result = array_merge($result, $this->_getValidFiles($file, $fileType, $path));
				continue;
			}

			$file = $path . $file;
			if (file_exists(WWW_ROOT . $fileType . DS . $file . '.' . $fileType)) {
				$file = str_replace('\\', '/', $file);

				$result[] = $file;
			}
		}

		return $result;
	}
}
?>