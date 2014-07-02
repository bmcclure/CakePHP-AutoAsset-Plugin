<?php
namespace AssetLib\Asset;

/**
 * Represents a CSS file to be used
 */
class CssAsset extends FileAsset implements AssetInterface {
    /**
     * @var string
     */
    protected $rel;

    /**
     * @var string
     */
    protected $media;

    /**
     * @param $path
     * @param string $rel
     * @param string $media
     * @param string $basePath
     *
     * @internal param string $mediaType
     */
    public function __construct($path, $rel = 'stylesheet', $media = 'screen', $basePath = CSS) {
        $this->rel = $rel;
        $this->media = $media;

        parent::__construct($path, $basePath);
    }

    /**
     * @return string
     */
    protected function _buildFullPath() {
        $fullPath = parent::_buildFullPath();

        return file_exists($fullPath) ? $fullPath : "$fullPath.css";
    }

    /**
     * @return string
     */
    public function getRel() {
        return $this->rel;
    }

    /**
     * @return string
     */
    public function getMedia() {
        return $this->media;
    }
}

?>