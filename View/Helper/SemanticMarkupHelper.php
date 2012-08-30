<?php
App::uses('AppHelper', 'View/Helper');

class SemanticMarkupHelper extends AppHelper {
    protected $baseUrl;

    public function __construct(View $View, $settings = array()) {
        if (empty($settings['baseUrl'])) {
            $settings['baseUrl'] = Router::url('/', true);
        }

        $this->baseUrl = $settings['baseUrl'];

        parent::__construct($View, $settings);
    }

    public function conditionalHtmlTag($class = "no-js") {
        $output = '<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->';
        $output .= "\n".'<!--[if IE 7]>    <html class="'.$class.' lt-ie9 lt-ie8" lang="en"> <![endif]-->';
        $output .= "\n".'<!--[if IE 8]>    <html class="'.$class.' lt-ie9" lang="en"> <![endif]-->';
        $output .= "\n".'<!--[if gt IE 8]><!--> <html class="'.$class.'" lang="en"> <!--<![endif]-->';

        return $output;
    }

    public function endConditionalHtmlTag() {
        return '</html>';
    }

    public function base($url = NULL, $html5 = TRUE) {
        if (empty($url)) {
            $url = $this->baseUrl;
        }

        $closing = $html5 ? '' : ' /';

        $output = '<base href="'.$url.'"'.$closing.'>';

        return $output;
    }

    public function chromeFrameBar($message = null, $minSupportedIE = "8") {
    	if (empty($message)) {
    		$message = 'Your browser is <em>unsupported</em>. <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.';
    	}
    	
        $output = "<!--[if lt IE $minSupportedIE]>";
        $output .= "<p class=chromeframe>$message</p>";
        $output .= '<![endif]-->';

        return $output;
    }
}
?>