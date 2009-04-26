<?
  // вход: $active_tests, $tests
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>Список тестов на re:tester</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <!-- <link rel="stylesheet" href="stylesheets/admin.css" type="text/css" media="screen" charset="utf-8" /> -->
</head>
<body>
  
<h1>Список тестов на re:tester</h1>

<? if($active_tests): ?>
  <? foreach($active_tests as $test): ?>
    <p>
      Вы не закончили проходить тест «<?= $test->name ?>»,
      остановившись на вопросе <?= $test->question_no ?>.
      <a href="<?= $test->url ?>">Продолжить?</a>
    </p>
  <? endforeach; ?>
<? endif; ?>

<p>
  Выберите тест:
</p>
<ul>
  <? foreach($tests as $test): ?>
    <li>
      <a href="<?= $test->url ?>"><?= htmlspecialchars($test->name) ?></a>
    </li>
  <? endforeach; ?>
</ul>

</body>
</html>
