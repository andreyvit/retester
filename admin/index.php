<?php
  include '../lib/common.inc.php';
  $title = "Администрирование re:tester";
  
  $tests = query('Test', "SELECT id, name FROM tests ORDER BY name");

  header('Cache-control: private, no-cache, must-revalidate');
  header('Expires: 0');
  
  ob_start();
  include_once('filemanager/class/FileManager.php');
  $FileManager = new FileManager("../data/");
  $FileManager->fmWebPath = "./filemanager/";
  $FileManager->enablePermissions = false;
  $FileManager->createBackups = false;
  $FileManager->create();
  $file_manager = ob_get_clean();
  
  render('list.haml', array('tests' => $tests, 'file_manager' => $file_manager));
?>
