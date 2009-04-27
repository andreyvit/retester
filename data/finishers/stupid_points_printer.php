<?php
  $points = 0;
  foreach($RES->answers as $answer) {
    $points += $answer->points;
  }
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<p>Готово, у вас <?=$points?> баллов. <a href="<?= $again_url ?>">Пройти тест заново</a></p>
