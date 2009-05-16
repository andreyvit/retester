<?php
$GLOBALS['tab'] = $tab;
function tab($name) {
  return ($name == $GLOBALS['tab'] ? ' active ' : '');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title><?= $title ?></title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js"></script>
  <script src="../javascripts/jquery.livequery.js"></script>
  <link rel="stylesheet" href="../stylesheets/admin.css" type="text/css" media="screen" charset="utf-8" />
</head>
<body>
  <div id="container">
    <div id="header">
      <h1><a href="#"><?= $title ?></a></h1>
      <div id="user-navigation">
        <ul>
          <li><a href="login.php">Вход</a></li>
          <li><a href="signup.php">Регистрация</a></li>
        </ul>
        <div class="clear"></div>
      </div>      
    </div>    
    <? echo $content; ?>
  </div>
</body>
</html>
