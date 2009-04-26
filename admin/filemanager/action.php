<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

include_once('class/FileManager.php');

if(function_exists('session_start')) session_start();
@set_time_limit(600);

if($_REQUEST['fmMode'] != 'getFile' && $_REQUEST['fmMode'] != 'getThumbnail') {
	header('Content-type: text/html; charset=utf-8');
	header('Cache-Control: private, no-cache, must-revalidate');
	header('Expires: 0');
}

$container = $_REQUEST['fmContainer'];

if($container && isset($_SESSION[$container])) {
	$FileManager = unserialize($_SESSION[$container]);
	$FileManager->action();
}

?>