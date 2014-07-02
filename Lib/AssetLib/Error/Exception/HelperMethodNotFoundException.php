<?php
namespace AssetLib\Error\Exception;

/**
 *
 */
class HelperMethodNotFoundException extends \Exception {
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = null, $code = 500) {
        if (!is_string($message)) {
            $method = (empty($message['helperMethod'])) ? 'The specified method' : "Method {$message['helperMethod']}";
            $helper = (empty($message['helper'])) ? 'helper' : " {$message['helper']}";

            $message = "$method of $helper does not exist.";
        }

        parent::__construct($message, $code);
    }
}

?>