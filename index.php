<?php
require_once 'lib/common.inc.php';

if (isset($_SESSION['tests'])) {
  $active_tests = Test::query_indexed('id', "SELECT id, name FROM tests WHERE id IN %s", array_keys($_SESSION['tests']));
  foreach ($_SESSION['tests'] as $test_id => $v) {
    if ($v->question_no == 1)
      unset($active_tests[intval($test_id)]);
    else {
      $active_tests[intval($test_id)]->question_no = $v->question_no;
      $active_tests[intval($test_id)]->url = "/tests/$test_id/";
    }
  }
} else {
  $active_tests = array();
}

$tests = Test::query("SELECT id, name FROM tests ORDER BY name");
foreach ($tests as &$test) {
  $test->url = "/tests/$test->id/";
}
unset($test);

include('templates/index.inc.php');

?>
