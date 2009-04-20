<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title><?= $title ?></title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
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
