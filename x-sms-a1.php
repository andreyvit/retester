<?php

require_once('lib/common.inc.php');

global $smsid;

function reply($text) {
  global $smsid;
  echo "smsid:$smsid\n";
  echo "status:reply\n";
  // echo "content-type: text/plain\n";
  echo "\n";
  echo "$text";
  exit;
}


/************************************************************************************
 Входные данные
************************************************************************************/

$smsid          = $_REQUEST['smsid'];
$service_phone  = $_REQUEST['num'];
$carrier        = $_REQUEST['operator_id'];
if (strlen($carrier) == 0) {
	$carrier    = $_REQUEST['operator'];
}
$user_phone     = $_REQUEST['user_id'];
$cost           = doubleval($_REQUEST['cost']);
$msg            = $_REQUEST['msg'];
$ran            = $_REQUEST['ran'];

$suffix = trim($msg);
if(!substr($suffix, 0, 6) == 'gelios'){
    die('Plohoy suffix.');
}
$suffix = trim(substr($suffix, 6));

/************************************************************************************
 Распознавание суффикса
************************************************************************************/

if(strlen($suffix) != 4) {
    if(strlen($suffix) != 13){
        reply(SMS_REPLY_WRONG_SUFFIX);
    }
    $codes = array('1' => 'Z', '2' => 'R', '3' => 'E', '4' => 'U');
    $first = @$codes[$suffix[0]];
    if (!$first){
        reply(SMS_REPLY_WRONG_SUFFIX);
    }
    $wmid = $first . substr($suffix, 1);
    $sql = sprintf("INSERT INTO passwords (password, status, allocated_at, received_at, suffix, wmid, smsid, service_phone, carrier, user_phone, cost, msg) VALUES('%s', %s, now(), now(), '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
    	"LONG_TERM",
    	MANUAL_TRANSFER,
        '',
    	mysql_real_escape_string($wmid),
    	mysql_real_escape_string($smsid),
    	mysql_real_escape_string($service_phone),
    	mysql_real_escape_string($carrier),
        mysql_real_escape_string($user_phone),
    	mysql_real_escape_string("$cost"),
        mysql_real_escape_string($msg));
    $result = mysql_query($sql);

    if (!$result) {
    	die('MySQL error: ' . mysql_error());
    }
    reply(SMS_REPLY_LONGTERM_MODE);
}

$sql = sprintf(
	"SELECT id, status, password, wmid FROM passwords WHERE suffix = '%s' ORDER BY allocated_at DESC LIMIT 1;",
    mysql_real_escape_string($suffix));

$result = mysql_query($sql);
if (!$result) {
	die('MySQL error: ' . mysql_error());
}

if(!($row = mysql_fetch_assoc($result))) {
    reply(SMS_REPLY_WRONG_SUFFIX);
}

$status   = intval($row['status']);
$id       = intval($row['id']);
$password = $row['password'];
$wmid     = $row['wmid'];


if ($status != WAITING_SMS) {
  reply(SMS_REPLY_USED_SUFFIX);
}


/************************************************************************************
 Антифрод: ограничение числа номеров телефонов, с которых можно переводить
 деньги на один кошелек
************************************************************************************/

$sql = sprintf(
	"SELECT user_phone FROM passwords WHERE wmid = '%s' AND received_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) GROUP BY user_phone",
	mysql_real_escape_string($wmid));
$result = mysql_query($sql);
if (!$result) {
	die('MySQL error: ' . mysql_error());
}
$phones = array();
while($row = mysql_fetch_row($result)) {
    $phones[] = $row[0];
}
mysql_free_result($result);

$new_status = WAITING_CODE;
if (count($phones) >= ANTIFRAUD_MAX_PHONES_PER_WM_PER_HOUR)
    if (!in_array($user_phone, $phones)) {
        $new_status = REJECTED_FRAUD;
    }

if ($ran <= 3 and $new_status == WAITING_CODE) {
    $new_status = MANUAL_TRANSFER;
}

/************************************************************************************
 Сохранение информации об СМСке
************************************************************************************/

$sql = sprintf(
	"UPDATE passwords SET status = %s, received_at = now(), smsid = '%s', service_phone = '%s', carrier = '%s', user_phone = '%s', cost = '%s', msg = '%s' WHERE id = $id",
	$new_status,
	mysql_real_escape_string($smsid),
	mysql_real_escape_string($service_phone),
	mysql_real_escape_string($carrier),
    mysql_real_escape_string($user_phone),
	mysql_real_escape_string("$cost"),
    mysql_real_escape_string($msg));
$result = mysql_query($sql);

if (!$result) {
	die('MySQL error: ' . mysql_error());
}

/************************************************************************************
 Готово
************************************************************************************/

switch ($new_status) {
    case WAITING_CODE:
        reply(sprintf(SMS_REPLY_OK, $password));
        break;
    case REJECTED_FRAUD:
        reply(SMS_REPLY_FRAUD);
        break;
    case MANUAL_TRANSFER:
        reply(SMS_REPLY_MANUAL_TRANSFER);
        break;
}

?>
