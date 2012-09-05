<?php
require_once 'AssetInterface.php';
require_once 'BaseAsset.php';
require_once 'ValueAsset.php';

/**
 * Represents a global Javascript variable
 */
class JsGlobalAsset extends ValueAsset implements AssetInterface {
    /**
     * @return array
     */
    public function getParameters() {
        return array($this->getString());
    }

    /**
     * @return string
     */
    public function getString() {
        $output = "var {$this->getName()} = ";

        $value = $this->getValue();

        if (is_array($value)) {
            $output .= json_encode($value);
        } elseif (is_bool($value)) {
            $output .= ($value) ? 'true' : 'false';
        } elseif (is_string($value)) {
            $output .= '\''.$value.'\'';
        } else {
            $output .= $value;
        }

        $output .= ';';

        return $output;
    }
}
?>