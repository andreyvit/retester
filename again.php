<?php
require_once 'lib/common.inc.php';

session_destroy();

$test_id = intval($_GET['test_id']);
if ($test_id == 0)
  die("invalid test_id");
  
if (isset($_SESSION['tests']))
  unset($_SESSION['tests'][$test_id]);
  
redirect("test.php?test_id=$test_id");

?>
