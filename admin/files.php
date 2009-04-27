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
  $FileManager->logHeight = 0;
  $FileManager->create();
  $file_manager = ob_get_clean();
  
  render('files.haml', array('file_manager' => $file_manager, 'tab' => 'files'));
?>
