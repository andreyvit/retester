<div id="sms-info">
  <p><select><option>— Выберите вашу страну —<option>Россия</option><option>Украина</option></select></p>
  <p><select><option>— Выберите вашего сотового оператора —</option><option>Билайн</option><option>МТС</option></select></p>
  <p>
    Отправьте СМС на номер <code>1111</code> с текстом «<code>Q7B <?=$RES->sms_chal?></code>».
    Стоимость СМС 10 рублей.
  </p>
  <form id="sms-resp-form" action="sms-resp.php?test_id=<?=$test->id?>" method="POST">
    <? if($FLASH): ?>
      <p style="color:red"><?= htmlspecialchars($FLASH) ?></p>
    <? else: ?>
      <p>Введите код, полученный в ответном СМС-сообщении (подсказка: <?=$RES->sms_resp?>):</p>
    <? endif; ?>
    <p>
      <input id="sms-resp" name="resp" size="<?=REATESTER_SMS_RESP_LENGTH+2?>" maxlength="<?=REATESTER_SMS_RESP_LENGTH?>" style="font-size: 2em;" />
      <input id="sms-resp-submit" type="submit" value="Показать результаты теста">
    </p>
    
  </form>
</div>

