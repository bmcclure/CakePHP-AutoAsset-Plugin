<?php
namespace AssetLib\Asset;

/**
 * Represents a Javascript file asset
 */
class JsAsset extends FileAsset implements AssetInterface {
    /**
     * @param $path
     * @param string $basePath
     */
    public function __construct($path, $basePath = JS) {
        parent::__construct($path, $basePath);
    }

    /**
     * @return string
     */
    protected function _buildFullPath() {
        $fullPath = parent::_buildFullPath();

        return file_exists($fullPath) ? $fullPath : "$fullPath.js";
    }
}

?>