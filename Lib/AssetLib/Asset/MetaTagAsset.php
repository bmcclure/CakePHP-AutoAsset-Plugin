<?php
namespace AssetLib\Asset;

/**
 * Represents a meta tag to be output on a page
 */
class MetaTagAsset extends BaseAsset implements AssetInterface {
    /**
     * @var array
     */
    private $definition;

    /**
     * @var bool
     */
    private $multi = false;

    /**
     * @param $type
     * @param $url
     * @param array $options
     */
    public function __construct($type, $url, $options = []) {
        $this->build($type, $url, $options);

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getParameters() {
        return $this->definition;
    }

    /**
     * @param $type
     * @param mixed $url
     * @param array $options
     *
     * @return array
     */
    protected function build($type, $url = null, $options = []) {
        if (is_array($type)) {
            $this->definition = [$type, $url, $options];

            return;
        }

        $types = $this->_types($url);

        switch ($type) {
            case 'msapplication-task':
                $name = $this->_setOneOf($options, 'name');
                $action = (!is_null($url)) ? $url : $this->_setOneOf($options, ['action', 'action-uri']);
                $icon = $this->_setOneOf($options, ['icon', 'icon-uri']);

                $content = sprintf($types['msapplication-task']['content'], $name, $action, $icon);

                $types['msapplication-task']['content'] = $content;

                break;
            case 'og':
                if (is_array($url)) {
                    $result = [];

                    foreach ($url as $key => $val) {
                        $result[] = [['property' => "og:$key", 'content' => $val], null, (array)$options];
                    }

                    $this->definition = $result;
                    $this->multi = true;

                    return;
                }

                if (is_string($options)) {
                    $type = ['property' => "og:$url", 'content' => $options];
                    $url = null;
                    $options = [];
                } elseif (is_string($url)) {
                    $content = $this->_setOneOf($options, 'content');

                    $type = ['property' => "og:$url", 'content' => $content];
                    $url = null;
                }
                break;
            default:
                $type = (string)$type;
                if ((strlen($type) > 3) && (substr($type, 0, 3) == 'og:')) {
                    $types[$type] = ['property' => $type, 'content' => $url];
                }
                break;
        }

        if (array_key_exists($type, $types)) {
            $type = $types[$type];
            $url = null;
        }

        $this->definition = [$type, $url, $this->_cleanOptions($options)];
    }

    /**
     * @param $options
     * @param $keys
     *
     * @return string
     */protected function _setOneOf($options, $keys = []) {
        $value = '';

        foreach ((array) $keys as $key) {
            if (isset($options[$key])) {
                $value = $options[$key];

                break;
            }
        }

        return $value;
    }

    /**
     * @param $options
     *
     * @return mixed
     */
    protected function _cleanOptions($options) {
        $dirtyKeys = [
            'name',
            'action',
            'action-uri',
            'icon',
            'icon-uri',
            'content',
        ];

        foreach ($dirtyKeys as $key) {
            unset($options[$key]);
        }

        return $options;
    }

    /**
     * @param $url
     *
     * @return array
     */
    protected function _types($url) {
        $types = [
            'author' => [
                'name' => 'author',
                'link' => (empty($url)) ? '/humans.txt' : $url,
            ],
            'viewport' => [
                'name' => 'viewport',
                'content' => (empty($url)) ? 'width=device-width, initial-scale=1' : $url,
            ],
            'sitemap' => [
                'type' => 'application/xml',
                'rel' => 'sitemap',
                'title' => 'Sitemap',
                'link' => (empty($url)) ? '/sitemap.xml' : $url,
            ],
            'search' => [
                'type' => 'application/opensearchdescription+xml',
                'rel' => 'search',
                'title' => 'Search',
                'link' => (empty($url)) ? '/opensearch.xml' : $url,
            ],
            'application-name' => [
                'name' => 'application-name',
                'content' => $url,
            ],
            'msapplication-tooltip' => [
                'name' => 'msapplication-tooltip',
                'content' => $url,
            ],
            'msapplication-starturl' => [
                'name' => 'msapplication-starturl',
                'content' => $url,
            ],
            'msapplication-task' => [
                'name' => 'msapplication-task',
                'content' => 'name=%s;action-uri=%s;icon-uri=%s',
            ],
            'canonical' => [
                'rel' => 'canonical',
                'link' => $url,
            ],
            'shortlink' => [
                'rel' => 'shortlink',
                'link' => $url,
            ],
            'pingback' => [
                'rel' => 'pingback',
                'link' => $url,
            ],
            'imagetoolbar' => [
                'http-equiv' => 'imagetoolbar',
                'content' => $url
            ],
            'robots' => [
                'name' => 'robots',
                'content' => (empty($url)) ? 'noindex' : $url
            ],
            'dns-prefetch' => [
                'rel' => 'dns-prefetch',
                'link' => $url
            ],
        ];

        return $types;
    }

    /**
     * @param int $idx
     *
     * @return mixed
     */
    public function getType($idx = 0) {
        if ($this->multi) {
            return $this->definition[$idx][0];
        }

        return $this->definition[0];
    }

    /**
     * @param int $idx
     *
     * @return mixed
     */
    public function getUrl($idx = 0) {
        if ($this->multi) {
            return $this->definition[$idx][1];
        }

        return $this->definition[1];
    }

    /**
     * @param int $idx
     *
     * @return mixed
     */
    public function getOptions($idx = 0) {
        if ($this->multi) {
            return $this->definition[$idx][2];
        }

        return $this->definition[2];
    }

    /**
     * @return int
     */
    public function numberOfValues() {
        if (!$this->multi) {
            return 1;
        }

        return count($this->definition);
    }

    /**
     * @return bool
     */
    public function isValid() {
        if (!$this->multi) {
            foreach ($this->definition as $def) {
                if (empty($def[0])) {
                    return false;
                }
            }

            return true;
        }

        return (!empty($this->definition[0]));
    }
}