<?php
  include '../lib/common.inc.php';
  
  $test = new Test;
  $test->id = $_REQUEST['test_id'];
  $test->delete();
?>
