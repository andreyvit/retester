<?php
require_once 'lib/common.inc.php';
require_once 'lib/carriers.inc.php';

$test_id = intval($_GET['test_id']);
if ($test_id == 0)
  die("invalid test_id");
  
$carrier_id = intval($_GET['carrier_id']);
if ($carrier_id == 0)
	die("invalid carrier_id");
  
$test = get('Test', "WHERE `id` = %d", $test_id);
if (!$test) {
  exit;
}

if (!isset($_SESSION['tests']) || !isset($_SESSION['tests'][$test_id])) {
  exit;
}

$RES =& $_SESSION['tests'][$test_id];
if (!$test->sms_enabled || empty($RES->sms_resp)) {
  // redirect("test.php?test_id=$test->id");
  die();
}

$best_carrier = null;
foreach ($js_carriers as $carrier)
	if (intval($carrier->id) == $carrier_id)
		$best_carrier = $carrier;

if (is_null($best_carrier))
	die("carrier with id $carrier_id not found");

$best_phone = null;
$best_phone_fee = null;
foreach ($best_carrier->phones as $phone)
	if (is_null($best_phone_fee) || doubleval($phone->fee) > $best_phone_fee) {
		$best_phone = $phone;
		$best_phone_fee = doubleval($phone->fee);
	}

js_call_and_exit("sms_allocated", $phone->phone, $phone->fee, $phone->fee_curr, '');

?>
