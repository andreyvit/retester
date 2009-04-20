<?php

require_once 'lib/dbconnect.inc.php';

$r = mysql_query("SHOW TABLES")
  or die("mysql_query failed: " . mysql_error());
echo '<pre>';
while(false !== ($row = mysql_fetch_assoc($r))) {
  echo htmlspecialchars(print_r($row, true)), "\n";
}
?>
