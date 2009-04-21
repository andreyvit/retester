<?php
  require_once '../lib/dbconnect.inc.php';
  require_once '../lib/HamlParser.class.php';
  session_start();
  error_reporting(E_ALL & ~E_NOTICE);
  
  function isAjax() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
  }
  
  // {beforeSend: function(xhr) {
  //   xhr.setRequestHeader("Accept", "text/javascript");
  // }}
  function wantsScript() {
    return in_array("text/javascript", explode(',', $_SERVER['HTTP_ACCEPT']));
  }
  
  function render_partial($template, $data) {
    $haml = new HamlParser('./templates', '../tmp/haml');
    $haml->append($data);
    return $haml->setFile($template);
  }
  
  function render($template, $data) {
    global $title;
    $flash = $_SESSION['flash'];
    $_SESSION['flash'] = '';
    $content = render_partial($template, $data);
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

  function error_redirect($extra, $flash = "") {
    if (isAjax()) {
      if (wantsScript())
        die("alert(\"" . addslashes($flash) . "\");");
      else
        die("<script>alert(\"" . addslashes($flash) . "\");</script>");
    } else {
      redirect($extra, $flash);
    }
  }
  
  function jsdie($name /*, $arg... */) {
    $args = func_get_args();
    array_shift($args);
    foreach ($args as &$arg)
      $arg = '"' . addslashes("$arg") . '"';
    die($name . '(' . implode(', ', $args) . ')');
  }
  
?>
