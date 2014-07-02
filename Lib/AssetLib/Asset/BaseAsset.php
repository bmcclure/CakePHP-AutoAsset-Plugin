<?php
namespace AssetLib\Asset;

/**
 * The basic abstract Asset class that most assets extend.
 */
abstract class BaseAsset implements AssetInterface {
    /**
     * Sets up an instance of BaseAsset for an inheriting class
     */
    public function __construct() {
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
}

?>