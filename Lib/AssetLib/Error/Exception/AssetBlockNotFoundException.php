<?php
/**
 *
 */
class AssetBlockNotFoundException extends CakeException {
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = NULL, $code = 500) {
        if (!is_string($message)) {
            $block = 'The specified asset block';
            if (!empty($message['assetBLock'])) {
                $block = 'Asset block '.$message['assetBLock'];
            }


            $message = "$block does not exist.";
        }

        parent::__construct($message, $code);
    }
}
?>