<?php

define('SITE_ROOT', dirname(__FILE__).'/..');
set_include_path(get_include_path() . PATH_SEPARATOR . SITE_ROOT);

require_once 'config/config.inc.php';
require_once 'lib/render.inc.php';
require_once 'lib/dbconnect.inc.php';
require_once 'lib/HamlParser.class.php';

session_start();
error_reporting(E_ALL & ~E_NOTICE);

?>