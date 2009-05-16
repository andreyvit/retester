<?php
  include '../lib/common.inc.php';

  // $test = Model::get_from_request('Test', 'index.php', "Извините, этот тест уже удален.");
  $title = "Статистика";
  
  $sum = "SUM(`count_free_starts`) AS `count_free_starts`, SUM(`count_free_finishes`) AS `count_free_finishes`, SUM(`count_starts`) AS `count_starts`, SUM(`count_finishes`) AS `count_finishes`, SUM(`count_smses`) AS `count_smses`";
  $daily_stats = query('DailyStatistics', "SELECT `day`, $sum FROM _T_ WHERE `day` >= DATE_ADD(NOW(), INTERVAL -1 MONTH) GROUP BY `day` ORDER BY `day`");
  
  $partner_stats = query('DailyStatistics', "SELECT `partner_id`, $sum FROM _T_ WHERE `day` >= DATE_ADD(NOW(), INTERVAL -1 MONTH) GROUP BY `partner_id`");
  $partners = query_indexed('Partner', 'id', "SELECT `id`, `email` FROM _T_ WHERE `id` IN %s", collect_attrs($partner_stats, 'partner_id'));
  foreach($partner_stats as &$stat)
    if (intval($stat->partner_id) == 0)
      $stat->partner_email = "(нет)";
    else
      $stat->partner_email = $partners[$stat->partner_id]->email;
  
  $test_stats = query('DailyStatistics', "SELECT `test_id`, $sum FROM _T_ WHERE `day` >= DATE_ADD(NOW(), INTERVAL -1 MONTH) GROUP BY `test_id`");
  $tests = query_indexed('Test', 'id', "SELECT `id`, `name` FROM _T_ WHERE `id` IN %s", collect_attrs($test_stats, 'test_id'));
  foreach($test_stats as &$stat)
    $stat->test_name = $tests[$stat->test_id]->name;
  
  render('statistics.haml', array('test' => $test, 'daily_stats' => $daily_stats, 'partner_stats' => $partner_stats, 'test_stats' => $test_stats, 'tab' => 'statistics'));
?>
