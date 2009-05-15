<?php
  include '../lib/common.inc.php';
  
  $id = $_GET['test_id'];
  if (!($test = get('Test', "WHERE id = %s", $id)))
    redirect("/", "Извините, этот тест уже удален.");
    
  function list_files_recursively($from, $pretty = '') {
      if(!is_dir($from))
          return false;
      $files = array();
      if( $dh = opendir($from)) {
          while( false !== ($file = readdir($dh))) {
              if( $file == '.' || $file == '..')
                  continue;
              $path = $from . '/' . $file;
              $subpretty = empty($pretty) ? $file : "$pretty/$file";
              if( is_dir($path) )
                  $files += list_files_recursively($path, $subpretty);
              elseif(preg_match('/\\.php$/i', $file))
                  $files[] = $subpretty;
          }
          closedir($dh);
      }
      return $files;
  }

  if ($_POST) {
    $test->assign('', array('name', 'design_file', 'finisher_file', 'handler_file', 'sms_enabled'));
    if ($test->is_valid()) {
      $test->put();
      redirect("test-settings.php?test_id=$test->id", "+Изменения сохранены.");
    }
  }
  
  $handler_files = list_files_recursively('../data/handlers');
  $finisher_files = list_files_recursively('../data/finishers');
  $design_files = list_files_recursively('../data/designs');
    
  $title = "$test->name";
  render('test-settings.haml', array('test' => $test, 'tab' => 'test-settings',
    'handler_files' => $handler_files, 'finisher_files'=>$finisher_files, 'design_files'=>$design_files));
?>
