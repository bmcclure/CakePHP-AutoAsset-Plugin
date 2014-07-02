<?php
namespace AssetLib\Error\Exception;

/**
 *
 */
class AssetTypeUnsupportedException extends \Exception {
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = null, $code = 500) {
        if (!is_string($message)) {
            $type = (empty($message['type'])) ? 'The asset type being used' : "Asset type {$message['type']}";
            $class = (empty($message['class'])) ? 'this class' : "class {$message['class']}";

            $message = "$type is not supported by $class.";
        }

        parent::__construct($message, $code);
    }
}

?>