<?php

require_once('lib/common.inc.php');

define('SMS_REPLY_INVALID_PREFIX', "Извините, неверный префикс у СМС-сообщения. Должно быть " . REATESTER_SMS_PREFIX . " вашкод, а вы отправили: %s");
define('SMS_REPLY_OK', 'Ваш пароль: %s');
define('SMS_REPLY_WRONG_SUFFIX', "Неверный суффикс.");

global $smsid;

function reply($text) {
  global $smsid;
  echo "smsid:$smsid\n";
  echo "status:reply\n";
//  echo "content-type: text/plain\n";
  echo "\n";
  echo "$text";
  exit;
}


/************************************************************************************
 Входные данные
************************************************************************************/

$sms = new SMS;
$sms->smsid            = $_REQUEST['smsid'];
$sms->service_phone    = $_REQUEST['num'];
$sms->carrier_id       = $_REQUEST['operator_id'];
$sms->user_phone       = $_REQUEST['user_id'];
$sms->service_earning  = doubleval($_REQUEST['cost']);
$sms->msg              = $_REQUEST['msg'];
$sms->confidence_rate  = $_REQUEST['ran'];
//if (strlen($carrier) == 0)
//	$carrier    = $_REQUEST['operator'];

$sms->suffix = trim($sms->msg);
if(substr($sms->suffix, 0, strlen(REATESTER_SMS_PREFIX)) != REATESTER_SMS_PREFIX){
  $sms->put(sprintf(SMS_REPLY_INVALID_PREFIX, $sms->suffix));
  reply();
}
$sms->suffix = trim(substr($sms->suffix, strlen(REATESTER_SMS_PREFIX)));


/************************************************************************************
 Распознавание суффикса
************************************************************************************/

if(strlen($suffix) != strlen(REATESTER_SMS_CHAL_LENGTH)) {
  $sms->put();
  reply(SMS_REPLY_WRONG_SUFFIX);
}


//$status   = intval($row['status']);
//$id       = intval($row['id']);
//$password = $row['password'];
//$wmid     = $row['wmid'];

//if ($status != WAITING_SMS) {
//  reply(SMS_REPLY_USED_SUFFIX);
//}


/************************************************************************************
 Антифрод: ограничение числа номеров телефонов, с которых можно переводить
 деньги на один кошелек
************************************************************************************/

//$sql = sprintf(
//	"SELECT user_phone FROM passwords WHERE wmid = '%s' AND received_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) GROUP BY user_phone",
//	mysql_real_escape_string($wmid));
//$result = mysql_query($sql);
//if (!$result) {
//	die('MySQL error: ' . mysql_error());
//}
//$phones = array();
//while($row = mysql_fetch_row($result)) {
//    $phones[] = $row[0];
//}
//mysql_free_result($result);
//
//$new_status = WAITING_CODE;
//if (count($phones) >= ANTIFRAUD_MAX_PHONES_PER_WM_PER_HOUR)
//    if (!in_array($user_phone, $phones)) {
//        $new_status = REJECTED_FRAUD;
//    }
//
//if ($ran <= 3 and $new_status == WAITING_CODE) {
//    $new_status = MANUAL_TRANSFER;
//}

/************************************************************************************
 Сохранение информации об СМСке
************************************************************************************/

$sms->put();

/************************************************************************************
 Готово
************************************************************************************/

reply(sprintf(SMS_REPLY_OK, "XXX"));

?>
