<?php
App::uses('AppHelper', 'View/Helper');

/**
 * Class SemanticMarkupHelper
 *
 * @property HtmlHelper $Html
 */
class SemanticMarkupHelper extends AppHelper {
    public $helpers = ['Html'];

    protected $baseUrl;

    /**
     * Default Constructor
     *
     * @param View $View The View this helper is being attached to.
     * @param array $settings Configuration settings for the helper.
     */
    public function __construct(View $View, $settings = []) {
        if (empty($settings['baseUrl'])) {
            $settings['baseUrl'] = Router::url('/', true);
        }

        $this->baseUrl = $settings['baseUrl'];

        parent::__construct($View, $settings);
    }

    /**
     * @param string $class
     *
     * @return string
     */
    public function conditionalHtmlTag($class = "no-js") {
        $output = '<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->';
        $output .= "\n" . '<!--[if IE 7]>    <html class="' . $class . ' lt-ie9 lt-ie8" lang="en"> <![endif]-->';
        $output .= "\n" . '<!--[if IE 8]>    <html class="' . $class . ' lt-ie9" lang="en"> <![endif]-->';
        $output .= "\n" . '<!--[if gt IE 8]><!--> <html class="' . $class . '" lang="en"> <!--<![endif]-->';

        return $output;
    }

    /**
     * A fairly useless method, but if you open the HTML tag with a PHP function, closing it with text might generate
     * warnings or formatting issues in your IDE.
     *
     * @return string
     */
    public function endConditionalHtmlTag() {
        return '</html>';
    }

    /**
     * Output a <base> tag in the head of the document.
     *
     * @param null $url
     * @param bool $html5
     *
     * @return string
     */
    public function base($url = null, $html5 = true) {
        $template = '<base href="%s"%s>';

        if (empty($url)) {
            $url = $this->baseUrl;
        }

        $output = sprintf($template, $url, $html5 ? '' : ' /');

        return $output;
    }

    /**
     * Outputs a conditional <p> tag for older browsers suggesting users upgrade their browser ot switch to Google
     * Chrome.
     *
     * @param null $message
     * @param string $minSupportedIE
     *
     * @return string
     */
    public function chromeFrameBar($message = null, $minSupportedIE = "8") {
        $template = "<!--[if le IE %s]>\n<p class=\"chromeframe\">%s</p>\n<![endif]-->";

        if (empty($message)) {
            $message = 'Your browser is <em>unsupported</em>. <a href="http://browsehappy.com/">Upgrade to a different
             browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a>
             to experience this site.';
        }

        $output = sprintf($template, $minSupportedIE, $message);

        return $output;
    }
}
