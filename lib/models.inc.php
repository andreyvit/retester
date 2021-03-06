<?php

define('IMAGE_UPLOAD_BASE_DIR', '../data/images');

class Test extends DBkitModel {
  
  var $table_name = 'tests';
  var $id, $name, $design_file, $handler_file, $finisher_file, $sms_enabled;
  
  function delete_children() {
    $questions = Question::query("SELECT id FROM questions WHERE test_id = ?", $this->id);
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

class Question extends DBkitModel {
  
  var $table_name = 'questions';
  var $id, $test_id, $text, $order, $image_file;
  
  function delete_children() {
    dbkit_execute("DELETE FROM answers WHERE question_id = ?", $this->id);
    dbkit_execute("DELETE FROM questions WHERE id = ?", $this->id);
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
      return "/images/placeholder.png";
    else
      return $this->image_path();
  }
  
  function image_path() {
    return "/data/images/{$this->image_file}";
  }
  
}

class Answer extends DBkitModel {
  
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
      return "/images/placeholder.png";
    else
      return $this->image_path();
  }

  function image_path() {
    return "/data/images/{$this->image_file}";
  }

  function image_code() {
    return empty($this->image_file) ? '' : 'keep';
  }

}

class Partner extends DBkitModel {
  
  var $table_name = "partners";
  var $id, $email, $first_name, $last_name, $middle_name, $phone, $icq, $wmid;
  var $password_salt, $password_hash; // NOT form fields
  var $form_fields = array('email', 'first_name', 'last_name', 'middle_name', 'phone', 'icq', 'wmid');
  var $earning_percent;
  
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

class DailyStatistics extends DBkitModel {
  
  var $table_name = 'daily_statistics';
  
  var $day, $partner_id, $test_id;
  var $count_free_starts, $count_free_finishes;
  var $count_starts, $count_finishes, $count_smses;
  var $service_earning, $partner_earning;
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

class TestSession extends DBkitModel {
  var $table_name = 'sessions';
  
  var $id;
  var $partner_id, $test_id, $day, $bounce_question_id;
  var $paid, $answer_count, $started_at, $finished_at, $sms_received_at;
  var $sms_chal, $sms_resp;
}

class SMS extends DBkitModel {
  var $table_name = 'smses';
  
  var $id, $smsid, $carrier_id, $service_phone, $user_phone, $msg, $suffix, $confidence_rate;
  var $fee, $fee_curr, $service_earning, $partner_earning;
  var $status;
}

class Payment extends DBkitModel {
  var $table_name = 'payments';
  var $id, $transferred_at, $created_at, $partner_id, $amount, $previous_period_balance;
}

define('SMS_STATUS_PROCESSING', 0);
define('SMS_STATUS_OK', 1);
define('SMS_STATUS_INVALID_SUFFIX_FORMAT', 10);
define('SMS_STATUS_SESSION_NOT_FOUND', 11);

?>
