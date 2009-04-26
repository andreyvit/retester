<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

include_once('ListingDetail.php');
include_once('ListingIcon.php');
include_once('Image.php');
include_once('Editor.php');

/**
 * This is the main class.
 */
class FileManager {

/* PUBLIC PROPERTIES *************************************************************************** */

	/* configuration variables; will be filled with content from config file */

	var $ftpHost;
	var $ftpUser;
	var $ftpPassword;
	var $ftpPort;
	var $ftpPassiveMode;
	var $language;
	var $startDir;
	var $fmWebPath;
	var $fmWidth;
	var $fmMargin;
	var $fmView;
	var $maskHeight;
	var $logHeight;
	var $thumbMaxWidth;
	var $thumbMaxHeight;
	var $defaultFilePermissions;
	var $defaultDirPermissions;
	var $hideFileTypes;
	var $hideSystemFiles;
	var $hideSystemType;
	var $enableUpload;
	var $enableDownload;
	var $enableEdit;
	var $enableDelete;
	var $enableRename;
	var $enablePermissions;
	var $enableNewDir;
	var $replSpacesUpload;
	var $replSpacesDownload;
	var $lowerCaseUpload;
	var $lowerCaseDownload;
	var $createBackups;
	var $loginPassword;

	/**
	 * path to temporary directory
	 *
	 * @var string
	 */
	var $tmpDir;

	/**
	 * path to cache directory
	 *
	 * @var string
	 */
	var $cacheDir;

	/**
	 * HTML container name
	 *
	 * @var string
	 */
	var $container;

/* PRIVATE PROPERTIES ************************************************************************** */

	/**
	 * file manager directory path (for includes)
	 *
	 * @var string
	 */
	var $incPath;

	/**
	 * error messages
	 *
	 * @var string
	 */
	var $error;

	/**
	 * holds listing object
	 *
	 * @var Listing
	 */
	var $Listing;

	/**
	 * HTML container name
	 *
	 * @var string
	 */
	var $listCont;

	/**
	 * HTML container name
	 *
	 * @var string
	 */
	var $logCont;

	/**
	 * user access
	 *
	 * @var boolean
	 */
	var $access;

/* PUBLIC METHODS ****************************************************************************** */

	/**
	 * constructor
	 *
	 * @param string $startDir		optional: directory path
	 * @return FileManager
	 */
	function FileManager($startDir = '') {
		$this->incPath = str_replace('\\', '/', realpath(dirname(__FILE__) . '/..'));
		$this->tmpDir = $this->incPath . '/tmp';
		$this->cacheDir = $this->incPath . '/cache';

		$this->initFromConfig();
		if($startDir != '') $this->startDir = $startDir;

		if($this->fmWebPath == '') {
			$this->fmWebPath = ereg_replace('^' . $_SERVER['DOCUMENT_ROOT'], '', $this->incPath);
			if($this->fmWebPath == $this->incPath) {
				$ld = basename($_SERVER['DOCUMENT_ROOT']);
				$this->fmWebPath = substr($this->incPath, strpos($this->incPath, $ld) + strlen($ld));
			}
		}
	}

	/**
	 * initialization from config file
	 */
	function initFromConfig() {
		include($this->incPath . '/config.inc.php');
		$this->ftpHost = $ftpHost;
		$this->ftpUser = $ftpUser;
		$this->ftpPassword = $ftpPassword;
		$this->ftpPort = $ftpPort ? $ftpPort : 21;
		$this->ftpPassiveMode = $ftpPassiveMode;
		$this->language = $language;
		$this->startDir = $startDir;
		$this->fmWebPath = $fmWebPath;
		$this->fmWidth = $fmWidth;
		$this->fmMargin = $fmMargin;
		$this->fmView = $fmView;
		$this->maskHeight = $maskHeight;
		$this->logHeight = $logHeight;
		$this->thumbMaxWidth = $thumbMaxWidth;
		$this->thumbMaxHeight = $thumbMaxHeight;
		$this->defaultFilePermissions = $defaultFilePermissions;
		$this->defaultDirPermissions = $defaultDirPermissions;
		$this->hideFileTypes = $hideFileTypes;
		$this->hideSystemFiles = $hideSystemFiles;
		$this->hideSystemType = $hideSystemType;
		$this->enableUpload = $enableUpload;
		$this->enableDownload = $enableDownload;
		$this->enableEdit = $enableEdit;
		$this->enableDelete = $enableDelete;
		$this->enableRename = $enableRename;
		$this->enablePermissions = $enablePermissions;
		$this->enableNewDir = $enableNewDir;
		$this->replSpacesUpload = $replSpacesUpload;
		$this->replSpacesDownload = $replSpacesDownload;
		$this->lowerCaseUpload = $lowerCaseUpload;
		$this->lowerCaseDownload = $lowerCaseDownload;
		$this->createBackups = $createBackups;
		$this->loginPassword = $loginPassword;
	}

	/**
	 * create file manager
	 */
	function create() {
		global $fmCnt, $msg;

		if(!$fmCnt) $fmCnt = 1;
		if($fmCnt == 1) {
			$this->getLanguageFile();
			$fmWebPath = $this->fmWebPath;
			include_once($this->incPath . '/template.inc.php');
		}

		if($this->startDir != '') {
			if(!$this->ftpHost) $this->startDir = realpath($this->startDir);
			$this->startDir = str_replace('\\', '/', $this->startDir);
			if($this->ftpHost) $this->startDir = preg_replace('%/*\.+%', '', $this->startDir);
		}

		if($this->loginPassword == '') $this->access = true;

		$this->container = 'fmCont' . $fmCnt;
		$this->listCont = 'fmList' . $fmCnt;
		$this->logCont = 'fmLog' . $fmCnt;
		$this->save();

		$this->viewHeader();
		$this->viewFooter();

		$url = $this->fmWebPath . '/action.php?fmContainer=' . $this->container;
		print "<script type=\"text/javascript\">\n";
		print "setTimeout(\"fmCall('$url&fmMode=refresh')\", 250);\n";
		print "</script>\n";
		$fmCnt++;
	}

	/**
	 * return listing object
	 *
	 * @return Listing
	 */
	function &getListing() {
		if(!$this->Listing) switch($this->fmView) {
			case 'details':	$this->Listing =& new ListingDetail($this); break;
			case 'icons':	$this->Listing =& new ListingIcon($this); break;
			default:		$this->error = 'Wrong view type: ' . $this->fmView;
		}
		return $this->Listing;
	}

	/**
	 * get language file
	 */
	function getLanguageFile() {
		global $msg;

		if(!isset($this->language)) $this->language = 'en';
		include_once($this->incPath . '/languages/lang_' . $this->language . '.inc');
	}

	/**
	 * perform requested action
	 */
	function action() {
		global $msg;

		$fmMode = $_REQUEST['fmMode'];
		$fmName = $_REQUEST['fmName'];

		$this->getLanguageFile();

		if(!$this->ftpHost && $this->startDir == '') {
			$this->error = "SECURITY ALERT:<br>Please set a start directory or an FTP server!";
			$log = $this->error;
		}
		else if($fmMode == 'login' && $fmName == $this->loginPassword) {
			$this->access = true;
			$fmMode = 'refresh';
		}
		else if($this->loginPassword != '' && !$this->access) {
			$this->viewLogin();
		}

		if(!$this->error && $this->access) {
			$fmObject = $_REQUEST['fmObject'];
			$fmPerms = $_REQUEST['fmPerms'];

			$this->getListing();

			switch($fmMode) {
				case 'sort':
					list($this->Listing->sortField, $this->Listing->sortOrder) = explode(',', $fmName);
					$this->Listing->view();
					break;

				case 'open':
					if($fmObject != '') {
						if($Entry =& $this->Listing->getEntry($fmObject)) {
							if($Entry->icon == 'dir') {
								$this->Listing->prevDir = $this->Listing->curDir;
								$this->Listing->curDir = $Entry->path;
								$this->Listing->searchString = '';
								$this->cleanUp($this->cacheDir);
							}
						}
					}
					if($this->Listing->prevDir !== $this->Listing->curDir) {
						$this->Listing->refresh();
					}
					else $this->Listing->view();
					break;

				case 'getFile':
					if($this->enableDownload && $fmObject != '') {
						if($Entry =& $this->Listing->getEntry($fmObject)) {
							if(!$Entry->sendFile()) {
								$this->error = $msg['errOpen'] . ": $Entry->name";
							}
						}
					}
					if(!$this->error) print 'READY';
					break;

				case 'getThumbnail':
					if($fmObject != '') {
						if($Entry =& $this->Listing->getEntry($fmObject)) {
							$Image = new Image($Entry->getImagePath(), $_REQUEST['width'], $_REQUEST['height']);
							$Image->view();
						}
					}
					break;

				case 'parent':
					$this->Listing->prevDir = $this->Listing->curDir;
					$this->Listing->curDir = ereg_replace('/[^/]+$', '', $this->Listing->curDir);
					$this->Listing->searchString = '';
					$this->cleanUp($this->cacheDir);
					$this->Listing->refresh();
					break;

				case 'rename':
					if($this->enableRename && $fmName != '' && $fmObject != '') {
						if($Entry =& $this->Listing->getEntry($fmObject)) {
							$path = dirname($Entry->path);
							if(get_magic_quotes_gpc()) $fmName = stripslashes($fmName);
							$fmName = basename($fmName);

							if(!$Entry->rename("$path/$fmName")) {
								$this->error = $msg['errRename'] . ": $Entry->name &raquo; $fmName";
							}
						}
					}
					if(!$this->error) $this->Listing->refresh();
					break;

				case 'delete':
					if($this->enableDelete && $fmObject != '') {
						if($Entry =& $this->Listing->getEntry($fmObject)) {
							if($Entry->icon == 'dir') {
								if(!$this->Listing->remDir($Entry->path)) {
									$this->error = $msg['errDelete'] . ": $Entry->name";
								}
							}
							else if(!$Entry->deleteFile()) {
								$this->error = $msg['errDelete'] . ": $Entry->name";
							}
						}
					}
					if(!$this->error) $this->Listing->refresh();
					break;

				case 'newDir':
					if($this->enableNewDir) {
						if($fmName != '') {
							if(get_magic_quotes_gpc()) $fmName = stripslashes($fmName);
							$fmName = str_replace('\\', '/', $fmName);
							$dirs = explode('/', $fmName);
							$dir = '';

							for($i = 0; $i < count($dirs); $i++) {
								if($dirs[$i] != '') {
									if($dir != '') $dir .= '/';
									$dir .= $dirs[$i];
									$curDir = $this->Listing->curDir;

									if(!$this->Listing->mkDir("$curDir/$dir")) {
										$this->error = $msg['errDirNew'] . ": $dir";
										break;
									}
									else if($this->defaultDirPermissions) {
										$Entry =& $this->Listing->getEntryByName($dir);
										if(!$Entry || !$Entry->changePerms($this->defaultDirPermissions)) {
											$this->error = $msg['errPermChange'] . ": $dir";
											break;
										}
									}
								}
							}
						}
					}
					$this->Listing->refresh();
					break;

				case 'newFile':
					if($this->enableUpload) {
						$fmFile = $_FILES['fmFile'];
						$fmReplSpaces = $_REQUEST['fmReplSpaces'];
						$fmLowerCase = $_REQUEST['fmLowerCase'];
						$errors = array();

						if(is_array($fmFile)) {
							for($i = 0; $i < count($fmFile['size']); $i++) {
								$newFile = $fmFile['name'][$i];

								if($fmFile['size'][$i]) {
									if($this->hideSystemFiles && $newFile[0] == '.') {
										$errors[] = $msg['errAccess'] . ": $newFile";
									}
									else {
										if($this->replSpacesUpload || $fmReplSpaces) {
											$newFile = str_replace(' ', '_', $newFile);
										}

										if($this->lowerCaseUpload || $fmLowerCase) {
											$newFile = strtolower($newFile);
										}

										if(!$this->Listing->upload($fmFile['tmp_name'][$i], $newFile)) {
											$errors[] = $msg['errSave'] . ": $newFile";
										}
										else if($this->defaultFilePermissions) {
											$Entry =& $this->Listing->getEntryByName($newFile);
											if(!$Entry || !$Entry->changePerms($this->defaultFilePermissions)) {
												$errors[] = $msg['errPermChange'] . ": $newFile";
											}
										}
									}
								}
								else if($newFile != '') {
									$errors[] = $msg['error'] . ": $newFile = 0 B";
									$maxFileSize = ini_get('upload_max_filesize');
									$postMaxSize = ini_get('post_max_size');
									$info = "PHP settings: upload_max_filesize = $maxFileSize, ";
									$info .= "post_max_size = $postMaxSize";
									$error = "Could not upload $newFile ($info)";
									$this->Listing->FileSystem->addMsg($error, 'error');
								}
							}
						}
						$this->Listing->refresh();
						if(count($errors) > 0) $this->error .= join('<br/>', $errors);
					}
					else $this->Listing->view();
					break;

				case 'refresh':
					$this->Listing->refresh();
					break;

				case 'permissions':
					if($this->enablePermissions && is_array($fmPerms) && $fmObject != '') {
						if($Entry =& $this->Listing->getEntry($fmObject)) {
							$mode = '';
							for($i = 0; $i < 9; $i++) {
								$mode .= $fmPerms[$i] ? 1 : 0;
							}
							if(!$Entry->changePerms(bindec($mode))) {
								$this->error = $msg['errPermChange'] . ": $Entry->name";
							}
						}
					}
					if(!$this->error) $this->Listing->refresh();
					break;

				case 'edit':
					if($this->enableEdit && $fmObject != '') {
						if($Entry =& $this->Listing->getEntry($fmObject)) {
							$fmText = $_POST['fmText'];
							if($fmText != '') {
								if(!$Entry->saveFile($fmText)) {
									$this->error = $msg['errSave'] . ": $Entry->name";
								}
								else $this->Listing->refresh();
							}
							else {
								$Editor = new Editor($this);
								$Editor->view($Entry);
							}
						}
					}
					break;

				case 'search':
					$this->Listing->performSearch($fmName);
					break;

				case 'switchView':
					$this->Listing =& $this->Listing->switchView();
					$this->Listing->refresh();
					break;

				default: if(!$this->error) $this->Listing->view();
			}
			if($this->ftpHost) $this->Listing->FileSystem->ftpClose();
			$this->cleanUp();
			$log = $this->Listing->FileSystem->getMessages();
		}

		if($this->error != '') {
			print '{{fmERROR}}' . $this->error . '{{/fmERROR}}';
			$this->error = '';
		}
		print '{{fmLOG}}' . $log . '{{/fmLOG}}';
		$this->save();
	}

/* PRIVATE METHODS ***************************************************************************** */

	/**
	 * view header
	 */
	function viewHeader() {
		print "<div id=\"$this->container\" class=\"fmTH1\" style=\"position:relative; ";
		print "width:{$this->fmWidth}px; margin:{$this->fmMargin}px; padding:1px\">\n";
		print "<div id=\"$this->listCont\" class=\"fmTH1\">\n";
		print "<div class=\"fmTH2\" style=\"height:100px\">&nbsp;</div>\n";
		print "</div>\n";

		if($this->logHeight > 0) {
			$logWidth = $this->fmWidth - 8;
			print "<div id=\"$this->logCont\" class=\"fmTH2\" ";
			print "style=\"width:{$logWidth}px; height:{$this->logHeight}px; ";
			print "margin-top:1px; padding:4px; text-align:left; overflow:auto\">\n";
			print "</div>\n";
		}
	}

	/**
	 * view footer
	 */
	function viewFooter() {
		print "</div>\n";
	}

	/**
	 * view login form
	 */
	function viewLogin() {
		global $msg;

		$url = $this->fmWebPath . '/action.php?fmContainer=' . $this->container;
		$action = "javascript:fmCall('$url', '{$this->container}Login')";

		print "<form name=\"{$this->container}Login\" action=\"$action\" class=\"fmForm\" method=\"post\">\n";
		print "<input type=\"hidden\" name=\"fmMode\" value=\"login\"/>\n";
		print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" height=\"100\"><tr>\n";
		print "<td class=\"fmTH1\" style=\"padding:4px\" align=\"left\" nowrap=\"nowrap\">{$msg['cmdLogin']}</td>\n";
		print "</tr><tr>\n";
		print "<td class=\"fmTH3\" align=\"center\" style=\"padding:4px\">\n";
		print "<input type=\"password\" name=\"fmName\" size=\"20\" maxlength=\"60\" class=\"fmField\"/><br/>\n";
		print "<input type=\"submit\" class=\"fmButton\" value=\"{$msg['cmdLogin']}\"/>\n";
		print "</td>\n";
		print "</tr></table>\n";
		print "</form>\n";
	}

	/**
	 * save FileManager object
	 */
	function save() {
		$_SESSION[$this->container] = serialize($this);
	}

	/**
	 * delete files from temporary directory
	 */
	function cleanUp($dir = '') {
		if(!$dir) $dir = $this->tmpDir;
		if($dp = @opendir($dir)) {
			while(($file = @readdir($dp)) !== false) {
				if($file != '.' && $file != '..') @unlink("$dir/$file");
			}
			@closedir($dp);
		}
	}
}

?>