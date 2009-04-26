<?php
require_once 'lib/common.inc.php';

$test_id = intval($_GET['test_id']);
if ($test_id == 0)
  die("invalid test_id");
  
$test = get('Test', "SELECT id, name FROM tests WHERE `id` = %d", $test_id);
if (!$test) {
  include('templates/test_no_longer_exists.inc.php');
  exit;
}

if (!isset($_SESSION['tests']))
  $_SESSION['tests'] = array();

function run_handler(&$RES, $test) {
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
    $question = get('Question', "SELECT `id`, `text`, `order` FROM questions WHERE `order` = %d AND `test_id` = %d LIMIT 1", $t->order, $test->id);
  } else if (is_array($action)) {
    $t = query('Model', "SELECT `id` FROM `questions` WHERE `test_id`=%d AND `order` BETWEEN %d AND %d AND $id_cond",
      $test->id, $action[0], $action[1], $answered_question_ids);
    if (empty($t)) {
      $action = 'finish';
    } else {
      $t = $t[mt_rand(0, count($t) - 1)];
      $question = get('Question', "SELECT `id`, `text`, `order` FROM questions WHERE `id` = %d AND `test_id` = %d LIMIT 1",
          $t->id, $test->id);
    }
  }
  if ($question) {
    $RES->question_id = $question->id;
    $RES->question_ord = $question->ord;
    $RES->question_no++;
    return $question;
  }
  if ($action == 'finish') {
    $RES->finished = true;
    redirect("test.php?test_id=$test->id");
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
    redirect("test.php?test_id=$test->id");
    die();
  }
  
  $answer_id = intval($_POST['answer']);
  if ($answer_id == 0)
    die("Bad request: answer not specified");
  
  $question = get('Question', "SELECT `id`, `order` FROM questions WHERE `id` = %d AND `test_id` = %d", $RES->question_id, $test->id);
  if ($question) {
    $answer = get('Answer', "SELECT `id`, `order`, `is_correct` FROM answers WHERE `id`=%d AND `question_id`=%d", $answer_id, $question->id);
    if ($answer) {
      $a = new QuestionResult;
      $a->question_id = $question->id;
      $a->question_ord = $question->order;
      $a->ord = $answer->order;
      $a->points = ($answer->is_correct ? 1 : 0);
      $RES->answers[] = $a;
    }
  }
  
  include($test->handler_file());
  if (!function_exists('next_action'))
    die("Invalid handler $handler_file: a handler must define function next_action(\$RES, \$question_count)");
  $question = run_handler($RES, $test);
  redirect("test.php?test_id=$test->id");
  exit;
}

if (!isset($_SESSION['tests'][$test_id])) {
  $_SESSION['tests'][$test_id] = new TestResult;
  $RES =& $_SESSION['tests'][$test_id];
  $RES->answers = array();
  $RES->finished = false;
}
$RES =& $_SESSION['tests'][$test_id];

$again_url = "again.php?test_id=$test->id";
$submit_url = "test.php?test_id=$test->id";

if (!isset($RES->question_no)) {
  $RES->question_no = 0;
  include($test->handler_file());
  if (!function_exists('next_action'))
    die("Invalid handler $handler_file: a handler must define function next_action(\$RES, \$question_count)");
  $question = run_handler($RES, $test);
} else if ($RES->finished) {
  include($test->finish_file());
  exit;
} else {
  // we always pick a question by id in GET request, so that pressing F5 will render the same
  // question over and over again
  $question = get('Question', "SELECT `id`, `text`, `order` FROM questions WHERE `id` = %d AND `test_id` = %d", $RES->question_id, $test_id);
}

$answers = query('Answer', "SELECT `id`, `text`, `order` FROM answers WHERE `question_id` = %d ORDER BY `order`", $question->id);

include('templates/question.inc.php');

?>
