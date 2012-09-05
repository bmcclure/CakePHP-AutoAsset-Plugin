<?php
require_once 'AssetInterface.php';
require_once 'BaseAsset.php';

/**
 * Represents any asset that is a file
 */
abstract class FileAsset extends BaseAsset implements AssetInterface {
    /**
     * @var string
     */
    protected $path;

    protected $absolute = false;

    /**
     * @var string
     */
    protected $basePath = null;

    /**
     * @param $path
     * @param string $basePath
     */
    public function __construct($path, $basePath = WEBROOT_DIR) {
        $this->path = $path;

        if ($this->_isAbsoluteUrl($path)) {
            $this->absolute = true;
        } else {
            $this->basePath = $basePath;
        }



        parent::__construct();
    }

    /**
     * @param $path
     * @return bool
     */
    protected function _isAbsoluteUrl($path) {
        if ((substr($path, 0, 7) != 'http://') && (substr($path, 0, 8) != 'https://')) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isValid() {
        $fullPath = $this->getFullPath();

        if ($this->absolute) {
            return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $fullPath);
        } else {
            return file_exists($fullPath) && is_readable($fullPath);
        }


    }

    /**
     * @return string
     */
    protected function getFullPath() {
        if ($this->absolute) {
            return $this->path;
        } else {
            return $this->_buildFullPath();
        }
    }

    protected function _buildFullPath() {
        return $this->basePath.DS.$this->path;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return array($this->path);
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }
}
?>