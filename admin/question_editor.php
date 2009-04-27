<?php
  include '../lib/common.inc.php';
  
  $id = $_REQUEST['question_id'];
  $is_new = ($id == 'new');
  
  if ($_POST) {
    if ($is_new) {
      $question = new Question();
      $question->test_id = $_REQUEST['test_id'];
      
      $v = get('Model', "SELECT MAX(`order`) AS max_order FROM `questions` WHERE `test_id`=%s", $question->test_id);
      $max_order = ($v ? $v->max_order : 0);
      $question->order = $max_order + 1;
      $answers = array();
    } else {
      if (!($question = get('Question', "WHERE id = %s", $id)))
        jsdie('questionNotFound', $id);
    }
    $question->assign('question_', array('text', 'image_code'));
      
    $answers_data = array();
    foreach($_POST as $k=>$v) {
      if (0 === strpos($k, "ans_")) {
        $arr = explode('_', $k, 3);
        $aid = $arr[1];
        if (!isset($answers_data[$aid]))
          $answers_data[$aid] = array();
        $answers_data[$aid][$arr[2]] = trim($v);
      }
    }
    
    $answers_by_id = query_indexed('Answer', 'id', "WHERE question_id=%d", $question->id);
    $answers = array();
    foreach($answers_data as $aid => $answer_data) {
      $answer = $answers_by_id[intval($aid)];
      if (!$answer)
        $answer = new Answer();
      if (0 !== strpos($aid, "new"))
        $answer->id = $aid;
      foreach($answer_data as $k => $v)
        $answer->$k = $v;
      $answer->points = intval($answer->points);
      $answers[] = $answer;
    }
    
    $question->put();
      
    foreach ($answers as $answer) {
      $answer->question_id = $question->id;
      $answer->test_id = $question->test_id;
      $answer->normalize();
      $answer->put_or_delete();
      if (!$answer->is_empty()) {
        $answer->normalize();
        $answer->put();
      }
    }
    jsdie("questionSaved", $question->id, $is_new);
  }
  
  if ($is_new) {
    $title = "Новый вопрос";
    $question = new Question();
    $question->id = 'new';
    $question->test_id = $_REQUEST['test_id'];
    $answers = array();
  } else {
    if (!($question = get('Question', "WHERE id = %s", $id)))
      redirect("/", "Извините, этот вопрос уже удален.");
    $title = "$question->name";
    $answers = query('Answer', "WHERE question_id = %s ORDER BY `order`", $question->id);
  }
  $max_answer_order = 0;
  foreach ($answers as $answer)
    $max_answer_order = max($max_answer_order, $answer->order);
  $to_add = max(3, 5-count($answers));
  for ($i = 1; $i <= $to_add; $i++) {
    $answer = new Answer();
    $answer->order = $max_answer_order + $i;
    $answer->id = "new".$i;
    $answers[] = $answer;
  }
  
  echo render_partial('question_editor.haml', array('question' => $question, 'answers' => $answers));
?>
