<?php
App::uses('AppHelper', 'View/Helper');

class AsyncAssetHelper extends AppHelper {
    public $helpers = array('Html');

    public function css($path, $rel = "stylesheet") {
        $o = (is_array($path))
            ? "['".implode("', '", $path)."']"
            : "'$path'";

        return $this->Html->scriptBlock("\$css($o);", array('inline' => true));
    }

    public function script($path) {
        $o = (is_array($path))
            ? "['".implode("', '", $path)."']"
            : "'$path'";

        return $this->Html->scriptBlock("\$script($o);", array('inline' => true));
    }
}
?>