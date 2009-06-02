<?php
  include '../lib/common.inc.php';

  $title = "Недавние тесты";
  
  $recent_limit = 100;
  $finished_sessions = query('TestSession', "WHERE `finished_at` IS NOT NULL ORDER BY `started_at` DESC LIMIT $recent_limit");
  $unfinished_sessions = query('TestSession', "WHERE `finished_at` IS NULL ORDER BY `started_at` DESC LIMIT $recent_limit");
  $sms_sessions = query('TestSession', "WHERE `sms_received_at` IS NOT NULL ORDER BY `started_at` DESC LIMIT $recent_limit");
  render('recent.haml', array(
      'unfinished_sessions' => $unfinished_sessions, 'finished_sessions' => $finished_sessions,
      'sms_sessions' => $sms_sessions,
      'tab' => 'recent', 'recent_limit' => $recent_limit));
?>
