<?php
class AssetLoaderHelper extends AppHelper {
/**
 * View helpers required by this helper
 *
 * @var array
 * @access public
 */
    public $helpers = array('Html');

	var $requiredDone = false;

	public function required($assets) {
		$output = '';

		if (isset($assets['css']['required'])) {
			foreach ($assets['css']['required'] as $asset) {
				if (!empty($output)) {
					$output .= "\n";
				}

				$output .= $this->Html->css($asset, null, array('inline' => true));
			}
		}

		if (!empty($output)) {
			$output .= "\n";
		}

		if (isset($assets['js']['required'])) {
			foreach ($assets['js']['required'] as $asset) {
				if (!empty($output)) {
					$output .= "\n";
				}

				$output .= $this->Html->script($asset, array('inline' => true));
			}
		}

		$this->requiredDone = true;

		return $output;
	}

	public function load($assets) {
		$output = '';

		if (isset($assets['css']['async']) && (!empty($assets['css']['async']))) {
			$output .= '$css.path = \'/css/\';';

			foreach ($assets['css']['async'] as $asset) {
				if (!empty($output)) {
					$output .= "\n";
				}

				$o = (is_array($asset))
					? '[\''.implode('\', \'', $asset).'\']'
					: '\''.$asset.'\'';

				$output .= '$css('.$o.');';
			}
		}

		if (!empty($output)) {
			$output .= "\n";
		}

		if (isset($assets['js']['async']) && (!empty($assets['js']['async']))) {
			if (!empty($output)) {
				$output .= "\n";
			}

			$output .= '$script.path = \'/js/\';';

			foreach ($assets['js']['async'] as $asset) {
				if (!empty($output)) {
					$output .= "\n";
				}

				$o = (is_array($asset))
					? '[\''.implode('\', \'', $asset).'\']'
					: '\''.$asset.'\'';

				$output .= '$script('.$o.');';
			}
		}

		$output = $this->Html->scriptBlock($output, array('inline' => true));

		if (!$this->requiredDone) {
			$output = $this->required($assets) . "\n\n" . $output;
		}

		return $output;
	}
}
?>