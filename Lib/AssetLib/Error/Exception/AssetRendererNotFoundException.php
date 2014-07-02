<?php
namespace AssetLib\Error\Exception;

/**
 *
 */
class AssetRendererNotFoundException extends \Exception {
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = null, $code = 500) {
        if (!is_string($message)) {
            $renderer = (empty($message['renderer'])) ? 'The specified asset renderer' :
                "Asset renderer {$message['renderer']}";

            $message = "$renderer was not found in this helper.";
        }

        parent::__construct($message, $code);
    }
}

?>