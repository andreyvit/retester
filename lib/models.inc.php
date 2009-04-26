<?php

class Test extends Model {
  function put() {
    if ($this->is_saved())
      ;
    else
      $this->do_insert("tests", array('name'));
  }
  
  function delete() {
    $questions = query('Question', "SELECT id FROM questions WHERE test_id = '%s'", $this->id);
    foreach ($questions as $q)
      $q->delete();
    execute("DELETE FROM tests WHERE id = '%s'", $this->id);
  }
  
  function handler_file() {
    return 'handlers/handler.inc.php';
  }
  
  function finish_file() {
    return 'handlers/finished.inc.php';
  }
  
}

class Question extends Model {  
  function put() {
    if ($this->is_saved())
      execute("UPDATE questions SET text = '%s', `order` = '%s' WHERE id=%s", $this->text, $this->order, $this->id);
    else {
      execute("INSERT INTO questions(text, `order`, test_id) VALUES ('%s', '%s', '%s')", $this->text, $this->order, $this->test_id);
      $this->id = mysql_insert_id();
    }
  }
  
  function delete() {
    execute("DELETE FROM answers WHERE question_id = '%s'", $this->id);
    execute("DELETE FROM questions WHERE id = '%s'", $this->id);
  }
}

class Answer extends Model {
  function put() {
    if ($this->is_saved())
      execute("UPDATE `answers` SET `text`='%s', `order`='%s', `points`='%s' WHERE id=%s", $this->text, $this->order, $this->points, $this->id);
    else {
      execute("INSERT INTO `answers` (`text`, `order`, `question_id`, `points`) VALUES ('%s', '%s', '%s', '%s')", $this->text, $this->order, $this->question_id, $this->points);
      $this->id = mysql_insert_id();
    }
  }
  
  function is_empty() {
    return !$this->text;
  }
  
  function delete() {
    if ($this->is_saved())
      execute("DELETE FROM `answers` WHERE id = %s", $this->id);
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