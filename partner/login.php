<?php
  include '../lib/common.inc.php';
  list($email, $flash) = loginkit_process_login('Partner', 'profile.php',
    "Извините, пользователя с таким адресом не существует.",
    "Извините, вы ввели неверный пароль.");
  render('login.haml', array('email' => $email, 'flash' => $flash), 'layout-bare');
?>
