<?php

define('IMAGE_UPLOAD_BASE_DIR', '../data/images');

class Test extends Model {
  
  var $table_name = 'tests';
  var $id, $name, $design_file, $handler_file, $finisher_file, $sms_enabled;
  
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

class Partner extends Model {
  
  var $table_name = "partners";
  var $id, $email, $first_name, $last_name, $middle_name, $phone, $icq, $wmid;
  var $password_salt, $password_hash; // NOT form fields
  var $form_fields = array('email', 'first_name', 'last_name', 'middle_name', 'phone', 'icq', 'wmid');
  
  var $loginkit__tags = array('partner+');
  
  function validate() {
    if (empty($this->email))
      $this->field_error('email', 'Обязательное поле.');
    else if (!validate_email($this->email))
      $this->field_error('email', 'Неверный формат адреса.');

    if ($this->is_saved() && empty($this->password) && !empty($this->password_confirmation)) {
      $this->field_error('password', 'Пароль нужно вводить в оба поля.');
      $this->field_error('password_confirmation', 'Пароль нужно вводить в оба поля.');
    } else if ($this->is_new() || !empty($this->password)) {
      if (empty($this->password)) {
        $this->field_error('password', 'Обязательное поле.');
        $this->field_error('password_confirmation', 'Пожалуйста, введите пароль еще раз.');
      } else if (strlen($this->password) < 6) {
        $this->field_error('password', 'Требуется как минимум 6 символов.');
        $this->field_error('password_confirmation', 'Пожалуйста, введите пароль еще раз.');
      } else if ($this->password != $this->password_confirmation) {
        $this->field_error('password', 'Пожалуйста, введите пароль еще раз.');
        $this->field_error('password_confirmation', 'Пароли не совпали.');
      }
    }
      
    if (empty($this->first_name))
      $this->field_error('first_name', 'Обязательное поле.');
    else if (strlen($this->first_name) < 2)
      $this->field_error('first_name', 'Требуется как минимум 2 символа.');
      
    if (empty($this->last_name))
      $this->field_error('last_name', 'Обязательное поле.');
    else if (strlen($this->last_name) < 2)
      $this->field_error('last_name', 'Требуется как минимум 2 символа.');
      
    if (!empty($this->middle_name))
      if (strlen($this->middle_name) < 2)
        $this->field_error('middle_name', 'Требуется как минимум 2 символа.');
      
    if (empty($this->phone))
      $this->field_error('phone', 'Обязательное поле.');
    else if (strlen($this->phone) < 7)
      $this->field_error('phone', 'Требуется как минимум 7 символов.');
    else if (!preg_match('/^[\\d+ ()-]+$/', $this->phone))
      $this->field_error('phone', 'Неверный формат данных.');
      
    if (!empty($this->icq))
      if (strlen($this->icq) < 4)
        $this->field_error('icq', 'Требуется как минимум 4 символа.');
      else if (!preg_match('/^[\d -]+$/', $this->icq))
        $this->field_error('icq', 'Неверный формат данных.');
      
    if (!empty($this->wmid))
      if (strlen($this->wmid) != 13)
        $this->field_error('wmid', 'Требуется ровно 13 символов: буква валюты и 12 цифр.');
      else if (!preg_match('/^[RZU]\d{12}$/', $this->wmid))
        $this->field_error('wmid', 'Неверный формат: требуется буква валюты (R, Z или U) и 12 цифр.');
  }
  
}

class DailyStatistics {
  
  var $table_name = 'daily_statistics';
  
  var $day, $partner_id, $test_id;
  var $count_free_starts, $count_free_finishes;
  var $count_starts, $count_finishes, $count_smses;
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