<?php
/**
 *
 */
class AssetTypeUnsupportedException extends CakeException {
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = NULL, $code = 500) {
        if (!is_string($message)) {
            $type = 'The asset type being used';
            if (!empty($message['type'])) {
                $type = 'Asset type '.$message['type'];
            }

            $class = 'this class';
            if (!empty($message['class'])) {
                $class = 'class '.$message['class'];
            }

            $message = "$type is not supported by $class.";
        }

        parent::__construct($message, $code);
    }
}
?>