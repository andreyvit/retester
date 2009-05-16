<?php
require_once 'lib/common.inc.php';

$test_id = intval($_GET['test_id']);
if ($test_id == 0)
  die("invalid test_id");
  
if (isset($_SESSION['tests']))
  unset($_SESSION['tests'][$test_id]);

$url = "test.php?test_id=$test_id";  
if (isset($_REQUEST['partner_id']))
  $url .= "&partner_id=".$_REQUEST['partner_id'];
  
redirect($url);

?>
