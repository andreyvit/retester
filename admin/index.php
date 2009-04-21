<?php
  include '../lib/common.inc.php';
  $title = "Администрирование re:tester";
  
  $tests = query('Test', "SELECT id, name FROM tests ORDER BY name");
  render('list.haml', array('tests' => $tests));
?>
