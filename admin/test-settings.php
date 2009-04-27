<?php
  include '../lib/common.inc.php';
  
  $id = $_GET['test_id'];
  if (!($test = get('Test', "SELECT id, name FROM tests WHERE id = %s", $id)))
    redirect("/", "Извините, этот тест уже удален.");

  if ($_POST) {
    $test->assign('', array('name', 'design_file'));
    if ($test->is_valid()) {
      $test->put();
      redirect("test-settings.php?test_id=$test->id", "+Изменения сохранены.");
    }
  }
    
  $title = "$test->name";
  render('test-settings.haml', array('test' => $test, 'tab' => 'test-settings'));
?>
