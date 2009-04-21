<?php
  include 'lib/prefix.inc.php';
  
  $test_id = $_REQUEST['test_id'];
  if(!get('Test', "SELECT id FROM tests WHERE id='%s'", $test_id))
    error_redirect('', 'Такого теста больше нет');
  foreach($_REQUEST['question_order'] as $qid => $order) {
    execute("UPDATE questions SET `order`='%s' WHERE test_id='%s' AND id='%s'", $order, $test_id, $qid);
  }
  
?>