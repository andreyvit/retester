<?php
require_once 'lib/common.inc.php';

$test_id = intval($_GET['test_id']);
if ($test_id == 0)
  die("invalid test_id");
  
$test = get('Test', "WHERE `id` = %d", $test_id);
if (!$test) {
  include('templates/test_no_longer_exists.inc.php');
  exit;
}

if (!isset($_SESSION['tests']) || !isset($_SESSION['tests'][$test_id])) {
  include('templates/test_expired.inc.php');
  exit;
}

$RES =& $_SESSION['tests'][$test_id];
if (!$test->sms_enabled || empty($RES->sms_resp)) {
  redirect("test.php?test_id=$test->id");
  die();
}

$resp = $_REQUEST['resp'];
if (strtoupper($resp) != strtoupper($RES->sms_resp)) {
  redirect("test.php?test_id=$test->id", "Неверный код, попробуйте еще раз");
  die();
}

$RES->sms_received = true;
redirect("test.php?test_id=$test->id");
