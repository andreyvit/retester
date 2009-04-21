<?php

require_once dirname(__FILE__) . "/../config/config.inc.php";

mysql_connect(REATESTER_DB_HOST, REATESTER_DB_USER, REATESTER_DB_PASSWORD) or
  die("cannot connect to the database");
mysql_select_db(REATESTER_DB_NAME)
  or die("cannot select database");
  
class Model {
  function getID() {
    return $this->id;
  }
  
  function is_new() {
    return !$this->is_saved();
  }
  
  function is_saved() {
    return $this->id && strspn($this->id, '1234567890') > 0;
  }
  
  function is_empty() {
    return false;
  }
  
  function put_or_delete() {
    if ($this->is_empty())
      $this->delete();
    else
      $this->put();
  }
}

class Test extends Model {
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
}

class Answer extends Model {
  function put() {
    if ($this->is_saved())
      execute("UPDATE `answers` SET `text`='%s', `order`='%s', `is_correct`='%s' WHERE id=%s", $this->text, $this->order, $this->is_correct, $this->id);
    else {
      execute("INSERT INTO `answers` (`text`, `order`, `question_id`, `is_correct`) VALUES ('%s', '%s', '%s', '%s')", $this->text, $this->order, $this->question_id, $this->is_correct);
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

function log_query($sql) {
  $fd = fopen('/tmp/query.log', 'a');
  fprintf($fd, "$sql\n");
  fclose($fd);
}

function execute($sql /*, $arg... */) {
  $args = func_get_args();
  array_shift($args);
  foreach($args as &$arg) {
    $arg = mysql_real_escape_string("$arg");
  }
  array_unshift($args, $sql);
  $sql = call_user_func_array('sprintf', $args);
  log_query($sql);
  mysql_query($sql) or die("database query failed: ".mysql_error());
}

function query($klass, $sql /*, $arg... */) {
  $args = func_get_args();
  array_shift($args);
  array_shift($args);
  foreach($args as &$arg) {
    $arg = mysql_real_escape_string("$arg");
  }
  array_unshift($args, $sql);
  $sql = call_user_func_array('sprintf', $args);
  log_query($sql);
  $r = mysql_query($sql) or die("database query failed: ".mysql_error());
  $res = array();
  while($row = mysql_fetch_object($r, $klass)) {
    $res[] = $row;
  }
  mysql_free_result($r);
  return $res;
}

function get($klass, $sql /*, $arg... */) {
  $args = func_get_args();
  $rows = call_user_func_array("query", $args);
  if (count($rows) == 0)
    return false;
  else
    return $rows[0];
}

?>