<?php

if (!defined('LOGINKIT_LAST_USERNAME_COOKIE'))
  define('LOGINKIT_LAST_USERNAME_COOKIE', 'loginkit-last-user-name');
if (!defined('LOGINKIT_ACCOUNT_DELETED_FLASH'))
  define('LOGINKIT_ACCOUNT_DELETED_FLASH', 'Sorry, your account has been deleted.');
if (!defined('LOGINKIT_LOGIN_URL'))
  define('LOGINKIT_LOGIN_URL', 'login.php');

/*
  $user = new User;
  $user->id = 17;
  loginkit_logged_in($user);
  
  $something = Something::get(xxx);
  loginkit_wants_to('access', $something);
  
  loginkit_u_access_denied($message);
  loginkit_u_redirect_to_login();
  loginkit_u_fetch_user($name, $model_name);
*/

# $_SESSION['user_kind'] = 'Partner'
# $_SESSION['user_id'] = '12'
# $_SESSION['user_tags'] = ['admin', 'admin+', 'partner+']

if (!function_exists('loginkit_u_access_denied')) {
  function loginkit_u_access_denied($message, $technical) {
    die("access denied<br>$message<br>$technical");
  }
}

function loginkit_start() {
  if (!session_id())
    session_start();
  if (!isset($GLOBALS['current_user']))
    if(isset($_SESSION['user_kind']) && isset($_SESSION['user_id'])) {
      $user = DBkitModel::get_with_klass($_SESSION['user_kind'], "WHERE `id` = ?", $_SESSION['user_id']);
      if (!$user)
        loginkit_log_out(LOGINKIT_ACCOUNT_DELETED_FLASH);
      $GLOBALS['current_user'] = $user;
    } else {
      $GLOBALS['current_user'] = null;
    }
  return $GLOBALS['current_user'];
}

function loginkit_log_out($flash = false) {
  $_SESSION = array();
  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
  }
  session_destroy();
  redirect($GLOBALS['_loginkit_logout_url'], $flash);
  die();
}

function loginkit_set_logout_url($url) {
  $GLOBALS['_loginkit_logout_url'] = $url;
}

function loginkit_logged_in($user, $tags = array()) {
  if (!isset($user->id))
    die("loginkit_logged_in: user object must have 'id' attribute, but ".get_class($user)." does not have one");

  $tags[] = strtolower(get_class($user));
  $tags[] = "logged-in";
  if (isset($user->loginkit__tags))
    $tags = array_merge($tags, $user->loginkit__tags);
  $_SESSION['user_kind'] = get_class($user);
  $_SESSION['user_id'] = $user->id;
  $_SESSION['user_tags'] = $tags;
  $GLOBALS['current_user'] =& $user;
}
              
function loginkit_redirect_to_login() {
  redirect(LOGINKIT_LOGIN_URL.'?url=' . urlencode($_SERVER['REQUEST_URI']));
}

function loginkit_current_user_kind() {
  if (isset($_SESSION['user_kind']))
    return $_SESSION['user_kind'];
  else
    return 'anonymous';
}

function loginkit_current_user() {
  global $current_user;
  return $current_user;
}

function loginkit_is_anonymous() {
  return !isset($_SESSION['user_kind']);
}

function loginkit_current_user_tags() {
  if (loginkit_is_anonymous())
    return array('anonymous');
  else
    return $_SESSION['user_tags'];
}

function loginkit_is($tag) {
  return in_array($tag, loginkit_current_user_tags());
}

function _loginkit_can($permission, $object = null) {
  if (loginkit_is('admin'))
    return true;

  $user_kind = loginkit_current_user_kind();
  $fperm = str_replace('-', '_', $permission);
  if (is_null($object)) {
    if (loginkit_is("can-$permission") || loginkit_is("$permission"))
      return true;
    if (function_exists("{$user_kind}_can_{$fperm}"))
      return call_user_func("{$user_kind}_can_{$fperm}");
    return false;
  }
  $object_kind = strtolower(get_class($object));
  if (!!($result = _loginkit_can("{$permission}-{$object_kind}")))
    return $result;
  else if (function_exists("{$user_kind}_can_{$fperm}_{$object_kind}"))
    return call_user_func("{$user_kind}_can_{$fperm}_{$object_kind}", $object);
  else if (function_exists("{$user_kind}_can_do_everything_with_{$object_kind}"))
    return call_user_func("{$user_kind}_can_do_everything_with_{$object_kind}", $object, $permission);
  else if (function_exists("can_{$fperm}_{$object_kind}"))
    return call_user_func("can_{$fperm}_{$object_kind}", $object);
  else if (method_exists($object, "allow_{$fperm}_by_{$user_kind}"))
    return call_user_method("allow_{$fperm}_by_{$user_kind}", $object);
  else if (method_exists($object, "allow_{$fperm}"))
    return call_user_method("allow_{$fperm}", $object);
  else
    return false;
}

function _loginkit_access_denied($message, $permission, $object) {
  $user_kind = loginkit_current_user_kind();
  if (is_null($object))
    $technical = "$user_kind cannot $permission";
  else {
    $object_kind = get_class($object);
    $technical = "$user_kind cannot $permission $object_kind";
  }
  if ($message === false)
    $message = '';
  loginkit_u_access_denied($message, $technical);
}

function loginkit_can($permission, $object = null) {
  $result = _loginkit_can($permission, $object);
  return ($result === true);
}

function loginkit_will($permission, $object) {
  $result = _loginkit_can($permission, $object);
  if ($result !== true)
    if (loginkit_is_anonymous())
      loginkit_redirect_to_login();
    else
      _loginkit_access_denied($result, $permission, $object);
}

function loginkit_require($permission) {
  loginkit_will($permission, null);
}

function loginkit_process_login($model_name,
      $default_logged_in_url = '/',
      $no_such_user_error = "Sorry, no such user exists.",
      $invalid_password_error = "Sorry, the password is incorrect.") {
  $name = '';
  $flash = '';
  
  if ($_POST) {
    if(!isset($_REQUEST['email']))
      die("invalid request: missing email");
    if(!isset($_REQUEST['password']))
      die("invalid request: missing password");
    $name = $_REQUEST['email'];
    $password = $_REQUEST['password'];
    setcookie(LOGINKIT_LAST_USERNAME_COOKIE, $name, time()+60*60*24*366 /* a year */);
    
    $user = DBkitModel::get_with_klass($model_name, "WHERE `email` = ?", $name);
    if (!$user) {
      $flash = $no_such_user_error;
    } else {
      if (empty ($user->password_salt))
        die("$model_name->password_salt must be defined");
      $password_hash = sha1($user->password_salt . $password);
      if ($password_hash != $user->password_hash) {
        $flash = $invalid_password_error;
      } else {
        $url = $default_logged_in_url;
        if (!empty($_REQUEST['url']))
          $url = $_REQUEST['url'];
        else if (method_exists($user, 'url_to_redirect_to_after_login'))
          $url = $user->url_to_redirect_to_after_login();
        else if (!empty($user->url_to_redirect_to_after_login))
          $url = $user->url_to_redirect_to_after_login;
        loginkit_logged_in($user);
        redirect($url);
        die();
      }
    }
  } else {
    if (isset($_COOKIE[LOGINKIT_LAST_USERNAME_COOKIE]))
      $name = $_COOKIE[LOGINKIT_LAST_USERNAME_COOKIE];
  }
  
  return array($name, $flash);
}

function loginkit_update_password_hash($user) {
  if (!empty($user->password) || empty($user->password_salt)) {
    $user->password_salt = random_string(10);
    $user->password_hash = sha1($user->password_salt . $user->password);
    return true;
  } else {
    return false;
  }
}

loginkit_set_logout_url('/');
loginkit_start();

?>
