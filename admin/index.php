<?php
  include '../lib/common.inc.php';
  $title = "Администрирование re:tester";
  
  $tests = Test::query("SELECT id, name, (SELECT count(*) FROM questions where test_id=tests.id) AS question_count FROM tests ORDER BY name");
  
  render('list.haml', array('tests' => $tests, 'tab' => 'all-tests', 'active_test_id' => $_GET['test_id']));
?>
