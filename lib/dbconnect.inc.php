<?php

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
  
  function do_insert($table, $fields) {
    $names = array();
    $values = array();
    $args = array();
    foreach($fields as $field) {
      $names[] = "`$field`";
      $values[] = "'%s'";
      $args[] = $this->$field;
    }
    $names = implode(", ", $names);
    $values = implode(", ", $values);
    array_unshift($args, "INSERT INTO `$table`($names) VALUES ($values)");
    call_user_func_array("execute", $args);
    $this->id = mysql_insert_id();
  }
}

function log_query($sql) {
  $fd = fopen('/tmp/query.log', 'a');
  fprintf($fd, "$sql\n");
  fclose($fd);
}

// res run_query(string sql, mixed arg...)
function run_query($sql) {
  $args = func_get_args();
  array_shift($args);
  foreach($args as &$arg) {
    if (is_array($arg)) {
      $parts = array();
      foreach ($arg as $part)
        $parts[] = mysql_real_escape_string("$part");
      $arg = "(" . implode(",", $parts) . ")";
    } else {
      $arg = mysql_real_escape_string("$arg");
    }
  }
  array_unshift($args, $sql);
  $sql = call_user_func_array('sprintf', $args);
  log_query($sql);
  $res = mysql_query($sql);
  if (!$res) die("database query failed: ".mysql_error());
  return $res;
}

// void execute(string sql, mixed arg...)
function execute($sql) {
  $args = func_get_args();
  call_user_func_array('run_query', $args);
}

function query($klass, $sql /*, $arg... */) {
  $args = func_get_args();
  array_shift($args);
  $r = call_user_func_array('run_query', $args);
  $res = array();
  while($row = mysql_fetch_object($r, $klass)) {
    $res[] = $row;
  }
  mysql_free_result($r);
  return $res;
}

function query_indexed($klass, $attr, $sql /*, $arg... */) {
  $args = func_get_args();
  array_splice($args, 1, 1);
  $array = call_user_func_array('query', $args);
  return index_by($array, $attr);
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
