<?php
  include '../lib/common.inc.php';
  
  $partner = new Partner;
  if ($_POST) {
    $partner->assign('');
    $partner->assign('', array('password', 'password_confirmation'));
    if ($partner->is_valid()) {
      loginkit_update_password_hash($partner);
      $partner->put();
      redirect("/parner/accounts/current/edit", "+Ваш аккаунт успешно создан.");
    }
  }

  $title = "Регистрация партнера";
  render('partner.haml', array('partner' => $partner, 'tab' => 'profile'));
?>
