<?php
  include '../lib/common.inc.php';
  
  loginkit_require('logged-in');
  
  $partner = loginkit_current_user();

  $tests = query('Test', "SELECT id, name FROM tests ORDER BY name");
  $host = $_SERVER['HTTP_HOST'];
  $path = dirname(dirname($_SERVER['PHP_SELF']));
  foreach ($tests as &$test) {
    $test->url = "http://$host$path/test.php?test_id=$test->id&partner_id=$partner->id";
  }
  unset($test);

  $title = "Ваши ссылки";
  render('banners.haml', array('partner' => $partner, 'tests' => $tests, 'tab' => 'banners'));
?>
