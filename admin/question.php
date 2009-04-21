<?php
  include 'lib/prefix.inc.php';
  
  $id = $_REQUEST['question_id'];
  
  if (!($question = get('Question', "SELECT id, `order`, `text` FROM questions WHERE id = %s", $id)))
    error_redirect("/", "Извините, этот вопрос уже удален.");

  echo render_partial('question.haml', array('question' => $question));
?>
