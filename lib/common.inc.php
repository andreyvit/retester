<?php

define('SITE_ROOT', dirname(__FILE__).'/..');
set_include_path(get_include_path() . PATH_SEPARATOR . SITE_ROOT);

require_once 'config/config.inc.php';
require_once 'lib/render.inc.php';
require_once 'lib/dbconnect.inc.php';
require_once 'lib/models.inc.php';
require_once 'lib/HamlParser.class.php';

session_start();
error_reporting(E_ALL & ~E_NOTICE);

function index_by($array, $attr) {
  $result = array();
  foreach ($array as $obj)
    $result[$obj->$attr] = $obj;
  return $result;
}

function collect_attrs($array, $attr) {
  $result = array();
  foreach ($array as $obj)
    $result[] = $obj->$attr;
  return $result;
}

function extension($file_name) {
  return pathinfo($file_name, PATHINFO_EXTENSION);
}

?>
