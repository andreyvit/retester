<?php
  $points = 0;
  foreach($RES->answers as $answer) {
    $points += $answer->points;
  }
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<? if($full): ?>
  <p>Готово, у вас <?=$points?> баллов. <a href="<?= $again_url ?>">Пройти тест заново</a></p>
<? else: ?>
  <p>У вас <?if($points % 2 == 0):?>четное<?else:?>нечетное<?endif;?>, но очень интересное количество баллов. Чтобы узнать подробнее, нужно отправить нам СМС.</p>
  <?= $sms_info ?>
<? endif; ?>
