<?php
  include 'lib/prefix.inc.php';
  
  $id = $_GET['id'];
  $is_new = ($id == 'new');
  if ($is_new) {
    $title = "Новый тест";
    $test = new Test();
    $questions = array();
  } else {
    if (!($test = get('Test', "SELECT id, name FROM tests WHERE id = %s", $id)))
      redirect("/", "Извините, этот тест уже удален.");
    $title = "$test->name";
    $questions = query('Question', "SELECT id, `order`, `text` FROM questions WHERE test_id = %s ORDER BY `order`", $test->id);
  }
  
  render('test.haml', array('test' => $test, 'questions' => $questions));
?>
