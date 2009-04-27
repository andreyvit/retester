<?php

class Test extends Model {
  
  var $table_name = 'tests';
  var $id, $name, $design_file, $handler_file, $finisher_file;
  
  function delete_children() {
    $questions = query('Question', "SELECT id FROM questions WHERE test_id = '%s'", $this->id);
    foreach ($questions as $q)
      $q->delete();
  }
  
  function design_file() {
    return "data/designs/$this->design_file";
  }
  
  function handler_file() {
    return "data/handlers/$this->handler_file";
  }
  
  function finisher_file() {
    return "data/finishers/$this->finisher_file";
  }
  
}

class Question extends Model {
  
  var $table_name = 'questions';
  var $id, $test_id, $text, $order;
  
  function delete_children() {
    execute("DELETE FROM answers WHERE question_id = '%s'", $this->id);
    execute("DELETE FROM questions WHERE id = '%s'", $this->id);
  }
  
}

class Answer extends Model {
  
  var $table_name = 'answers';
  var $id, $question_id, $text, $order, $points;
  
  function is_empty() {
    return !$this->text;
  }

}

class TestResult {
  // $question_no
  // $answers
}

class QuestionResult {
  // $question_ord
  // $ord
  // $points
}

?>