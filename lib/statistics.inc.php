<?php

function _stat_update_daily($test_id, $partner_id, $day, $set) {
  $sql = "UPDATE `daily_statistics` SET $set WHERE `day`='%s' AND `partner_id`='%s' AND `test_id`='%s'";
  // @execute("INSERT INTO `daily_statistics`(`day`, `partner_id`, `test_id`) VALUES ('%s','%s','%s')", $day, $partner_id, $test_id);
  if (0 == execute($sql, $day, $partner_id, $test_id)) {
    execute("LOCK TABLES `daily_statistics` WRITE");
    execute("INSERT INTO `daily_statistics`(`day`, `partner_id`, `test_id`) VALUES ('%s','%s','%s')", $day, $partner_id, $test_id);
    $affected = execute($sql, $day, $partner_id, $test_id);
    execute("UNLOCK TABLES");
    if ($affected != 1)
      die("internal error: failed to update daily statistics");
  }
}

function stat_test_started($test_id, $partner_id, $day, $next_question_id, $paid) {
  $field = ($paid ? 'count_starts' : 'count_free_starts');
  _stat_update_daily($test_id, $partner_id, $day, "`$field`=`$field`+1");
  
  execute("UPDATE `questions` SET `count_bounces`=`count_bounces`+1 WHERE `id`='%s'", $next_question_id);
  
  execute("INSERT INTO `sessions`(`partner_id`, `test_id`, `bounce_question_id`, `paid`) VALUES ('%s', '%s', '%s', '%s')",
      $partner_id, $test_id, $next_question_id, $paid);
  return mysql_insert_id();
}

function stat_question_answered($session_id, $test_id, $partner_id, $day, $prev_question_id, $answer_id, $next_question_id, $paid) {
  execute("UPDATE `sessions` SET `bounce_question_id`='%s', `answer_count`=`answer_count`+1 WHERE `id`='%s'", $next_question_id, $session_id);
  execute("UPDATE `questions` SET `count_bounces`=`count_bounces`-1, `count_answers`=`count_answers`+1 WHERE `id`='%s'", $prev_question_id);
  execute("UPDATE `questions` SET `count_bounces`=`count_bounces`+1 WHERE `id`='%s'", $next_question_id);
  execute("UPDATE `answers` SET `count_answers`=`count_answers`+1 WHERE `id`='%s'", $answer_id);
}

function stat_test_finished($session_id, $test_id, $partner_id, $day, $prev_question_id, $answer_id, $paid, $sms_chal, $sms_resp) {
  execute("UPDATE `sessions` SET `bounce_question_id`=0, `answer_count`=`answer_count`+1, `finished_at`=NOW(), `sms_chal`='%s', `sms_resp`='%s' WHERE `id`='%s'", $sms_chal, $sms_resp, $session_id);
  execute("UPDATE `questions` SET `count_bounces`=`count_bounces`-1, `count_answers`=`count_answers`+1 WHERE `id`='%s'", $prev_question_id);
  execute("UPDATE `answers` SET `count_answers`=`count_answers`+1 WHERE `id`='%s'", $answer_id);
  
  $field = ($paid ? 'count_finishes' : 'count_free_finishes');
  execute("UPDATE `daily_statistics` SET `$field`=`$field`+1 WHERE `day`='%s' AND `partner_id`='%s' AND `test_id`='%s'", $day, $partner_id, $test_id);
}

function stat_sms_received($session_id, $test_id, $partner_id, $day, $paid) {
  execute("UPDATE `sessions` SET `sms_received_at`=NOW() WHERE `id`='%s'", $session_id);
  execute("UPDATE `daily_statistics` SET `count_smses`=`count_smses`+1 WHERE `day`='%s' AND `partner_id`='%s' AND `test_id`='%s'", $day, $partner_id, $test_id);
}

?>