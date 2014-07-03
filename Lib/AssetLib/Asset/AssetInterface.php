<?php
namespace AssetLib\Asset;

/**
 * Represents the most basic class that all assets inherit.
 */
interface AssetInterface {
    /**
     * Returns a boolean value indicating whether this asset and its data are valid and able to be used.
     *
     * @abstract
     * @return bool
     */
    public function isValid();

    /**
     * Returns a string representation of the type of asset this is
     *
     * @return mixed
     */
    public function getAssetType();

    /**
     * Returns the options array for this asset
     *
     * @return mixed
     */
    public function getOptions();

    /**
     * Returns the named option if it exists, or null if not
     *
     * @param $name
     *
     * @return mixed
     */
    public function getOption($name);
}

?>