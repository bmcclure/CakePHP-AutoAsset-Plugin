<?php
require_once 'AssetInterface.php';
require_once 'BaseAsset.php';


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
    private $multi = FALSE;

    /**
     * @param $type
     * @param $url
     * @param array $options
     * @param Helper $helper
     * @param string $helperMethod
     */
    public function __construct($type, $url, $options = array()) {
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
     * @param null $url
     * @param array $options
     * @return array
     */
    protected function build($type, $url = NULL, $options = array()) {
        if (is_array($type)) {
            $this->definition = array($type, $url, $options);
        }

        $types = array(
            'author' => array(
                'name' => 'author',
                'link' => (empty($url)) ? '/humans.txt' : $url,
            ),
            'viewport' => array(
                'name' => 'viewport',
                'content' => (empty($url)) ? 'width=device-width, initial-scale=1' : $url,
            ),
            'sitemap' => array(
                'type' => 'application/xml',
                'rel' => 'sitemap',
                'title' => Inflector::humanize($type),
                'link' => (empty($url)) ? '/sitemap.xml' : $url,
            ),
            'search' => array(
                'type' => 'application/opensearchdescription+xml',
                'rel' => 'search',
                'title' => Inflector::humanize($type),
                'link' => (empty($url)) ? '/opensearch.xml' : $url,
            ),
            'application-name' => array(
                'name' => 'application-name',
                'content' => $url,
            ),
            'msapplication-tooltip' => array(
                'name' => 'msapplication-tooltip',
                'content' => $url,
            ),
            'msapplication-starturl' => array(
                'name' => 'msapplication-starturl',
                'content' => $url,
            ),
            'msapplication-task' => array(
                'name' => 'msapplication-task',
                'content' => 'name=%s;action-uri=%s;icon-uri=%s',
            ),
            'canonical' => array(
                'rel' => 'canonical',
                'link' => $url,
            ),
            'shortlink' => array(
                'rel' => 'shortlink',
                'link' => $url,
            ),
            'pingback' => array(
                'rel' => 'pingback',
                'link' => $url,
            ),
            'imagetoolbar' => array(
                'http-equiv' => 'imagetoolbar',
                'content' => $url
            ),
            'robots' => array(
                'name' => 'robots',
                'content' => (empty($url)) ? 'noindex' : $url
            ),
            'dns-prefetch' => array(
                'rel' => 'dns-prefetch',
                'link' => $url
            ),
        );

        switch ($type) {
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
                    $action = $options['action'];
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
                    $result = array();

                    foreach ($url as $key => $val) {
                        $result[] = array(array('property' => "og:$key", 'content' => $val), null, (array) $options);
                    }

                    $this->definition = $result;
                    $this->multi = TRUE;
                    return;
                }

                if (is_string($options)) {
                    $type = array('property' => "og:$url", 'content' => $options);
                    $url = NULL;
                    $options = array();
                } elseif (is_string($url)) {
                    $content = '';
                    if (isset($options['content'])) {
                        $content = $options['content'];
                        unset($options['content']);
                    }

                    $type = array('property' => "og:$url", 'content' => $content);
                    $url = NULL;
                }
                break;
            default:
                $type = (string) $type;
                if ((strlen($type) > 3) && (substr($type, 0, 3) == 'og:')) {
                    $types[$type] = array('property' => $type, 'content' => $url);
                }
                break;
        }

        if (array_key_exists($type, $types)) {
            $type = $types[$type];
            $url = NULL;
        }

        $this->definition = array($type, $url, $options);
    }

    /**
     * @return mixed
     */
    public function getType($idx = 0) {
        if ($this->multi) {
            return $this->definition[$idx][0];
        }

        return $this->definition[0];
    }

    /**
     * @return mixed
     */
    public function getUrl($idx = 0) {
        if ($this->multi) {
            return $this->definition[$idx][1];
        }

        return $this->definition[1];
    }

    /**
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
        return (!empty($this->definition[0]));
    }
}