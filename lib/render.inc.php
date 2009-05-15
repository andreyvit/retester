<?php

$FLASH = $_SESSION['flash'];
$_SESSION['flash'] = '';

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
  $haml = new HamlParser('./templates', SITE_ROOT.'/tmp/haml');
  $haml->append($data);
  return $haml->setFile($template);
}

function render($template, $data) {
  global $title, $FLASH;
  $flash = $FLASH;
  $content = render_partial($template, $data);
  foreach($data as $k => $v)
    $$k = $v;
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
