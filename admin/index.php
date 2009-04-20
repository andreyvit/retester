<?php
  include 'lib/prefix.inc.php';
  $title = "Администрирование re:tester";
  
  $tests = query("SELECT id, name FROM tests ORDER BY created_at DESC");
  render('list.haml', array('tests' => $tests));
?>
