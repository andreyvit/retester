<?php
if (isset($_REQUEST['rewritecheck'])) die("OK");
ob_start();
function render_layout() {
  $content = ob_get_clean();
  ?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
  <html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Установка re:tester</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.js"></script>
    <style>
      * {padding: 0; margin: 0;}
      body { font-size: 12px; font-family: sans-serif; }
      h1 { margin: 15px 0; font-size: 22px; font-weight: normal; }
      h2 { font-size: 22px; margin: 15px 0; font-weight: normal;}
      h3 { font-size: 18px; margin: 10px 0; font-weight: normal;}
      h4 { font-size: 16px; margin: 10px 0; font-weight: normal;}
      hr {height: 1px; border: 0; }
      p { margin: 15px 0;}
      a img { border: none; }
      #container { width: 600px; margin-top: 100px; margin-left: auto; margin-right: auto; border: 1px solid black; padding: 10px 20px; }
      #footer { margin-top: 10px; padding-top: 10px; font-size: 10px; }
    </style>
  </head>
  <body>
    <div id="container">
      <?= $content ?>
      <p id="footer">© 2009, re:action. Разработка компании YourSway. Поддержка по адресу <a href="mailto:andreyvit@gmail.com">andreyvit@gmail.com</a>.</p>
    </div>
  </body>
  </html>
  
  <?php
}
register_shutdown_function('render_layout');

$PROPAGATE = array();
function propagate_query_string() {
  global $PROPAGATE;
  $qs = "";
  foreach($PROPAGATE as $k=>$v) {
    if (strlen($qs)) $qs .= '&';
    $qs .= urlencode($k) . '=' . urlencode($v);
  }
  return $qs;
}
function redirect($extra = '') {
  $host  = $_SERVER['HTTP_HOST'];
  $uri   = $_SERVER['PHP_SELF'];
  header("Location: http://$host$uri?$extra".propagate_query_string());
  die();
}

define('REQUIRED_PHP_VERSION', '5.2.0');
define('REATESTER_PASSWORD_SALT', 're:tester-setup-password');
function version_to_int($v) { $v = explode('.', $v); return 10000*$v[0] + 100*$v[1] + $v[2]; }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!file_exists('config/config.inc.php'))
  die("<h1>Требуется скопировать файл конфигурации</h1>".
    "<p>Файл конфигурации <code>config/config.inc.php</code> не найден. ".
    "Вероятно, дело в том, что вы только что скачали данный скрипт из Git'а.</p>".
    "<p>Пожалуйста, скопируйте файл-пример <code>config/config.inc.php.example</code> в <code>config/config.inc.php</code>.</p>");

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

include('config/config.inc.php');

if (!defined('REATESTER_SETUP_PASSWORD') || REATESTER_SETUP_PASSWORD == '')
  die("<h1>Требуется задать пароль для установки</h1>".
    "<p>Чтобы доказать, что вы имеете доступ на запись к файлам скрипта, установите пароль в файле конфигурации.</p>".
    "<p>Откройте <code>config/config.inc.php</code> в редакторе и задайте пароль константой <code>REATESTER_SETUP_PASSWORD</code>.");

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_REQUEST['password'])) {
  $PROPAGATE['password_hash'] = md5(REATESTER_PASSWORD_SALT.$_REQUEST['password']);
  redirect();
}
if (!isset($_REQUEST['password_hash']) || $_REQUEST['password_hash'] != md5(REATESTER_PASSWORD_SALT.REATESTER_SETUP_PASSWORD)) {
  if (isset($_REQUEST['password_hash']))
    echo "<h1>Неверный пароль</h1>";
  else
    echo "<h1>Введите пароль к программе установки</h1>";
  die("<p>Пароль задается константой <code>REATESTER_SETUP_PASSWORD</code> в файле конфигурации <code>config/config.inc.php</code>.</p>".
    "<form><p>Пароль:&nbsp;&nbsp;<input id='password' type='password' name='password' />&nbsp;&nbsp;&nbsp;<input type='submit' value='Продолжить установку' /></p></form>".
    "<script> jQuery(function($) { $('#password').focus(); }); </script>");
}
$PROPAGATE['password_hash'] = $_REQUEST['password_hash'];
  
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (version_to_int(phpversion()) < version_to_int(REQUIRED_PHP_VERSION) && !isset($_REQUEST['skipvercheck']))
  die("<h1>Неподдерживаемая версия PHP</h1>".
    "<p>У вас стоит PHP версии ".phpversion().", тогда как требуется версия ".REQUIRED_PHP_VERSION.".</p>");
if (isset($_REQUEST['skipvercheck']))
  $PROPAGATE['skipvercheck'] = '1';

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!isset($_REQUEST['rewriteok'])) {
  $fail_url = addslashes("setup.php?rewriteok=0&".propagate_query_string());
  $ok_url = addslashes("setup.php?rewriteok=1&".propagate_query_string());
  die("<h1>Проверка работы mod_rewrite</h1>".
    "<p>Пожалуйста, подождите несколько секунд…</p>".
    "<script>jQuery(function($) { window.setTimeout(function() { $.ajax({ url: '/setup-rewrite-check/',".
    "    success: function(data) { window.location.href = (data == 'OK' ? '$ok_url' : '$fail_url'); },".
    "    error: function() { window.location.href = '$fail_url'; }".
    "  }); }, 1000); });</script>");
}

if ($_REQUEST['rewriteok'] == '1')
  $PROPAGATE['rewriteok'] = $_REQUEST['rewriteok'];
else
  die("<h1>Ошибка: mod_rewrite не работает</h1>".
    "<p>Обнаружено, что mod_rewrite не работает, или ваш Apache не интерпретирует <code>.htaccess</code>, или вы пользуетесь не Apache.</p>".
    "<p>В любом случае, для работы re:tester необходим mod_rewrite. Обратитесь к квалифицированному администратору для его установки и настройки.</p>".
    "<p><a href='/setup.php?".htmlentities(propagate_query_string())."'>Повторить проверку</a></p>");

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

function check_writable($dir, &$errors) {
  if (!is_dir($dir)) @mkdir($dir, 0777, true);
  if (!is_dir($dir))
    $errors[] = "<p>Директория <code>$dir</code> не существует. Пожалуйста, создайте её командой «<code>mkdir -m 777 $dir</code>».</p>";
  else {
    $data = rand().time();
    @file_put_contents($dir."/dummy-file.tmp", $data);
    if (@file_get_contents($dir."/dummy-file.tmp") != $data)
      $errors[] = "<p>У скрипта нет прав записи в директорию <code>$dir</code>. Пожалуйста, смените её права командой «<code>chmod 777 $dir</code>».</p>";
    @unlink($dir."/dummy-file.tmp");
  }
}

if(!isset($_REQUEST['modeok'])) {
  $errors = array();
  check_writable("tmp", $errors);
  check_writable("tmp/haml", $errors);
  check_writable("tmp/uploads", $errors);
  check_writable("data", $errors);
  check_writable("data/designs", $errors);
  check_writable("data/finishers", $errors);
  check_writable("data/handlers", $errors);
  check_writable("data/images", $errors);
  if ($errors) {
    die("<h1>Настройте права доступа к директориям</h1>".
      "<p>Для работы скрипту необходимо записывать файлы в некоторые директории. Пожалуйста, настройте права доступа согласно следующим инструкциям.</p>"
      .implode("\n", $errors).
      "<p><a href='/setup.php?".htmlentities(propagate_query_string())."'>Повторить проверку</a></p>");
  } else {
    $PROPAGATE['modeok'] = '1';
    redirect();
  }
}
$PROPAGATE['modeok'] = '1';

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(!isset($_REQUEST['confirm'])) {
  if (!isset($_REQUEST['connecterr']))
    echo "<h1>Конфигурация</h1>" . "<p>Пожалуйста, проверьте базовые настройки скрипта.</p>";
  else
    echo "<h1>Ошибочная конфигурация</h1>" . "<p>Не удалось подключиться к MySQL-серверу. Пожалуйста, проверьте настройки еще раз.</p>";
  echo "<table width='100%' border='1'>\n";
  echo "<tr><th>Настройка</th><th>Значение</th><th>Константа</th></tr>\n";
  echo "<tr><td>Хост MySQL</td><td>" . REATESTER_DB_HOST . "</td><td>REATESTER_DB_HOST</td></tr>\n";
  echo "<tr><td>Имя пользователя MySQL</td><td>" . REATESTER_DB_USER . "</td><td>REATESTER_DB_USER</td></tr>\n";
  echo "<tr><td>Пароль MySQL</td><td>" . REATESTER_DB_PASSWORD . "</td><td>REATESTER_DB_PASSWORD</td></tr>\n";
  echo "<tr><td>Название базы данных MySQL</td><td>" . REATESTER_DB_NAME . "</td><td>REATESTER_DB_NAME</td></tr>\n";
  echo "<tr><td>СМС-префикс биллинга</td><td>" . REATESTER_SMS_PREFIX . "</td><td>REATESTER_SMS_PREFIX</td></tr>\n";
  echo "</table>\n";
  echo "<p>Эти настройки можно изменить в файле <code>config/config.inc.php</code>.</p>";
  die("<p><input type='submit' onclick=\"window.location.href = '/setup.php?confirm=1&amp;".htmlentities(propagate_query_string())."';\" value='Да, всё правильно настроено' /></p></form>");
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

$r = @mysql_connect(REATESTER_DB_HOST, REATESTER_DB_USER, REATESTER_DB_PASSWORD);
if (!$r)
  redirect('connecterr=1&');

$PROPAGATE['confirm'] = '1';

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(!isset($_REQUEST['start'])) {
  echo "<h1>Всё готово к началу установки</h1>";
  echo "<p>Будет создана и инициализирована пустая база данных.</p>";
  die("<form method='POST' action='/setup.php?start=1&amp;".htmlentities(propagate_query_string())."'>".
    "<p><input type='checkbox' name='testdata' id='testdata'><label for='testdata'> Наполнить базу данных большим количеством данных для примера</label></p>".
    "<p><input type='submit' value='Установить re:tester!' /></p>".
    "</form>");
}
$PROPAGATE['start'] = '1';
$PROPAGATE['testdata'] = (isset($_REQUEST['testdata']) ? '1' : '0');
$testdata = ($PROPAGATE['testdata'] == '1');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

function log_query($sql) {
  $fd = fopen('/tmp/query.log', 'a');
  fprintf($fd, "$sql\n");
  fclose($fd);
}

function execute($sql) {
  $args = func_get_args();
  array_shift($args);
  return execute_with_array($sql, $args);
}

function execute_with_array($sql, $args) {
  foreach($args as &$arg) {
    if (is_array($arg)) {
      if (count($arg) == 0) $arg = array(-1);
      $parts = array();
      foreach ($arg as $part)
        $parts[] = "'" . mysql_real_escape_string("$part") . "'";
      $arg = "(" . implode(",", $parts) . ")";
    } else {
      $arg = "'" . mysql_real_escape_string("$arg") . "'";
    }
  }
  $sql = str_replace("?", "%s", $sql);
  array_unshift($args, $sql);
  $sql = call_user_func_array('sprintf', $args);
  log_query($sql);
  $res = mysql_query($sql);
  if (!$res)
    die("<h1>Ошибка работы с базой данных</h1>" . 
      "<p>При исполнении запроса возникла следующая ошибка: ".htmlentities(mysql_error())."</p>".
      "<p>Неудавшийся запрос: <code>".htmlentities($sql)."</code></p>");
  return mysql_affected_rows();
}

$DDL = <<<EOT
create table tests (
  id int auto_increment not null primary key,
  name varchar(255) not null,
  created_at timestamp not null default current_timestamp,
  design_file varchar(255),
  handler_file varchar(255),
  finisher_file varchar(255),
  sms_enabled tinyint not null
);

create table questions (
  id int auto_increment not null primary key,
  test_id int not null,
  created_at timestamp not null default current_timestamp,
  `order` int not null,
  `text` longtext not null default "",
  image_file varchar(255),
  count_bounces int not null default 0,
  count_answers int not null default 0
);

create table answers (
  id int auto_increment not null primary key,
  question_id int not null,
  `order` int not null,
  `text` longtext not null default "",
  points int not null default 0,
  image_file varchar(255),
  count_answers int not null default 0
);

create table partners (
  id int auto_increment not null primary key,
  email varchar(255) not null,
  password_salt varchar(255) not null,
  password_hash varchar(255) not null,
  first_name varchar(255) not null,
  last_name varchar(255) not null,
  middle_name varchar(255) not null,
  phone varchar(255) not null,
  icq varchar(255) not null,
  wmid varchar(255) not null,
  earning_percent decimal(12,4) not null default '70'
);

create table sessions (
  id int auto_increment not null primary key,
  partner_id int not null, /* 0 for none */
  test_id int not null,
  day date not null,
  bounce_question_id int not null,
  paid tinyint not null,
  answer_count int not null default 0,
  started_at timestamp not null default current_timestamp,
  finished_at timestamp null,
  sms_chal varchar(10) null,
  sms_resp varchar(10) null,
  sms_received_at timestamp null,
  service_earning decimal(12,4) not null default 0,
  partner_earning decimal(12,4) not null default 0
);

create table daily_statistics (
  day date not null,
  partner_id int not null, /* 0 for none */
  test_id int not null,
  count_free_starts int not null default 0,
  count_free_finishes int not null default 0,
  count_starts int not null default 0,
  count_finishes int not null default 0,
  count_smses int not null default 0,
  service_earning decimal(12,4) not null default 0,
  partner_earning decimal(12,4) not null default 0,
  primary key (day, partner_id, test_id)
);

create table smses (
  id int not null auto_increment primary key,
  smsid varchar(20) not null,
  carrier_id int null,
  service_phone varchar(20) not null,
  user_phone varchar(20) not null,
  msg varchar(100) not null,
  suffix varchar(100) not null,
  confidence_rate int not null,
  fee decimal(12,4) null,
  fee_curr varchar(3) null,
  service_earning decimal(12,4) not null,
  partner_earning decimal(12,4) not null,
  status int not null default 0
);

create table payments (
  id int not null auto_increment primary key,
  transferred_at datetime not null,
  created_at timestamp not null default current_timestamp,
  partner_id int not null,
  amount decimal(12,4) not null,
  previous_period_balance decimal(12,4) not null
);

EOT;

if (!isset($_REQUEST['done'])) {
  execute("drop database ".REATESTER_DB_NAME);
  execute("create database ".REATESTER_DB_NAME." default charset utf8");
  mysql_select_db(REATESTER_DB_NAME);
  foreach(explode("\n\n", $DDL) as $sql) 
    execute($sql);
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!isset($_REQUEST['done']) && $testdata) {
  include 'lib/dbkit.inc.php';
  include 'lib/models.inc.php';
  include 'lib/loginkit.inc.php';
  include 'lib/statistics.inc.php';
  include 'lib/utilities.inc.php';
  
  function add_test($name) {
    $test = new Test;
    $test->name = $name;
    $test->design_file = 'stupid_design.php';
    $test->handler_file = 'random_order.php';
    $test->finisher_file = 'sms_points_printer.php';
    $test->sms_enabled = 1;
    $test->put();
    $test->all_questions = array();
    return $test;
  }
  
  function add_question($test, $text, $answers) {
    if (!isset($test->question_count)) $test->question_count = 0;
    $question = new Question;
    $question->test_id = $test->id;
    $question->order = ++$test->question_count;
    $question->text = $text;
    $question->put();
    $question->all_answers = array();
    
    $answer_count = 0;
    foreach($answers as $answer_text) {
      $answer = new Answer;
      $answer->question_id = $question->id;
      $answer->order = ++$answer_count;
      $answer->text = $answer_text;
      $answer->put();
      $question->all_answers[] = $answer;
    }
    $test->all_questions[] = $question;
  }
  
  function add_partner($email, $name) {
    $partner = new Partner;
    $partner->email = $email;
    list($partner->last_name, $partner->first_name, $partner->middle_name) = explode(" ", $name);
    $partner->password = array_shift(explode('@', $email));
    loginkit_update_password_hash($partner);
    $partner->put();
    return $partner;
  }
  
  srand(3546565); // random test data should be the same every time

  $all_tests[] = $test = add_test("IQ-тест");
  add_question($test, "Как называется приспособление для подъема воды из колодца?", array("Журавль", "Аист", "Цапля", "Страус"));
  add_question($test, "Чью мать обещал показать американцам Хрущев?", array("Кузькину", "Чертову", "Свою", "Микояна"));
  add_question($test, "Какое прозвище носила Манька в фильме «Место встречи изменить нельзя»?", array("Акция", "Ваучер", "Облигация", "Лотерейный билет"));
  
  $all_tests[] = $test = add_test("Тест на интеллект");
  add_question($test, "Кем работал в зоопарке Крокодил Гена?", array("Сторожем", "Директором", "Дрессировщиком", "Крокодилом"));
  add_question($test, "Какой запах, как утверждают, сопровождает появление нечистой силы?", array("Нашатырного спирта", "Озона", "Серы", "Хлора"));
  add_question($test, "Что (или кто) «нечаянно нагрянет, когда ее совсем не ждешь»?", array("Жена", "Зима", "Налоговая инспекция", "Любовь"));
  
  
  $all_partners[] = add_partner('andreyvit@gmail.com', "Таранцов Андрей Витальевич");
  $all_partners[] = add_partner('fourdman@gmail.com', "Калугин Михаил Борисович");
  
  $percent_partner_sessions = 70;
  $percent_sessions = array(10, 50, 5, 35); // unfinished, finished-nosms, finished-sms-nocode, finished
  $time_range = 90; // days
  $session_count = 1000;
  $payment_count = floor($time_range / 7) * count($all_partners);
  
  for($i = 1; $i < count($percent_sessions); $i++) $percent_sessions[$i] += $percent_sessions[$i-1];
  
  for($i = 0; $i < $session_count; $i++) {
    $test = $all_tests[rand(0, count($all_tests)-1)];
    if (rand(0, 100) < $percent_partner_sessions)
      $partner = $all_partners[rand(0, count($all_partners)-1)];
    else
      $partner = null;
    $partner_id = is_null($partner) ? 0 : $partner->id;

    $day_ordinal = rand(0, $time_range);
    $timeofday = rand(0, 60*60*24-1);
    $start_time = time() - $day_ordinal*60*60*24 + $timeofday;
    $day = strftime('%Y-%m-%d', $start_time);
    
    $session_id = stat_test_started($test->id, $partner_id, $day, $test->all_questions[0]->id, $test->sms_enabled);
    
    $r = rand(0, 100);
    $count_questions = ($r <= $percent_sessions[0] ? rand(0, count($test->all_questions)-1) : count($test->all_questions)-1);
    
    for ($q = 0; $q < $count_questions; $q++) {
      $question = $test->all_questions[$i];
      $answer = $question->all_answers[rand(0, count($question->all_answers) - 1)];
      stat_question_answered($session_id, $test->id, $partner_id, $day, $question->id, $answer->id, $test->all_questions[$i+1]->id, $test->sms_enabled);
    }
      
    if ($r > $percent_sessions[0]) {
      $question = $test->all_questions[count($test->all_questions) - 1];
      $answer = $question->all_answers[rand(0, count($question->all_answers) - 1)];
      
      stat_test_finished($session_id, $test->id, $partner_id, $day, $question->id, $answer->id, $test->sms_enabled, random_string(REATESTER_SMS_CHAL_LENGTH), random_string(REATESTER_SMS_RESP_LENGTH));
      
      if ($r > $percent_sessions[1]) {
        stat_sms_received($session_id, $test->id, $partner_id, $day, $test->sms_enabled, 300, 200);
      }
    }
  }
  
  $daily_statistics_by_partner = group_by(DailyStatistics::query("ORDER BY day"), "partner_id");
  
  $payment_dates = array();
  for($i = 0; $i < $payment_count; $i++) $payment_dates[] = time() - rand(0, 60*60*24*$time_range);
  sort($payment_dates);
  
  $partner_last_payment = array();
  $partner_last_stat_index = array();
  for($i = 0; $i < count($all_partners); $i++) { $partner_last_payment[] = null; $partner_last_stat_index[$i] = 0; }
  
  for($i = 0; $i < $payment_count; $i++) {
    $payment = new Payment;
    $payment->transferred_at = $payment_dates[$i];
    $payment->partner_id = $all_partners[rand(0, count($all_partners)-1)]->id;
    
    $last_payment = $partner_last_payment[$payment->partner_id];
    
    $cutoff = end_of_last_finished_period_before($payment->transferred_at);
    
    $balance = 0.0;
    $daily_statistics = $daily_statistics_by_partner[$payment->partner_id];
    for($k = $partner_last_stat_index[$payment->partner_id]; $k < count($daily_statistics); $k++) {
      // echo("day: " . gettype($daily_statistics[$k]) . $k. ", count " . count($daily_statistics) . "<br>\n");
      $tm = strptime($daily_statistics[$k]->day, '%Y-%m-%d');
      $date = mktime(0, 0, 0, $tm['mon'], $tm['mday'], $tm['year']);
      if (!is_null($last_payment) && $date <= end_of_last_finished_period_before($last_payment->transferred_at))
        die("internal error 345678732: statistics for day " . $daily_statistics[$k]->day);
      if ($date > $cutoff)
        break;
      $balance += $daily_statistics[$k]->partner_earning;
    }
    $partner_last_stat_index[$payment->partner_id] = $k;
    
    if (!is_null($last_payment))
      $balance = $balance + $last_payment->previous_period_balance - $last_payment->amount;
    
    $payment->previous_period_balance = $balance;
    $payment->amount = min(20000, rand(floor($balance/2000), floor($balance/1000))*1000);
    
    if ($payment->amount < -0.00001)
      die("internal error 9087654345");
    if ($payment->amount < 1.00001)
      continue;
    
    $payment->put();
    $partner_last_payment[$payment->partner_id] = $payment;
  }
  

  // add_question($test, "", array("", "", "", ""));
  // add_question($test, "", array("", "", "", ""));
  // add_question($test, "", array("", "", "", ""));
  // add_question($test, "", array("", "", "", ""));
  // add_question($test, "", array("", "", "", ""));
  // add_question($test, "", array("", "", "", ""));
  
  
}

if (!isset($_REQUEST['done'])) {
  $again_url = '/setup.php?' . propagate_query_string();
  $PROPAGATE['done'] = '1';
  redirect('again=' . urlencode($again_url) . '&');
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo "<h1>Поздравляем, re:tester готов к работе</h1>";
echo "<p>Администрирование доступно по адресу <a href='http://$_SERVER[HTTP_HOST]/admin/'>http://$_SERVER[HTTP_HOST]/admin/</a>.</p>";
echo "<p>Партнерская программа доступна по адресу <a href='http://$_SERVER[HTTP_HOST]/partner/'>http://$_SERVER[HTTP_HOST]/partner/</a>.</p>";
echo "<p>Список тестов доступен по адресу <a href='http://$_SERVER[HTTP_HOST]/'>http://$_SERVER[HTTP_HOST]/</a>.</p>";
echo "<p><a href='" . htmlentities($_REQUEST['again']) . "'>Повторить установку</a></p>";

?>
