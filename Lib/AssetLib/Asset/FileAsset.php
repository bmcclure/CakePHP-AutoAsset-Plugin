<?php
namespace AssetLib\Asset;

/**
 * Represents any asset that is a file
 */
abstract class FileAsset extends BaseAsset implements AssetInterface {
    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
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
     *
     * @return bool
     */
    protected function _isAbsoluteUrl($path) {
        return (preg_match('%^https?://%', $path));
    }

    /**
     * @return bool
     */
    public function isValid() {
        $fullPath = $this->getFullPath();

        if ($this->absolute) {
            return (bool) filter_var($fullPath, FILTER_VALIDATE_URL);
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

    /**
     * @return string
     */
    protected function _buildFullPath() {
        return $this->basePath . DS . $this->path;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return [$this->path];
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }
}

?>