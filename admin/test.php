<?php
  include '../lib/common.inc.php';

  if ($_GET['test_id'] == 'new') {
    $title = "Новый тест";
    $test = new Test();
    $questions = array();
  } else {
    $test = Test::get_from_request('/admin/', "Извините, этот тест уже удален.");
    $title = "$test->name";
    $questions = Question::query("SELECT **, (SELECT count(*) FROM answers WHERE question_id=questions.id) AS answer_count FROM _T_ WHERE test_id = %s ORDER BY `order`", $test->id);
  }
  
  render('test.haml', array('test' => $test, 'questions' => $questions, 'tab' => 'questions'));
?>
