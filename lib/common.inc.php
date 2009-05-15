<?php

define('WEB_ROOT', (strstr($_SERVER['PHP_SELF'], "admin") ? ".." : "."));
define('SITE_ROOT', dirname(__FILE__).'/..');
set_include_path(get_include_path() . PATH_SEPARATOR . SITE_ROOT);

require_once 'config/config.inc.php';
require_once 'lib/dbconnect.inc.php';
require_once 'lib/models.inc.php';
require_once 'lib/HamlParser.class.php';

session_start();
require_once 'lib/render.inc.php';

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

function random_string($l, $c = "ABCDEFGHIJKLMNOPQRSTUVWXYZ", $u = true) { 
 if (!$u) for ($s = '', $i = 0, $z = strlen($c)-1; $i < $l; $x = rand(0,$z), $s .= $c{$x}, $i++); 
 else for ($i = 0, $z = strlen($c)-1, $s = $c{rand(0,$z)}, $i = 1; $i != $l; $x = rand(0,$z), $s .= $c{$x}, $s = ($s{$i} == $s{$i-1} ? substr($s,0,-1) : $s), $i=strlen($s)); 
 return $s; 
}

?>
