<?php
$aa = dirname(dirname(__FILE__)) . DS . 'Lib' . DS . 'AssetLib';

if (strpos(ini_get('include_path'), $aa) === false) {
    ini_set('include_path', $aa.DS.ini_get('include_path'));
}
?>