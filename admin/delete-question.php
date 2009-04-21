<?php
include '../lib/common.inc.php';

$question = new Question();
$question->id = $_REQUEST['question_id'];
$question->delete();

?>