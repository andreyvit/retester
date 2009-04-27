<?php
  include '../lib/common.inc.php';
  
  $id = $_GET['test_id'];
  $is_new = ($id == 'new');
  if ($is_new) {
    $title = "Новый тест";
    $test = new Test();
    $questions = array();
  } else {
    if (!($test = get('Test', "WHERE id = %s", $id)))
      redirect("/", "Извините, этот тест уже удален.");
    $title = "$test->name";
    $questions = query('Question', "SELECT **, (SELECT count(*) FROM answers WHERE question_id=questions.id) AS answer_count FROM _T_ WHERE test_id = %s ORDER BY `order`", $test->id);
  }
  
  render('test.haml', array('test' => $test, 'questions' => $questions, 'tab' => 'questions'));
?>
