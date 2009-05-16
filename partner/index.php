<?php
  include '../lib/common.inc.php';
  $title = "Партнерам re:tester";
  
  render('index.haml', array('tab' => 'all-tests', 'active_test_id' => $_GET['test_id']));
?>
