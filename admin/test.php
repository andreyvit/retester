<?php
  include 'lib/prefix.inc.php';
  
  $id = $_GET['id'];
  $is_new = ($id == 'new');
  if ($is_new) {
    $title = "Новый тест";
    $test = new stdClass();
  } else {
    if (!($test = get("SELECT id, name FROM tests WHERE id = %s", $id)))
      redirect("/", "Извините, такого теста не существует.");
    $title = "$test->name";
  }
  
  render('test.haml', array('test' => $test));
?>
