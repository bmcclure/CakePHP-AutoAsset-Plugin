<?php
App::uses('AppHelper', 'View/Helper');

/**
 * Class AsyncAssetHelper
 *
 * Mimics HtmlHelper's css and script methods, but for loading those files asynchronously.
 */
class AsyncAssetHelper extends AppHelper {
    public $helpers = ['Html'];

    /**
     * @param $path
     * @param string $rel
     *
     * @return mixed
     */
    public function css($path, $rel = "stylesheet") {
        $o = (is_array($path))
            ? "['" . implode("', '", $path) . "']"
            : "'$path'";

        return $this->Html->scriptBlock("\$css($o);", ['inline' => true]);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function script($path) {
        $o = (is_array($path))
            ? "['" . implode("', '", $path) . "']"
            : "'$path'";

        return $this->Html->scriptBlock("\$script($o);", ['inline' => true]);
    }
}
