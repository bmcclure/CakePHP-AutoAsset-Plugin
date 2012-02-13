<?php
class LessHelper extends AppHelper {
	public function css($path, $rel = null, $options = array()) {
		$options += array('inline' => true);
		if (is_array($path)) {
			$out = '';
			foreach ($path as $i) {
				$out .= "\n\t" . $this->css($i, $rel, $options);
			}
			if ($options['inline'])  {
				return $out . "\n";
			}
			return;
		}

		if (strpos($path, '//') !== false) {
			$url = $path;
		} else {
			if ($path[0] !== '/') {
				$path = CSS_URL . $path;
			}

			if (strpos($path, '?') === false) {
				if ((substr($path, -5) !== '.less') && (substr($path, -4) !== '.css')) {
					$path .= '.less';
				}
			}
			$url = $this->assetTimestamp($this->webroot($path));

			if (Configure::read('Asset.filter.css')) {
				$pos = strpos($url, CSS_URL);
				if ($pos !== false) {
					$url = substr($url, 0, $pos) . 'ccss/' . substr($url, $pos + strlen(CSS_URL));
				}
			}
		}

		if ($rel == 'import') {
			$out = sprintf($this->_tags['style'], $this->_parseAttributes($options, array('inline'), '', ' '), '@import url(' . $url . ');');
		} else {
			if ($rel == null) {
				$rel = 'stylesheet';
				
				if (substr($path, -5) === '.less') {
					$rel .= '/less';
				}
			}
			
			$out = sprintf($this->_tags['css'], $rel, $url, $this->_parseAttributes($options, array('inline'), '', ' '));
		}

		if ($options['inline']) {
			return $out;
		} else {
			$this->_View->addScript($out);
		}
	}
}
?>