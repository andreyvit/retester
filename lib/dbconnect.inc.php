<?php

mysql_connect(REATESTER_DB_HOST, REATESTER_DB_USER, REATESTER_DB_PASSWORD) or
  die("cannot connect to the database");
mysql_select_db(REATESTER_DB_NAME)
  or die("cannot select database");
  
class Model {
  
  // public methods
  
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
  
  function put() {
    if ($this->is_saved())
      $this->do_update($this->get_fields_for_update());
    else
      $this->do_insert($this->get_fields_for_insert());
  }
  
  function put_or_delete() {
    if ($this->is_empty())
      $this->delete();
    else
      $this->put();
  }
  
  function assign($prefix, $fields) {
    foreach ($fields as $field) {
      $v = (isset($_REQUEST[$prefix.$field]) ? $_REQUEST[$prefix.$field] : null);
      if (method_exists($this, "normalize_".$field))
        $v = call_user_method("normalize_".$field, $this, $v);
      if (!is_null($v))
        $this->$field = $v;
    }
    $this->normalize();
    
    $this->overall_errors = array();
    $this->field_errors = new stdClass;
    $this->validate();
  }
  
  function delete() {
    if ($this->is_saved()) {
      execute("DELETE FROM `$this->table_name` WHERE `id` = '%s'", $this->id);
      $this->delete_children();
      $this->id = null;
    }
  }
  
  // protected, override points

  // void normalize() -- postprocess data after receiving it from a form
  function normalize() {
    // if($this->some_field) $this->some_other_field = trim($this->some_other_field);
    // for simpler cases, define "mixed normalize_$field(mixed value)" that returns a normalized value
  }
  
  // void validate()
  function validate() {
    // if(empty($this->name)) $this->field_error('name', "Required.");
  }
  
  function delete_children() {
    // execute("DELETE FROM child_items WHERE parent_id = '%s'", $this->id);
  }
  
  // protected
  
  function field_error($field, $message) {
    // never overwrite a field-specific error message to allow checking for several error conditions
    if (!isset($this->field_errors->$field))
      $this->field_errors->$field = $message;
  }
  
  function overall_error($message) {
    $this->overall_errors[] = $message;
  }
  
  function is_valid() {
    $vars = get_object_vars($this->field_errors);
    return empty($vars) && empty($this->overall_errors);
  }
  
  // private
  
  function get_fields_for_insert() {
    $fields = array_keys(get_class_vars(get_class($this)));
    foreach($fields as &$field)
      if (strstr($field, "__"))
        $field = false;
    $fields = array_filter($fields);
    $fields = array_diff($fields, array("id", "created_at", "updated_at", "table_name"));
    return $fields;
  }
  
  function get_fields_for_update() {
    $fields = array_keys(get_class_vars(get_class($this)));
    foreach($fields as &$field)
      if (strstr($field, "__"))
        $field = false;
    $fields = array_filter($fields);
    $fields = array_diff($fields, array("id", "created_at", "updated_at", "table_name"));
    return $fields;
  }
  
  function do_insert($fields) {
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
    array_unshift($args, "INSERT INTO `$this->table_name`($names) VALUES ($values)");
    call_user_func_array("execute", $args);
    $this->id = mysql_insert_id();
  }
  
  function do_update($fields) {
    if (isset($this->_fetched_fields)) {
      foreach($fields as &$field)
        if (!in_array($field, $this->_fetched_fields) && (!isset($this->$field) || is_null($this->$field)))
          $field = false;
      $fields = array_filter($fields);
    }
    unset($field);
    $sets = array();
    $args = array();
    foreach($fields as $field) {
      $sets[] = "`$field`='%s'";
      $args[] = $this->$field;
    }
    $sets = implode(", ", $sets);
    $args[] = $this->id;
    array_unshift($args, "UPDATE `$this->table_name` SET $sets WHERE `id`=%d");
    call_user_func_array("execute", $args);
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
  $fields = array();
  $n = mysql_num_fields($r);
  for($i = 0; $i < $n; $i++)
    $fields[] = mysql_field_name($r, $i);
  $res = array();
  while($row = mysql_fetch_object($r, $klass)) {
    $row->_fetched_fields = $fields;
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
