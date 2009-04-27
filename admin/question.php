<?php
  include '../lib/common.inc.php';
  
  $id = $_REQUEST['question_id'];
  
  if (!($question = get('Question', "SELECT **, (SELECT count(*) FROM answers WHERE question_id=questions.id) AS answer_count FROM _T_ WHERE id = %s", $id)))
    error_redirect("/", "Извините, этот вопрос уже удален.");

  echo render_partial('question.haml', array('question' => $question));
?>
