<?php
namespace AssetLib\Asset;

/**
 * The basic abstract Asset class that most assets extend.
 */
abstract class BaseAsset implements AssetInterface {
    protected $options;

    /**
     * Sets up an instance of BaseAsset for an inheriting class
     */
    public function __construct($options = []) {
        $this->options = $options;
    }

    /**
     * Returns a boolean value indicating whether this asset and its data are valid and able to be used.
     *
     * @abstract
     * @return bool
     */
    abstract public function isValid();

    /**
     * @return string
     */
    public function getAssetType() {
        $reflect = new \ReflectionClass($this);
        $className = $reflect->getShortName();

        return \Inflector::variable(substr($className, 0, -5));
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param $name
     *
     * @return null
     */
    public function getOption($name) {
        if (!isset($this->options[$name])) {
            return null;
        }

        return $this->options[$name];
    }
}

?>