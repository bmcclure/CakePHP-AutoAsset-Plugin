<?php
/**
 *
 */
class HelperMethodNotFoundException extends CakeException {
    /**
     * @param null $message
     * @param int $code
     */
    public function __construct($message = NULL, $code = 500) {
        if (!is_string($message)) {
            $method = 'The specified method';
            if (!empty($message['helperMethod'])) {
                $method = 'Method '.$message['helperMethod'];
            }

            $helper = 'helper';
            if (!empty($message['helper'])) {
                $helper .= ' '.$message['helper'];
            }


            $message = "$method of $helper does not exist.";
        }

        parent::__construct($message, $code);
    }
}
?>