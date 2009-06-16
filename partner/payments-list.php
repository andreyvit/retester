<?php
define('REATESTER_PAYMENTS_HISTORY_MONTHS', 3);

include '../lib/common.inc.php';

loginkit_require('logged-in');

$partner = loginkit_current_user();

$payments = Payment::query("WHERE partner_id=? AND transferred_at >= DATE_SUB(NOW(), INTERVAL ".REATESTER_PAYMENTS_HISTORY_MONTHS." MONTH) ORDER BY transferred_at DESC", $partner->id);

$title = "История выплат";
render('payments-list.haml', array('partner' => $partner, 'payments' => $payments, 'tab' => 'payments'));

?>
