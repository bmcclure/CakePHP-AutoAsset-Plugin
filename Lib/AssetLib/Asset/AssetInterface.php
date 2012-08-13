<?php
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
}
?>