<?php
  include '../lib/common.inc.php';
  
  $test_id = $_REQUEST['test_id'];
  if(!Test::get("SELECT id FROM tests WHERE id=?", $test_id))
    error_redirect('', 'Такого теста больше нет');
  foreach($_REQUEST['question_order'] as $qid => $order) {
    dbkit_execute("UPDATE questions SET `order`=? WHERE test_id=? AND id=?", $order, $test_id, $qid);
  }
  
?>