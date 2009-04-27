<?php
$uploaddir = '../tmp/uploads/';
$token = uniqid(md5(rand()), true);
$file_name = basename($_FILES['userfile']['name']);
$matches = array();
$ext = pathinfo($file_name, PATHINFO_EXTENSION);
$file_name = $token . '.' . $ext;
$uploadfile = $uploaddir . $file_name;

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
  echo $file_name;
} else {
  // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
  // Otherwise onSubmit event will not be fired
  echo "error";
}

ob_start();
if($dh = opendir($uploaddir)) {
  while(false !== ($file = readdir($dh))) {
    if( $file == '.' || $file == '..')
        continue;
    $path = $uploaddir . '/' . $file;
    if(is_file($path) && time()-filemtime($path) > 60*60*24)
      unlink($path);
  }
  closedir($dh);
}
ob_end_clean();
?>
