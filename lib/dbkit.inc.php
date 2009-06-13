<?php

// YourSway DBkit: a tiny sane ORM

mysql_connect(REATESTER_DB_HOST, REATESTER_DB_USER, REATESTER_DB_PASSWORD) or
  die("cannot connect to the database");
mysql_select_db(REATESTER_DB_NAME)
  or die("cannot select database");
  
// private
function dbkit_classify($name) {
  return preg_replace('/(?:([a-z0-9])_|^)([a-z])/e', '"$1".strtoupper("$2")', $name);
}

// private
function dbkit_tableize($name) {
  return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $name));
}

// private
function dbkit_collect_attrs($array, $attr) {
  $result = array();
  foreach ($array as $obj)
    $result[] = $obj->$attr;
  return $result;
}

// private
function dbkit_log_query($sql) {
  $fd = fopen('/tmp/query.log', 'a');
  fprintf($fd, "$sql\n");
  fclose($fd);
}

// private
// res dbkit_run_query(string sql, mixed arg...)
function dbkit_run_query($sql) {
  $args = func_get_args();
  array_shift($args);
  return dbkit_run_query_with_array($sql, $args);
}

function dbkit_run_query_with_array($sql, $args) {
  foreach($args as &$arg) {
    if (is_array($arg)) {
      if (count($arg) == 0) $arg = array(-1);
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
  dbkit_log_query($sql);
  $res = mysql_query($sql);
  if (!$res) die("database query failed: ".mysql_error());
  return $res;
}

// public
// void dbkit_execute(string sql, mixed arg...)
function dbkit_execute($sql) {
  $args = func_get_args();
  array_shift($args);
  return dbkit_execute_with_array($sql, $args);
}

function dbkit_execute_with_array($sql, $args) {
  dbkit_run_query_with_array($sql, $args);
  return mysql_affected_rows();
}

// available natively in PHP 5.3+, replacement from Phuby (http://github.com/shuber/phuby/)
if (!function_exists('get_called_class')) {
    function get_called_class() { 
        $backtrace = debug_backtrace();
        if (preg_match('/eval\(\)\'d code$/', $backtrace[1]['file'])) {
          echo '<pre>'; print_r(debug_backtrace());
            return $backtrace[3]['args'][0];
        } else {
            $lines = file($backtrace[1]['file']);
            preg_match('/([a-zA-Z0-9\_]+)::'.$backtrace[1]['function'].'/', $lines[$backtrace[1]['line']-1], $matches);
            return $matches[1];
        }
    }
}

//die(implode(", ", array(dbkit_classify("foo"), dbkit_classify("foo_bar"), dbkit_classify("foo2_bar"))));
//die(implode(", ", array(dbkit_tableize("Foo"), dbkit_tableize("FooBar"), dbkit_tableize("Foo2Bar"))));
  
class DBkitModel {
  
  var $field_errors;
  
  // public class methods
  
  // call via descendant, i.e. MyModel::get_from_request('/', 'Sorry, MyModel has been deleted.')
  function get_from_request(
        $fallback_url,  /* redirect here if the request is invalid */
        $deleted_flash_message,  /* flash message when no such object is found, null to prevent redirection */
        $request_param_name = null, $scope_var_name = null, $scope_foreign_key = null) {
    $model_name = get_called_class();
    if (is_null($request_param_name))
      $request_param_name = strtolower($model_name)."_id";

    if (empty($_REQUEST[$request_param_name])) {
      redirect($fallback_url);
      die();
    }
    
    $id = $_REQUEST[$request_param_name];
    if (is_null($scope_var_name)) {
      $result = DBkitModel::get_with_klass($model_name, "WHERE `id` = '%s'", $id);
    } else {
      if (is_null($scope_foreign_key))
         $scope_foreign_key = $scope_var_name."_id";
      if (empty($GLOBALS[$scope_var_name]))
        die("global variable $scope_var_name must be set before the call to DBkitModel::get_from_request");
      $scope = $GLOBALS[$scope_var_name];
      if (empty($scope->id))
        die("$scope_var_name->id is not defined");
      $result = DBkitModel::get_with_klass($model_name, "WHERE `id` = '%s' AND `$scope_foreign_key` = '%s'", $id, $scope->id);
    }
    
    if (!$result && !is_null($deleted_flash_message)) {
      redirect($fallback_url, $deleted_flash_message);
      die();
    }
    return $result;
  }

  // call via descendant, i.e. MyModel::query("WHERE id='%s'", 12)
  function query($sql /*, $arg... */) {
    $args = func_get_args();
    array_shift($args);
    return DBkitModel::query_with_klass_and_array(get_called_class(), $sql, $args);
  }
  
  function query_with_klass($klass, $sql /*, $arg... */) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    return DBkitModel::query_with_klass_and_array($klass, $sql, $args);
  }
  
  function query_with_klass_and_array($klass, $sql, $args) {
    if (!class_exists($klass))
      die("query_with_klass_and_array: invalid klass '$klass'");
    $std_fields = implode(", ", DBkitModel::get_fields_for_select($klass));
    $vars = get_class_vars($klass);
    if (!strstr($sql, "SELECT"))
      $sql = "SELECT ** FROM _T_ $sql";
    $sql = str_replace('_T_', "{$vars['table_name']}", $sql);
    $sql = str_replace('**', $std_fields, $sql);

    array_unshift($args, $sql);
    $r = call_user_func_array('dbkit_run_query', $args);
    $fields = array();
    $n = mysql_num_fields($r);
    for($i = 0; $i < $n; $i++)
      $fields[] = mysql_field_name($r, $i);
    $res = array();
    while($row = mysql_fetch_object($r, $klass)) {
      $row->_fetched_fields = $fields;
      if (method_exists($row, 'wakeup'))
        $row->wakeup();
      $res[] = $row;
    }
    mysql_free_result($r);
    foreach($res as &$r)
      $r->dbkit__resultset = $res;
    return $res;
  }

  // call via descendant, i.e. MyModel::query_indexed(...)
  function query_indexed($attr, $sql /*, $arg...*/) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    return DBkitModel::query_indexed_with_klass_and_array(get_called_class(), $attr, $sql, $args);
  }
  
  function query_indexed_with_klass($klass, $attr, $sql /*, $arg... */) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    array_shift($args);
    return DBkitModel::query_indexed_with_klass_and_array($klass, $attr, $sql, $args);
  }
  
  function query_indexed_with_klass_and_array($klass, $attr, $sql, $args) {
    $array = DBkitModel::query_with_klass_and_array($klass, $sql, $args);
    return index_by($array, $attr);
  }

  // call via descendant, i.e. MyModel::get(...)
  function get($sql /*, $arg... */) {
    $args = func_get_args();
    array_shift($args);
    return DBkitModel::get_with_klass_and_array(get_called_class(), $sql, $args);
    }

  function get_with_klass($klass, $sql /*, $arg... */) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    return DBkitModel::get_with_klass_and_array($klass, $sql, $args);
  }

  function get_with_klass_and_array($klass, $sql, $args) {
    $rows = DBkitModel::query_with_klass_and_array($klass, $sql, $args);
    if (count($rows) == 0)
      return false;
    else
      return $rows[0];
  }
  
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
  
  function assign($prefix, $fields = null) {
    if (!$fields)
      if (isset($this->form_fields))
        $fields = $this->form_fields;
      else
        die("assign() expects the second argument or \$this->form_fields to be defined");
    foreach ($fields as $field) {
      $v = (isset($_REQUEST[$prefix.$field]) ? $_REQUEST[$prefix.$field] : null);
      if (isset($_REQUEST[$prefix.$field."_checkbox"]))
        $v = (is_null($v) ? 0 : ($v == '0' ? 0 : 1));
      if (method_exists($this, "normalize_".$field))
        $v = call_user_method("normalize_".$field, $this, $v);
      else
        $v = trim($v);
      if (!is_null($v))
        $this->$field = $v;
    }
  }
  
  function delete() {
    if ($this->is_saved()) {
      dbkit_execute("DELETE FROM `$this->table_name` WHERE `id` = '%s'", $this->id);
      $this->delete_children();
      $this->id = null;
    }
  }
  
  function __get($name) {
    $id_name = $name."_id";
    $class_name = $name."__class";
    $fk_name = $name."__fk";
    if (isset($this->$id_name)) {
      $klass = isset($this->$class_name) ? $this->$class_name : dbkit_classify($name);
      if (!isset($this->dbkit__resultset))
        $this->$name = get($klass, "WHERE `id`='%s'", $this->$id_name);
      else {
        $items = DBkitModel::query_indexed_with_klass($klass, "id", "WHERE `id` IN %s",
            array_unique(dbkit_collect_attrs($this->dbkit__resultset, $id_name)));
        foreach($this->dbkit__resultset as $row)
          $row->$name = $items[$row->$id_name];
      }
      return $this->$name;
    } else if (isset($this->$class_name)) {
      $klass = $this->$class_name;
      $fk = isset($this->$fk_name) ? $this->$fk_name : dbkit_tableize(get_class($this))."_id";
      $this->$name = get($klass, "WHERE `$fk`='%s'", $this->id);
      return $this->$name;
    }
    $trace = debug_backtrace();
    trigger_error(
        'Undefined property via __get(): '.$name.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
        E_USER_NOTICE);
    return null;
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
    // dbkit_execute("DELETE FROM child_items WHERE parent_id = '%s'", $this->id);
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
    $this->normalize();
    
    $this->overall_errors = array();
    $this->field_errors = new stdClass;
    $this->validate();
    
    $vars = get_object_vars($this->field_errors);
    return empty($vars) && empty($this->overall_errors);
  }
  
  function normalize_file_field($code_field, $file_field, $basename, $basedir) {
    if (isset($this->$code_field) && $this->$code_field != 'keep') {
      if ($this->$code_field != '' && !$this->is_saved())
        return;

      $path = "$basedir/{$this->$file_field}";
      if (is_file($path))
        unlink($path);
        
      if ($this->$code_field == '') {
        $this->$file_field = '';
      } else {
        $ext = extension($this->$code_field);
        $file_name = "$basename.$ext";
        $path = "$basedir/$file_name";
        if (!is_dir(dirname($path)))
          mkdir(dirname($path), 0777, true);
        rename("../tmp/uploads/{$this->$code_field}", $path);
        $this->$file_field = $file_name;
        $this->$code_field = 'keep';
      }
    }
  }
  
  // private instance methods
  
  function filter_out_special_fields($fields) {
    return array_diff($fields, array("id", "created_at", "updated_at", "table_name", "form_fields", "field_errors"));
  }
  
  function get_fields_for_insert() {
    $fields = array_keys(get_class_vars(get_class($this)));
    foreach($fields as &$field)
      if (strstr($field, "__"))
        $field = false;
    $fields = array_filter($fields);
    $fields = $this->filter_out_special_fields($fields);
    return $fields;
  }
  
  function get_fields_for_update() {
    $fields = array_keys(get_class_vars(get_class($this)));
    foreach($fields as &$field)
      if (strstr($field, "__"))
        $field = false;
    $fields = array_filter($fields);
    $fields = $this->filter_out_special_fields($fields);
    return $fields;
  }
  
  // static
  function get_fields_for_select($klass) {
    $fields = array_keys(get_class_vars($klass));
    foreach($fields as &$field)
      if (strstr($field, "__"))
        $field = false;
    $fields = array_filter($fields);
    $fields = array_diff($fields, array("table_name", "form_fields", "field_errors"));
    foreach ($fields as &$field)
      if (in_array(substr($field, strlen($field)-3), array('_on', '_at')))
        $field = "UNIX_TIMESTAMP(`$field`) AS `$field`";
      else
        $field = "`$field`";
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
    dbkit_execute_with_array("INSERT INTO `$this->table_name`($names) VALUES ($values)", $args);
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
    dbkit_execute_with_array("UPDATE `$this->table_name` SET $sets WHERE `id`=%d", $args);
  }
  
}

?>
