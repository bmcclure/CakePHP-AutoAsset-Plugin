<?php
require_once 'AssetInterface.php';

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
    //abstract public function isValid();
}
?>