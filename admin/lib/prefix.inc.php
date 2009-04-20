<?php
  require_once '../lib/dbconnect.inc.php';
  require_once '../lib/HamlParser.class.php';
  session_start();
  
  function render($template, $data) {
    global $title;
    $flash = $_SESSION['flash'];
    $_SESSION['flash'] = '';
    
    $haml = new HamlParser('./templates', '../tmp/haml');
    $haml->append($data);
    $content = $haml->setFile($template);
    include 'templates/layout.inc.php';
  }
  
  function redirect($extra, $flash = "") {
    $host  = $_SERVER['HTTP_HOST'];
    if ($extra == '/') $extra = '';
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $_SESSION['flash'] = $flash;
    header("Location: http://$host$uri/$extra");
    die();
  }
  
?>
