<?php
  include '../lib/common.inc.php';
  
  loginkit_require('logged-in');
  
  $partner = loginkit_current_user();
  if ($_POST) {
    $partner->assign('');
    $partner->assign('', array('password', 'password_confirmation'));
    if ($partner->is_valid()) {
      $more = (loginkit_update_password_hash($partner) ? "Пароль изменен." : "");
      $partner->put();
      redirect("profile.php", "+Изменения сохранены. $more");
    }
  }

  $title = "Ваш профиль";
  render('partner.haml', array('partner' => $partner, 'tab' => 'profile'));
?>
