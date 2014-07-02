<?php
namespace AssetLib\Error\Exception;

/**
 *
 */
class AssetBlockNotFoundException extends \Exception {
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = null, $code = 500) {
        if (!is_string($message)) {
            $block = (empty($message['assetBlock'])) ? 'The specified asset block' :
                "Asset block {$message['assetBlock']}";

            $message = "$block does not exist.";
        }

        parent::__construct($message, $code);
    }
}

?>