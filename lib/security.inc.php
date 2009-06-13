<?php

if (!defined('LOGINKIT_ACCOUNT_DELETED_FLASH'))
  define('LOGINKIT_ACCOUNT_DELETED_FLASH', 'Sorry, your account has been deleted.');

function partner_can_access_partner($partner) {
  global $current_user;
  return $current_user->id == $partner->id;
}

loginkit_set_logout_url('/');

?>
