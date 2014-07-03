<?php
namespace AssetLib\Asset;

/**
 * Represents an asset tha is a key/value pair
 */
abstract class ValueAsset extends BaseAsset implements AssetInterface {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param string $name
     * @param mixed $value
     * @param array $options
     */
    public function __construct($name, $value = null, $options = []) {
        $this->name = $name;
        $this->value = $value;

        parent::__construct($options);
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