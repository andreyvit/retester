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
  <h1><?= $title ?></h1>
  <div style="height: 1em; margin: 0em auto; width: 50%;">
    <? if($flash): ?>
      <p style="background: orange; padding: 5px;"><?= $flash ?></p>
    <? endif; ?>
  </div>
  <? echo $content; ?>
</body>
</html>
