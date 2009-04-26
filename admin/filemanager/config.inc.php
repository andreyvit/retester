<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

// FTP access; leave empty to use local file system instead
$ftpHost = "";				// FTP server name, example: www.yourdomain.com
$ftpUser = "";				// FTP user name
$ftpPassword = "";			// FTP password
$ftpPort = 21;				// FTP port number (default is 21)
$ftpPassiveMode = true;		// use passive mode (true = yes, false = no)

// language: cs, de, en, es, et, fi, fr, it, nl, pl, pt, pt-BR, ro, ru, sk, sv
$language = "ru";

// start directory (file path, example: /home/users/gerry/htdocs/tools)
// If not in FTP mode, PHP must have at least read permission for this directory!
$startDir = "../../data";

// FileManager WEB path (example: [http://domain]/tools/filemanager)
// Only set this if FileManager doesn't view properly!
$fmWebPath = ".";

// FileManager width (pixels)
$fmWidth = 600;

// FileManager margin (pixels)
$fmMargin = 20;

// FileManager default view ("details" or "icons")
$fmView = "details";

// edit mask height (pixels)
$maskHeight = 400;

// log window height (pixels; 0 = don't view log)
$logHeight = 100;

// max. width of preview thumbnails (pixels)
$thumbMaxWidth = 200;

// max. height of preview thumbnails (pixels)
$thumbMaxHeight = 200;

// default permissions for uploaded files (octal number, example: 0755)
// NOTE: does not work correctly on Windows systems
$defaultFilePermissions = 0;

// default permissions for new directories (octal number, example: 0755)
// NOTE: does not work correctly on Windows systems
$defaultDirPermissions = 0;

// hide files with certain extensions, example: array("mp3", "txt", "jpg")
// NOTE: only use lowercase extensions; they will also work with uppercase files!
$hideFileTypes = array();

// hide system files with leading dot, example: .htaccess (true = yes, false = no)
$hideSystemFiles = true;

// hide system type (true = yes, false = no)
$hideSystemType = true;

// enable file upload (true = yes, false = no)
$enableUpload = true;

// enable file download (true = yes, false = no)
$enableDownload = true;

// enable file editing (true = yes, false = no)
$enableEdit = true;

// enable file / directory deleting (true = yes, false = no)
$enableDelete = true;

// enable file / directory renaming (true = yes, false = no)
$enableRename = true;

// enable file / directory permissions changing (true = yes, false = no)
$enablePermissions = false;

// enable directory creation (true = yes, false = no)
$enableNewDir = true;

// upload: replace spaces in filenames with underscores (true = yes, false = no)
$replSpacesUpload = false;

// download: replace spaces in filenames with underscores (true = yes, false = no)
$replSpacesDownload = false;

// upload: convert filenames to lowercase (true = yes, false = no)
$lowerCaseUpload = false;

// download: convert filenames to lowercase (true = yes, false = no)
$lowerCaseDownload = false;

// upload: backup files, i.e. don't overwrite (true = yes, false = no)
$createBackups = true;

// password protection; leave empty if you don't need it
$loginPassword = "";

?>