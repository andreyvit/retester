<? require_once 'lib/carriers.inc.php' ?>
<input id="test_id" type="hidden" value="<?=$test->id ?>" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="javascripts/carriers.js"></script>
<script type="text/javascript" src="javascripts/carrier-select.js"></script>
<div id="sms-info">
  <p>
    <select id="countries" name="country" style="width: 200px;">
      <option value="">— Выберите вашу страну —</option>
      <? echo_country_options(); ?>
    </select>
  </p>
  <p>
    <select id="carriers" name="carrier" style="width: 200px;" disabled="disabled">
      <option value="">--выберите вашего оператора--</option>
    </select>
    <span id="only_carrier"></span>
  </p>
  <p>
  	<span id="sms_progress">Загрузка…</span>
  	<span id="sms_error"></span>
  	<span id="sms_details">
	    Отправьте СМС на номер <code id="sms_phone">????</code>
	    с текстом «<code><span id="sms_prefix"><?= REATESTER_SMS_PREFIX ?></span> <?=$RES->sms_chal?></code>».
	    Стоимость СМС <span id="sms_price">??</span>.
    </span>
  </p>
  <div id="sms-response">
	<form id="sms-resp-form" action="/tests/<?=$test->id?>/verify-sms" method="POST">
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
</div>
