<?php
App::uses('AppHelper', 'View/Helper');

/**
 * Class AsyncAssetHelper
 *
 * Mimics HtmlHelper's css and script methods, but for loading those files asynchronously.
 */
class AsyncAssetHelper extends AppHelper {
    public $helpers = ['Html'];

    public $templates = [
        'require' => "\$script.ready(%s, function () { %s };",
        'script' => '$script(%s, %s);'
    ];

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
     * Render one or more scripts asynchronously
     *
     * @param $path
     * @param array $options
     *
     * @return mixed
     */
    public function script($path, $options = []) {
        $name = (isset($options['name'])) ? $options['name'] : null;
        $require = (isset($options['require'])) ? $options['require'] : [];

        $path = (is_array($path))
            ? "['" . implode("', '", $path) . "']"
            : "'$path'";

        if (!empty($require)) {
            $require = (is_array($require))
                ? "['" . implode("', '", $require) . "']"
                : "'$require'";
        }

        $name = is_null($name) ? 'null' : '"$name"';

        $output = sprintf($this->templates['script'], $path, $name);

        if (!empty($require)) {
            $output = sprintf($this->templates['require'], $require, $output);
        }

        return $this->Html->scriptBlock($output, ['inline' => true]);
    }
}
