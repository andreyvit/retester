<?php
  include '../lib/common.inc.php';

  if ($_GET['test_id'] == 'new') {
    $title = "Новый тест";
    $test = new Test();
    $questions = array();
  } else {
    $test = Model::get_from_request('Test', 'index.php', "Извините, этот тест уже удален.");
    $title = "$test->name";
    $questions = query('Question', "SELECT **, (SELECT count(*) FROM answers WHERE question_id=questions.id) AS answer_count FROM _T_ WHERE test_id = %s ORDER BY `order`", $test->id);
  }
  
  render('test.haml', array('test' => $test, 'questions' => $questions, 'tab' => 'questions'));
?>
