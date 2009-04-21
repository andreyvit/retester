<?php
  include 'lib/prefix.inc.php';
  
  $test = new Test();
  $test->name = trim($_REQUEST['name']);
    
  // validate

  $test->put();
  jsdie('testCreated', $test->id);
?>
