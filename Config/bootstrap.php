<?php
$path = CakePlugin::path('AutoAsset') . 'Lib';
require_once $path . DS . 'AssetLib' . DS . 'AutoAssetClassLoader.php';
$classLoader = new AutoAssetClassLoader('AssetLib', $path);
$classLoader->register();
?>