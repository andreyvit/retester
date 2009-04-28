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
          <li><a href="logout.php">Выход</a></li>
        </ul>
        <div class="clear"></div>
      </div>      
      <div id="main-navigation">
        <ul>
          <li class="<?=tab('all-tests')?> first"><a href="index.php?test_id=<?=$_REQUEST['test_id']?>">Выбор теста</a></li>
          <li class="<?=tab('files')?>"><a href="files.php?test_id=<?=$_REQUEST['test_id']?>">Файлы</a></li>
          <? if($_REQUEST['test_id']): ?>
          <li class="<?=tab('test-settings')?>"><a href="test-settings.php?test_id=<?=$_REQUEST['test_id']?>">Настройки теста</a></li>
          <li class="<?=tab('questions')?>"><a href="test.php?test_id=<?=$_REQUEST['test_id']?>">Вопросы</a></li>
          <? endif; ?>
        </ul>
        <div class="clear"></div>
      </div>
    </div>    
    <div id="wrapper">
      <div id="main">
        <? if($flash): ?>
        <div class="block">
          <div class="content">            
            <div class="flash">
              <? if($flash{0} == '!'): ?>
                <div class="message error">
                  <p><?= substr($flash, 1) ?></p>
                </div>                     
              <? elseif($flash{0} == "+"): ?>         
                <div class="message notice">
                  <p><?= substr($flash, 1) ?></p>
                </div>                
              <? else: ?>
                <div class="message warning">
                  <p><?= $flash ?></p>
                </div>          
              <? endif; ?>      
            </div>
          </div>
        </div>
        <? endif; ?>
        <? echo $content; ?>
      </div>
    </div>
  </div>
        
</body>
</html>
