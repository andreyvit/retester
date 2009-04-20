<?php

require_once dirname(__FILE__) . "/../config/config.inc.php";

mysql_connect(REATESTER_DB_HOST, REATESTER_DB_USER, REATESTER_DB_PASSWORD) or
  die("cannot connect to the database");
mysql_select_db(REATESTER_DB_NAME)
  or die("cannot select database");
  
function query($sql /*, $arg... */) {
  $args = func_get_args();
  array_shift($args);
  foreach($args as &$arg) {
    $arg = mysql_real_escape_string("$arg");
  }
  array_unshift($args, $sql);
  $sql = call_user_func_array('sprintf', $args);
  $r = mysql_query($sql) or die("database query failed: ".mysql_error());
  $res = array();
  while($row = mysql_fetch_object($r)) {
    $res[] = $row;
  }
  mysql_free_result($r);
  return $res;
}

function get($sql /*, $arg... */) {
  $args = func_get_args();
  $rows = call_user_func_array("query", $args);
  if (count($rows) == 0)
    return false;
  else
    return $rows[0];
}

?>