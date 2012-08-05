<?php
/**
 * AssetLoader Helper
 *
 * Assists in outputting prerequisite (required) and asynchronous (lazy-loaded)
 *  CSS and JavaScript assets with the help of the HtmlHelper.
 */
class AssetLoaderHelper extends AppHelper {
	/**
	 * View helpers required by this helper
	 */
	public $helpers = array('Html', 'AutoAsset.Less');
	
	public $settings = array(
		'assetsVar' => 'assets',
	);
	
	var $types = array('css', 'less', 'js');
	
	var $typeHelpers = array(
		'less' => 'Less',
	);

	/**
	 * Indicates whether or not the required (prerequisite) files
	 *  have already been output;
	 */
	var $requiredDone = array(
		'css' => false,
		'less' => false,
		'js' => false,
	);
	
	private $_assets = array();
/**
 * Constructor.
 *
 * @access public
 */
    function __construct(View $View, $settings = array()) {
		$this->settings = array_merge($this->settings, (array) $settings);

        if (!isset($View->viewVars[$this->settings['assetsVar']])) {
        	return;
        }

        $this->_assets = $View->viewVars[$this->settings['assetsVar']];

		parent::__construct($View, (array)$settings);
    }

    public function conditional($condition = null, $type = null, $helper = null) {
        $output = '';

        if (empty($condition)) {
            foreach (array_keys((array) $this->_assets['conditionals']) as $condition) {
                if (!empty($output)) {
                    $output .= "\n";
                }

                $output .= $this->conditional($condition, $type);
            }

            return $output;
        }

        if (empty($type)) {
            foreach ($this->types as $currentType) {
                if (!empty($output)) {
                    $output .= "\n";
                }

                $output .= $this->conditional($condition, $currentType);
            }

            return $output;
        }

        if (empty($this->_assets['conditionals'][$condition][$type])) {
            return $output;
        }

        $helper = $this->getHelper($helper, $type);

        $output .= $this->asset($type, $this->_assets['conditionals'][$condition][$type], $helper);

        if (!empty($output)) {
            if (strpos($condition, '(') !== 0) {
                $condition = "($condition)";
            }

            $output = "<!--[if $condition]><!-->\n\t$output\n<!--<![endif]-->";
        }

        return $output;
    }

    public function asset($type, $path, $helper) {
        $output = '';

        foreach ((array) $path as $asset) {
            if (!empty($output)) {
                $output .= "\n";
            }

            switch($type) {
                case 'css':
                case 'less':
                    $output .= $helper->css($asset, null, array('inline' => true));
                    break;
                case 'js':
                default:
                    $output .= $helper->script($asset, array('inline' => true));
                    break;
            }
        }
    }

    protected function getHelper($helper = null, $type = 'js') {
        if (empty($helper) && isset($this->typeHelpers[$type])) {
            $helper = $this->typeHelpers[$type];
        }

        if (is_string($helper) && is_object($this->$helper)) {
            $helper = $this->$helper;
        }

        if (!is_object($helper)) {
            $helper = $this->Html;
        }

        return $helper;
    }

	/**
	 * Returns a string containing the HTML output for the required Javascript
	 *  and CSS files referenced within $assets
	 */
	public function required($type = null, $helper = null) {
		$output = '';
		
		$workLeft = $this->_workLeft();
		
		if (!$workLeft) {
			return $output;
		}
		
		if (empty($type)) {
			$type = $this->types;
		}
		
		if (is_array($type)) {
			foreach ($type as $currentType) {
				if (!empty($output)) {
					$output .= "\n";
				}
				
				$output .= $this->required($currentType, $helper);
			}
			
			return $output;
		}

        $helper = $this->getHelper($helper, $type);
		
		if (array_key_exists($type, $this->requiredDone) && $this->requiredDone[$type]) {
			return $output;
		}

		if (isset($this->_assets[$type]['required'])) {
            if ($type == 'css') {
                foreach ($this->_assets[$type]['required'] as $media => $assets) {
                    foreach ($assets as $asset) {
                        if (!empty($output)) {
                            $output .= "\n";
                        }

                        $output .= $helper->css($asset, null, array('media' => $media, 'inline' => true));
                    }
                }
            } else {
                $output .= $this->loadRequiredAssets($this->_assets[$type]['required'], $type, $helper);
            }
		}

		$this->requiredDone[$type] = true;

		return $output;
	}

    private function loadRequiredAssets($assets, $type, $helper) {
        $output = '';

        foreach ((array) $assets as $asset) {
            if (!empty($output)) {
                $output .= "\n";
            }

            switch($type) {
                case 'css':
                case 'less':
                    $output .= $helper->css($asset, null, array('inline' => true));
                    break;
                case 'js':
                default:
                    $output .= $helper->script($asset, array('inline' => true));
                    break;
            }

        }

        return $output;
    }
	
	public function base($url = null, $html5 = true) {
		if (empty($url)) {
			if (!empty($this->_assets['baseUrl'])) {
				$url = $this->_assets['baseUrl'];
			} else {
				$url = Router::url('/', true);
			}
		}
		
		$closing = $html5 ? '' : ' /';
		
		$output = '<base href="'.$url.'"'.$closing.'>';
		
		return $output;
	}
	
	public function meta($early = false) {
		$output = '';
		
		$setting = $early ? 'earlyMeta' : 'meta';
		
		if (!isset($this->_assets[$setting])) {
			return $output;
		}
		
		foreach ((array)$this->_assets[$setting] as $meta) {
			if (!isset($meta[0])) {
				continue;
			}
			if (!isset($meta[1])) {
				$meta[1] = null;
			}
			if (!isset($meta[2])) {
				$meta[2] = array();
			}
			if (!empty($output)) {
				$output .= "\n";
			}
			
			$meta[2]['inline'] = true;
			
			$output .= $this->Html->meta($meta[0], $meta[1], $meta[2]);
		}
		
		return $output;
	}

	public function globals() {
		$output = '';

		if (!is_array($this->_assets['globals'])) {
			return $output;
		}

		foreach ($this->_assets['globals'] as $key => $value) {
			if (!empty($output)) {
				$output .= "\n";
			}

			$output .= "var $key = ";

			if (is_array($value)) {
				$output .= json_encode($value);
			} elseif (is_bool($value)) {
				$output .= ($value) ? 'true' : 'false';
			} elseif (is_string($value)) {
				$output .= '\''.$value.'\'';
			} else {
				$output .= $value;
			}

			$output .= ';';
		}

		$output = $this->Html->scriptBlock($output, array('inline' => true));

		return $output;
	}

	/**
	 * Returns the HTML output to lazy-load the configured Javascript and Css
	 *
	 * Also includes the required CSS and JS if it has not already been output with the
	 *  required($assets) function, since they need to appear before the async assets.
	 */
	public function load() {
		$output = '';
		
		if (isset($this->_assets['css'])) {
			$output .= "\$css.path = '/css/';\n";
		}
		if (isset($this->_assets['less'])) {
			$output .= "\$less.path = '/css/';\n";
		}
		if (isset($this->_assets['js'])) {
			$output .= "\$script.path = '/js/';\n";
		}
		
		foreach ($this->types as $type) {
			if (!empty($this->_assets[$type]['async'])) {
				foreach ((array) $this->_assets[$type]['async'] as $asset) {
					if (!empty($output)) {
						$output .= "\n";
					}

					$o = (is_array($asset))
						? "['".implode("', '", $asset)."']"
						: "'$asset'";
					
					if ($type == 'js') {
						$type = 'script';
					}
					$output .= "\$$type($o);";
				}
			}
		}

		$output = $this->Html->scriptBlock($output, array('inline' => true));

		if ($this->_workLeft()) {
			$output = $this->required() . "\n\n" . $output;
		}

		return $output;
	}
	
	private function _workLeft() {
		foreach ($this->requiredDone as $done) {
			if (!$done) {
				return true;
			}
		}
		
		return false;
	}
}
?>