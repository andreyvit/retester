<?php
require_once 'lib/common.inc.php';

$test_id = intval($_GET['test_id']);
if ($test_id == 0)
  die("invalid test_id");
  
$test = get('Test', "WHERE `id` = %d", $test_id);
if (!$test) {
  include('templates/test_no_longer_exists.inc.php');
  exit;
}

if (isset($_REQUEST['partner_id'])) {
  $_SESSION['partner_id'] = intval($_REQUEST['partner_id']);
}

if (!isset($_SESSION['tests']))
  $_SESSION['tests'] = array();

function run_handler(&$RES, $test, $answered_question_id, $answer_id) {
  $t = get('Model', "SELECT MAX(`order`) AS question_count FROM questions WHERE `test_id` = %d", $test->id);
  $question_count = $t->question_count;
  $action = next_action($RES, $question_count);
  if ($action == 'random')
    $action = array(1, $question_count);
  if ($action == 'next')
    $action = $RES->question_ord + 1;
  if (is_integer($action) && $action > $question_count)
    $action = 'finish';
    
  $answered_question_ids = collect_attrs($RES->answers, 'question_id');
  $id_cond = (empty($answered_question_ids) ? "TRUE" : "`id` NOT IN %s");
  if (is_integer($action)) {
    $t = get('Model', "SELECT MIN(`order`) AS `order` FROM `questions` WHERE `test_id`=%d AND `order`>=%d AND $id_cond",
      $test->id, $action, $answered_question_ids);
    if (!$t) {
      // TODO: what to do when the question_ord returned by the handler does not exist?
    }
    $question = get('Question', "WHERE `order` = %d AND `test_id` = %d LIMIT 1", $t->order, $test->id);
  } else if (is_array($action)) {
    $t = query('Model', "SELECT `id` FROM `questions` WHERE `test_id`=%d AND `order` BETWEEN %d AND %d AND $id_cond",
      $test->id, $action[0], $action[1], $answered_question_ids);
    if (empty($t)) {
      $action = 'finish';
    } else {
      $t = $t[mt_rand(0, count($t) - 1)];
      $question = get('Question', "WHERE `id` = %d AND `test_id` = %d LIMIT 1",
          $t->id, $test->id);
    }
  }
  if ($question) {
    $RES->question_id = $question->id;
    $RES->question_ord = $question->order;
    $RES->question_no++;
    if (!is_null($answered_question_id))
      stat_question_answered($RES->session_id, $test->id, $RES->partner_id, $RES->day, $answered_question_id, $answer_id, $RES->question_id, $RES->paid);
    return $question;
  }
  if ($action == 'finish') {
    $RES->finished = true;
    if ($RES->paid) {
      $RES->sms_chal = random_string(REATESTER_SMS_CHAL_LENGTH);
      $RES->sms_resp = random_string(REATESTER_SMS_RESP_LENGTH);
    } else {
      $RES->sms_chal = $RES->sms_resp = null;
    }
    stat_test_finished($RES->session_id, $test->id, $RES->partner_id, $RES->day, $answered_question_id, $answer_id, $RES->paid, $RES->sms_chal, $RES->sms_resp);
    redirect("/tests/$test->id/");
    die();
  }
  die("Internal error: invalid handler action '$action'");
}

if ($_POST) {
  if (!isset($_SESSION['tests']) || !isset($_SESSION['tests'][$test_id])) {
    include('templates/test_expired.inc.php');
    exit;
  }
  $RES =& $_SESSION['tests'][$test_id];
  if ($RES->finished) {
    redirect("/tests/$test->id/");
    die();
  }
  
  $answer_id = intval($_POST['answer']);
  if ($answer_id == 0)
    die("Bad request: answer not specified");
  
  $question = get('Question', "WHERE `id` = %d AND `test_id` = %d", $RES->question_id, $test->id);
  if ($question) {
    $answer = get('Answer', "WHERE `id`=%d AND `question_id`=%d", $answer_id, $question->id);
    if ($answer) {
      $a = new QuestionResult;
      $a->question_id = $question->id;
      $a->question_ord = $question->order;
      $a->ord = $answer->order;
      $a->points = $answer->points;
      $RES->answers[] = $a;
    }
  }
  
  include($test->handler_file());
  if (!function_exists('next_action'))
    die("Invalid handler $handler_file: a handler must define function next_action(\$RES, \$question_count)");
  $question = run_handler($RES, $test, ($question ? $question->id : 0), ($answer ? $answer->id : 0));
  redirect("/tests/$test->id/");
  exit;
}

if (!isset($_SESSION['tests'][$test_id])) {
  $_SESSION['tests'][$test_id] = new TestResult;
  $RES =& $_SESSION['tests'][$test_id];
  $RES->answers = array();
  $RES->finished = false;
}
$RES =& $_SESSION['tests'][$test_id];

$again_url = "/tests/$test->id/restart";
$submit_url = "/tests/$test->id/";

if (!isset($RES->question_no)) {
  // start of test
  $RES->question_no = 0;
  $RES->partner_id = (isset($_SESSION['partner_id']) ? $_SESSION['partner_id'] : 0);
  $RES->day = strftime("%Y-%m-%d");
  // be nice to the user (and to the statistics), save sms_enabled at the start of test in case it is changed later
  $RES->paid = $test->sms_enabled;
  $RES->sms_password_entered = false;
  include($test->handler_file());
  if (!function_exists('next_action'))
    die("Invalid handler $handler_file: a handler must define function next_action(\$RES, \$question_count)");
  // if the handler finishes the test right away, the statistics will end up incorrect
  $question = run_handler($RES, $test, null, null);
  $RES->session_id = stat_test_started($test->id, $RES->partner_id, $RES->day, $question->id, $RES->paid);
} else if ($RES->finished) {
  if ($RES->paid) {
    if ($RES->sms_password_entered)
      $full = true;
    else {
      $full = false;
      ob_start();
      include('templates/sms-info.inc.php');
      $sms_info = ob_get_clean();
    }
  } else {
    $full = true;
  }
  
  include($test->finisher_file());
  exit;
} else {
  // we always pick a question by id in GET request, so that pressing F5 will render the same
  // question over and over again
  $question = get('Question', "WHERE `id` = %d AND `test_id` = %d", $RES->question_id, $test_id);
}

$answers = query('Answer', "WHERE `question_id` = %d ORDER BY `order`", $question->id);

include($test->design_file());

?>
