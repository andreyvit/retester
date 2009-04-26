<?php
  $points = 0;
  foreach($RES->answers as $answer) {
    $points += $answer->points;
  }
?>
<p>Готово, у вас <?=$points?> баллов. <a href="<?= $again_url ?>">Пройти тест заново</a></p>
