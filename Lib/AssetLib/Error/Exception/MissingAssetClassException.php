<?php
namespace AssetLib\Error\Exception;

/**
 *
 */
class MissingAssetClassException extends \Exception {
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = NULL, $code = 500) {
        if (!is_string($message)) {
            $class = (empty($message['class'])) ? 'The specified asset class' : "Asset class {$message['class']}";

            $package = 'Lib/Asset';
            if (!empty($message['plugin'])) {
                $package = $message['plugin'].".$package";
            }

            $message = "$class could not be located in package $package";
        }

        parent::__construct($message, $code);
    }
}
?>