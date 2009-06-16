<?php

define('WEB_ROOT', '/'); //(strstr($_SERVER['PHP_SELF'], "admin") ? ".." : "."));
define('SITE_ROOT', dirname(__FILE__).'/..');
if (strstr($_SERVER['PHP_SELF'], "partner"))
  define('LOGINKIT_LOGIN_URL', '/partner/accounts/login/');
else
  define('LOGINKIT_LOGIN_URL', '/login/');
set_include_path(get_include_path() . PATH_SEPARATOR . SITE_ROOT);

require_once 'config/config.inc.php';
require_once 'lib/dbkit.inc.php';
require_once 'lib/models.inc.php';
require_once 'lib/HamlParser.class.php';
require_once 'lib/statistics.inc.php';

session_start();
require_once 'lib/render.inc.php';
require_once 'lib/loginkit.inc.php';
require_once 'lib/security.inc.php';

error_reporting(E_ALL & ~E_NOTICE);

require_once 'lib/utilities.inc.php';

setlocale(LC_ALL, 'ru_RU');

?>
