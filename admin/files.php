<?php
  include '../lib/common.inc.php';
  $title = "Администрирование re:tester";

  header('Cache-control: private, no-cache, must-revalidate');
  header('Expires: 0');
  
  function create_file_manager($dir) {
    ob_start();
    include_once('filemanager/class/FileManager.php');
    $FileManager = new FileManager("../data/$dir");
    $FileManager->fmWebPath = "/admin/filemanager/";
    $FileManager->enablePermissions = false;
    $FileManager->createBackups = false;
    $FileManager->logHeight = 0;
    $FileManager->create();
    return ob_get_clean();
  }
  
  render('files.haml', array('tab' => 'files',
    'designs_fm' => create_file_manager('designs'),
    'handlers_fm' => create_file_manager('handlers'),
    'finishers_fm' => create_file_manager('finishers')));
?>
