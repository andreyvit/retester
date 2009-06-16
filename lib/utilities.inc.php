<?php

function index_by($array, $attr) {
  $result = array();
  foreach ($array as $obj)
    $result[$obj->$attr] = $obj;
  return $result;
}

function group_by($array, $attr) {
  $result = array();
  foreach ($array as $obj)
    if (isset($result[$obj->$attr]))
      $result[$obj->$attr][] = $obj;
    else
      $result[$obj->$attr] = array($obj);
  return $result;
}

function collect_attrs($array, $attr) {
  $result = array();
  foreach ($array as $obj)
    $result[] = $obj->$attr;
  return $result;
}

function extension($file_name) {
  return pathinfo($file_name, PATHINFO_EXTENSION);
}

function random_string($l, $c = "ABCDEFGHIJKLMNOPQRSTUVWXYZ", $u = true) { 
 if (!$u) for ($s = '', $i = 0, $z = strlen($c)-1; $i < $l; $x = rand(0,$z), $s .= $c{$x}, $i++); 
 else for ($i = 0, $z = strlen($c)-1, $s = $c{rand(0,$z)}, $i = 1; $i != $l; $x = rand(0,$z), $s .= $c{$x}, $s = ($s{$i} == $s{$i-1} ? substr($s,0,-1) : $s), $i=strlen($s)); 
 return $s; 
}

function validate_email($email){
  return preg_match('/^[_A-z0-9-]+((\.|\+)[_A-z0-9-]+)*@[A-z0-9-]+(\.[A-z0-9-]+)*(\.[A-z]{2,4})$/',$email);
}

function verify_email_dns($email) {
    list($name, $domain) = split('@', $email);
    return checkdnsrr($domain, 'MX');
}

function absolute_time($time) {
    $today = strtotime(date('M j, Y'));
    $reldays = ($time - $today)/86400;
    if ($reldays >= 0 && $reldays < 1)
        return date('h:i', $time);
    else if (abs($reldays) < 182)
        return date('M j h:i', $time);
    else
        return date('M j, Y',$time);
}

function time_span($seconds) {
  return floor(($seconds + 10) / 60) . "&nbsp;мин";
}

function relative_date($time) {
    $today = strtotime(date('M j, Y'));
    $reldays = ($time - $today)/86400;
    if ($reldays >= 0 && $reldays < 1) {
        return 'today';
    } else if ($reldays >= 1 && $reldays < 2) {
        return 'tomorrow';
    } else if ($reldays >= -1 && $reldays < 0) {
        return 'yesterday';
    }
    if (abs($reldays) < 7) {
        if ($reldays > 0) {
            $reldays = floor($reldays);
            return 'in ' . $reldays . ' day' . ($reldays != 1 ? 's' : '');
        } else {
            $reldays = abs(floor($reldays));
            return $reldays . ' day'  . ($reldays != 1 ? 's' : '') . ' ago';
        }
    }
    if (abs($reldays) < 182) {
        return date('l, F j', $time);
    } else {
        return date('l, F j, Y',$time);
    }
}

function js_to_string($arg) {
	return "'" . addslashes($arg) . "'";
}

function js_call_and_exit($func) {
  $args = func_get_args();
  array_shift($args);
  
  $data = ob_get_clean();
  if (strlen($data))
  	echo "if (window.console) window.console.log(" . js_to_string($data) . ");";
  
  echo "$func(" . implode(", ", array_map('js_to_string', $args)) . ");";
  die();
}
  
function end_of_last_finished_period_before($timestamp) {
  $info = getdate($timestamp);
  $weekday = ($info['wday'] == 0 ? 7 : $info['wday']);
  return mktime(23, 59, 59, $info['mon'], $info['mday'] - $weekday, $info['year']);
}

?>
