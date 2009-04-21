<?php
  include 'lib/prefix.inc.php';
  
  $id = $_REQUEST['question_id'];
  $is_new = ($id == 'new');
  
  if ($_POST) {
    $editor_id = $_REQUEST['editor_id'];
    if ($is_new) {
      $question = new Question();
      $question->test_id = $_REQUEST['test_id'];
      
      $v = get('Model', "SELECT MAX(`order`) AS max_order FROM `questions` WHERE `test_id`=%s", $question->test_id);
      $max_order = ($v ? $v->max_order : 0);
      $question->order = $max_order + 1;
      $answers = array();
    } else {
      if (!($question = get('Question', "SELECT id, `order`, `text` FROM questions WHERE id = %s", $id)))
        jsdie('questionNotFound', $editor_id, $id);
    }
    $question->text = trim($_REQUEST['question_text']);
      
    $answers_data = array();
    foreach($_POST as $k=>$v) {
      if (0 === strpos($k, "ans_")) {
        $arr = explode('_', $k);
        $aid = $arr[1];
        if (!isset($answers_data[$aid]))
          $answers_data[$aid] = array();
        $answers_data[$aid][$arr[2]] = trim($v);
      }
    }
    
    $answers = array();
    foreach($answers_data as $aid => $answer_data) {
      $answer = new Answer();
      if (0 !== strpos($aid, "new"))
        $answer->id = $aid;
      foreach($answer_data as $k => $v)
        $answer->$k = $v;
      $answer->is_correct = (isset($answer->correct) && $answer->correct ? 1 : 0);
      $answers[] = $answer;
    }
    // var_dump($answers);
    
    // validate

    $question->put();
      
    foreach ($answers as $answer) {
      $answer->question_id = $question->id;
      $answer->put_or_delete();
    }
    jsdie("questionSaved", $editor_id, $question->id, $is_new);
  }
  
  if ($is_new) {
    $title = "Новый вопрос";
    $question = new Question();
    $question->id = 'new';
    $question->test_id = $_REQUEST['test_id'];
    $answers = array();
  } else {
    if (!($question = get('Question', "SELECT id, `order`, `text` FROM questions WHERE id = %s", $id)))
      redirect("/", "Извините, этот вопрос уже удален.");
    $title = "$question->name";
    $answers = query('Answer', "SELECT id, `order`, `text`, `is_correct` FROM answers WHERE question_id = %s ORDER BY `order`", $question->id);
  }
  $max_answer_order = 0;
  foreach ($answers as $answer)
    $max_answer_order = max($max_answer_order, $answer->order);
  for ($i = 1; $i <= 3; $i++) {
    $answer = new Answer();
    $answer->order = $max_answer_order + $i;
    $answer->id = "new".$i;
    $answers[] = $answer;
  }
  
  echo render_partial('question_editor.haml', array('question' => $question, 'answers' => $answers));
?>
