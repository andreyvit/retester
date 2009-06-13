<?php
  include '../lib/common.inc.php';
  
  loginkit_require('logged-in');

  $title = "Статистика";
  
  $sum = "SUM(`count_free_starts`) AS `count_free_starts`, SUM(`count_free_finishes`) AS `count_free_finishes`, SUM(`count_starts`) AS `count_starts`, SUM(`count_finishes`) AS `count_finishes`, SUM(`count_smses`) AS `count_smses`, SUM(`partner_earning`) AS `partner_earning`";
  $daily_stats = DailyStatistics::query("SELECT `day`, $sum FROM _T_ WHERE `day` >= DATE_ADD(NOW(), INTERVAL -1 MONTH) AND `partner_id`=? GROUP BY `day` ORDER BY `day`", $current_user->id);
  
  $test_stats = DailyStatistics::query("SELECT `test_id`, $sum FROM _T_ WHERE `day` >= DATE_ADD(NOW(), INTERVAL -1 MONTH) AND `partner_id`=? GROUP BY `test_id`", $current_user->id);
  $tests = Test::query_indexed('id', "SELECT `id`, `name` FROM _T_ WHERE `id` IN ?", collect_attrs($test_stats, 'test_id'));
  foreach($test_stats as &$stat)
    $stat->test_name = $tests[$stat->test_id]->name;
  
  render('statistics.haml', array('test' => $test, 'daily_stats' => $daily_stats, 'test_stats' => $test_stats, 'tab' => 'statistics'));
?>
