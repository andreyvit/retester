<?php
  include '../lib/common.inc.php';
  
  $test = new Test();
  $test->name = trim($_REQUEST['name']);
    
  // validate

  $test->put();
  jsdie('testCreated', $test->id);
?>
