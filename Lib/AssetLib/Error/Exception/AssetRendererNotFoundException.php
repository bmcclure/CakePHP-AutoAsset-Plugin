<?php
/**
 *
 */
class AssetRendererNotFoundException extends CakeException {
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = NULL, $code = 500) {
        if (!is_string($message)) {
            $renderer = 'The specified asset renderer';
            if (!empty($message['renderer'])) {
                $renderer = 'Asset renderer '.$message['renderer'];
            }

            $message = "$renderer was not found in this helper.";
        }

        parent::__construct($message, $code);
    }
}
?>