<?php
require_once 'AssetInterface.php';
require_once 'BaseAsset.php';


/**
 * Represents an asset tha is a key/value pair
 */
abstract class ValueAsset extends BaseAsset implements AssetInterface {
    /**
     * @var
     */
    protected $name;

    /**
     * @var null
     */
    protected $value;

    /**
     * @param $name
     * @param null $value
     */
    public function __construct($name, $value = NULL) {
        $this->name = $name;
        $this->value = $value;

        parent::__construct();
    }

    /**
     * @return bool
     */
    public function isValid() {
        return (!empty($this->name));
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getValue() {
        return $this->value;
    }
}
?>