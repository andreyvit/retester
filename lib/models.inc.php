<?php

define('IMAGE_UPLOAD_BASE_DIR', '../data/images');

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
  var $id, $test_id, $text, $order, $image_file;
  
  function delete_children() {
    execute("DELETE FROM answers WHERE question_id = '%s'", $this->id);
    execute("DELETE FROM questions WHERE id = '%s'", $this->id);
  }
  
  function normalize_text($v) {
    return trim($v);
  }
  
  function wakeup() {
    $this->image_code = empty($this->image_file) ? '' : 'keep';
  }
  
  function normalize() {
    $this->normalize_file_field('image_code', 'image_file', "test_{$this->test_id}/q_{$this->id}", IMAGE_UPLOAD_BASE_DIR);
  }
  
  function image_file_or_placeholder() {
    if (empty($this->image_file))
      return WEB_ROOT."/images/placeholder.png";
    else
      return $this->image_path();
  }
  
  function image_path() {
    return WEB_ROOT."/data/images/{$this->image_file}";
  }
  
}

class Answer extends Model {
  
  var $table_name = 'answers';
  var $id, $question_id, $text, $order, $points, $image_file;
  
  function wakeup() {
    $this->image_code = empty($this->image_file) ? '' : 'keep';
  }
  
  function is_empty() {
    return empty($this->text) && empty($this->image_code);
  }
  
  function normalize() {
    $this->normalize_file_field('image_code', 'image_file', "test_{$this->test_id}/q_{$this->question_id}_a_{$this->id}", IMAGE_UPLOAD_BASE_DIR);
  }
  
  function image_file_or_placeholder() {
    if (empty($this->image_file))
      return WEB_ROOT."/images/placeholder.png";
    else
      return $this->image_path();
  }

  function image_path() {
    return WEB_ROOT."/data/images/{$this->image_file}";
  }

  function image_code() {
    return empty($this->image_file) ? '' : 'keep';
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