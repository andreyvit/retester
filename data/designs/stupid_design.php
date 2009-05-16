<?
  /* вход:
    $test->id
    $test->name             название теста
    $question->text         текст вопроса
    $answers[i]->text       текст варианта ответа
    $RES->question_no       на который по счету вопрос отвечает пользователь (первый всегда 1)
  */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title><?= htmlspecialchars($test->name) ?></title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <!-- <link rel="stylesheet" href="stylesheets/admin.css" type="text/css" media="screen" charset="utf-8" /> -->
</head>
<body>
  
<h1><?= htmlspecialchars($test->name) ?></h1>

<form action="<?=$submit_url?>" method="POST">
  <p><b>Вопрос <?= $RES->question_no ?></b></p>
  <p>
    <?= htmlspecialchars($question->text) ?>
  </p>
  <? if($question->image_file): ?>
  <p><img src="<?= htmlspecialchars($question->image_path()) ?>"></p>
  <? endif; ?>
  
  <div>
    <? foreach($answers as $answer): ?>
      <p>
        <input type="radio" id="answer_<?= $answer->id ?>" name="answer" value="<?= $answer->id ?>" />
        <label for="answer_<?= $answer->id ?>">
          <?= htmlspecialchars($answer->text) ?>
          <? if($answer->image_file): ?>
          <p><img src="<?= htmlspecialchars($answer->image_path()) ?>"></p>
          <? endif; ?>
        </label>
      </p>
    <? endforeach; ?>
  </div>
  
  <p><input type="submit" value="Ответить"></p>
</form>

<p>Partner ID: <?= $RES->partner_id ?></p>

</body>
</html>
