<?php
  include 'lib/prefix.inc.php';
  
  $test = new Test;
  $test->id = $_REQUEST['test_id'];
  $test->delete();
?>
