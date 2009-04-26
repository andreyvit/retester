<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

/**
 * This class manages directory listing entries.
 *
 * @package FileManager
 * @subpackage class
 * @author Gerd Tentler
 */
class Entry {

/* PUBLIC PROPERTIES *************************************************************************** */

	/**
	 * file name
	 *
	 * @var string
	 */
	var $name;

	/**
	 * file owner
	 *
	 * @var string
	 */
	var $owner;

	/**
	 * file group
	 *
	 * @var string
	 */
	var $group;

	/**
	 * file size
	 *
	 * @var string
	 */
	var $size;

	/**
	 * last modified
	 *
	 * @var string
	 */
	var $changed;

	/**
	 * file permissions
	 *
	 * @var string
	 */
	var $permissions;

	/**
	 * file icon
	 *
	 * @var string
	 */
	var $icon;

	/**
	 * file path
	 *
	 * @var string
	 */
	var $path;

	/**
	 * thumbnail hash
	 *
	 * @var string
	 */
	var $thumbHash;

	/**
	 * thumbnail width
	 *
	 * @var integer
	 */
	var $thumbWidth;

	/**
	 * thumbnail height
	 *
	 * @var integer
	 */
	var $thumbHeight;

	/**
	 * stores entry ID
	 *
	 * @var integer
	 */
	var $id;

/* PROTECTED PROPERTIES ************************************************************************ */

	/**
	 * holds FileManager object
	 *
	 * @var FileManager
	 */
	var $FileManager;

	/**
	 * holds listing object
	 *
	 * @var Listing
	 */
	var $Listing;

/* PUBLIC METHODS ****************************************************************************** */

	/**
	 * constructor
	 *
	 * @param Listing $Listing
	 * @return Entry
	 */
	function Entry(&$Listing) {
		$this->Listing =& $Listing;
		$this->FileManager =& $this->Listing->FileManager;
	}

	/**
	 * rename file or directory
	 *
	 * @param string $dst		new file/directory path
	 * @return boolean
	 */
	function rename($dst) {
		return $this->Listing->FileSystem->rename($this->path, $dst);
	}

	/**
	 * delete file
	 *
	 * @return boolean
	 */
	function deleteFile() {
		return $this->Listing->FileSystem->deleteFile($this->path);
	}

	/**
	 * save file data
	 *
	 * @param string $data		file data
	 * @return boolean
	 */
	function saveFile(&$data) {
		return $this->Listing->FileSystem->writeFile($this->path, $data);
	}

	/**
	 * change file permissions
	 *
	 * @param integer $mode		new mode
	 * @return boolean
	 */
	function changePerms($mode) {
		return $this->Listing->FileSystem->changePerms($this->path, $mode);
	}

	/**
	 * get file permissions
	 *
	 * @return string			permissions
	 */
	function getPerms() {
		if($this->FileManager->ftpHost) {
			return $this->permissions;
		}
		$file = $this->path;
		if(is_dir($file)) {
			$perms = 'd';
			$rwx = substr(decoct(@fileperms($file)), 2);
		}
		else {
			$perms = '-';
			$rwx = substr(decoct(@fileperms($file)), 3);
		}
		for($i = 0; $i < strlen($rwx); $i++) {
			switch($rwx[$i]) {
				case 1: $perms .= '--x'; break;
				case 2: $perms .= '-w-'; break;
				case 3: $perms .= '-wx'; break;
				case 4: $perms .= 'r--'; break;
				case 5: $perms .= 'r-x'; break;
				case 6: $perms .= 'rw-'; break;
				case 7: $perms .= 'rwx'; break;
				default: $perms .= '---';
			}
		}
		return $perms;
	}

	/**
	 * send file for download
	 *
	 * @return boolean		false on failure
	 */
	function sendFile() {
		$file = $this->getFile();
		if(is_file($file)) {
			$filename = $this->name;
			if($this->FileManager->replSpacesDownload) {
				$filename = str_replace(' ', '_', $filename);
			}
			if($this->FileManager->lowerCaseDownload) {
				$filename = strtolower($filename);
			}
			header('Content-Type: application/octetstream');
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Cache-Control: private, no-cache, must-revalidate');
			header('Expires: 0');
			readfile($file);
			exit;
		}
		return false;
	}

	/**
	 * get image path
	 *
	 * @return string		local path
	 */
	function getImagePath() {
		$file = $this->path;
		if($this->FileManager->ftpHost) {
			$cachePath = $this->FileManager->cacheDir . '/'. $this->name;
			if(!is_file($cachePath)) {
				return $this->getFile($this->FileManager->cacheDir);
			}
			return $cachePath;
		}
		return $file;
	}

	/**
	 * get thumbnail size and type
	 *
	 * @param integer $maxWidth			optional: max. width
	 * @param integer $maxHeight		optional: max. height
	 * @return array					thumbnail size and type
	 */
	function getThumbSize($maxWidth = 0, $maxHeight = 0) {
		$file = $this->getImagePath();
		list($width, $height, $type) = @getimagesize($file);

		if($type == 1 || $type == 2 || $type == 3) {
			if($maxWidth && $width > $maxWidth) {
				$perc = $maxWidth / $width;
				$width = round($width * $perc);
				$height = round($height * $perc);
			}
			if($maxHeight && $height > $maxHeight) {
				$perc = $maxHeight / $height;
				$width = round($width * $perc);
				$height = round($height * $perc);
			}
		}
		return array($width, $height, $type);
	}

	/**
	 * get file path; loads file from FTP server if necessary
	 *
	 * @param string $dstDir	optional: destination directory
	 * @return string			file path
	 */
	function getFile($dstDir = '') {
		$file = $this->path;
		if(!$dstDir) $dstDir = $this->FileManager->tmpDir;
		$dstPath = $dstDir . '/' . $this->name;
		return $this->Listing->FileSystem->getFile($file, $dstPath);
	}

/* PROTECTED METHODS *************************************************************************** */

	/**
	 * get icon action
	 *
	 * @return array	link, tooltip
	 */
	function getIconAction() {
		global $msg;

		$cont = $this->FileManager->container;
		$url = $this->FileManager->fmWebPath . "/action.php?fmContainer=$cont";

		switch($this->icon) {

			case 'cdup':
				if($this->Listing->searchString != '') {
					$action = "fmCall('$url&fmMode=search')";
					$tooltip = $msg['cmdGoBack'];
				}
				else {
					$action = "fmCall('$url&fmMode=parent&fmObject=$this->id')";
					$tooltip = $msg['cmdParentDir'];
				}
				break;

			case 'dir':
				$action = "fmCall('$url&fmMode=open&fmObject=$this->id')";
				$tooltip = $msg['cmdChangeDir'];
				break;

			default:
				if($this->FileManager->enableDownload) {
					$action = "fmGetFile('$url&fmMode=getFile&fmObject=$this->id')";
					$tooltip = $msg['cmdGetFile'];
				}
				else $action = $tooltip = '';
		}
		return array($action, $tooltip);
	}

	/**
	 * view action icons
	 */
	function viewActionIcons() {
		global $msg;

		$url = $this->FileManager->fmWebPath . '/action.php?fmContainer=' . $this->FileManager->container;

		if($this->FileManager->enableRename) {
			$icon = $this->FileManager->fmWebPath . '/icons/rename.gif';
			$tooltip = $msg['cmdRename'];
			$name = addslashes($this->name);
			$title = addslashes($msg['cmdRename']) . ': ' . $name;
			$onClick = "fmOpenDialog('$url', 'fmRename', '$title', '$this->id', '$name')";
			$style = 'cursor:pointer';
		}
		else {
			$icon = $this->FileManager->fmWebPath . '/icons/rename_x.gif';
			$tooltip = $style = '';
			$error = addslashes($msg['cmdRename'] . ': ' . $msg['errDisabled']);
			$onClick = "fmOpenDialog('', 'fmError', '$error')";
		}
		print "<img src=\"$icon\" border=\"0\" width=\"10\" height=\"10\" ";
		print "alt=\"$tooltip\" title=\"$tooltip\" style=\"$style\" onClick=\"$onClick\"/>\n";

		if($this->FileManager->enablePermissions) {
			$icon = $this->FileManager->fmWebPath . '/icons/permissions.gif';
			$tooltip = $msg['cmdChangePerm'];
			$name = addslashes($this->name);
			$title = addslashes($msg['cmdChangePerm']) . ': ' . $name;
			$onClick = "fmOpenDialog('$url', 'fmPerm', '$title', '$this->id', '$name', '$this->permissions')";
			$style = 'cursor:pointer';
		}
		else {
			$icon = $this->FileManager->fmWebPath . '/icons/permissions_x.gif';
			$tooltip = $style = '';
			$error = addslashes($msg['cmdChangePerm'] . ': ' . $msg['errDisabled']);
			$onClick = "fmOpenDialog('', 'fmError', '$error')";
		}
		print "<img src=\"$icon\" border=\"0\" width=\"10\" height=\"10\" ";
		print "alt=\"$tooltip\" title=\"$tooltip\" style=\"$style\" onClick=\"$onClick\"/>\n";

		if($this->FileManager->enableDelete) {
			if($this->icon == 'dir') {
				$mode = 'removeDir';
				$confirm = addslashes($msg['msgRemoveDir']);
			}
			else {
				$mode = 'removeFile';
				$confirm = addslashes($msg['msgDeleteFile']);
			}
			$icon = $this->FileManager->fmWebPath . '/icons/delete.gif';
			$tooltip = $msg['cmdDelete'];
			$name = addslashes($this->name);
			$title = addslashes($msg['cmdDelete']) . ': ' . $name;
			$onClick = "fmOpenDialog('$url', 'fmDelete', ['$title', '$confirm'], '$this->id')";
			$style = 'cursor:pointer';
		}
		else {
			$icon = $this->FileManager->fmWebPath . '/icons/delete_x.gif';
			$tooltip = $style = '';
			$error = addslashes($msg['cmdDelete'] . ': ' . $msg['errDisabled']);
			$onClick = "fmOpenDialog('', 'fmError', '$error')";
		}
		print "<img src=\"$icon\" border=\"0\" width=\"10\" height=\"10\" ";
		print "alt=\"$tooltip\" title=\"$tooltip\" style=\"$style\" onClick=\"$onClick\"/>\n";

		if($this->icon == 'text') {
			if($this->FileManager->enableEdit) {
				$icon = $this->FileManager->fmWebPath . '/icons/edit.gif';
				$tooltip = $msg['cmdEdit'];
				$onClick = "fmCall('$url&fmMode=edit&fmObject=$this->id')";
				$style = 'cursor:pointer';
			}
			else {
				$icon = $this->FileManager->fmWebPath . '/icons/edit_x.gif';
				$tooltip = $style = '';
				$error = addslashes($msg['cmdEdit'] . ': ' . $msg['errDisabled']);
				$onClick = "fmOpenDialog('', 'fmError', '$error')";
			}
		}
		else {
			if($this->FileManager->fmView == 'details') {
				$icon = $this->FileManager->fmWebPath . '/icons/blank.gif';
			}
			else $icon = '';
		}

		if($icon) {
			print "<img src=\"$icon\" border=\"0\" width=\"10\" height=\"10\" ";
			print "alt=\"$tooltip\" title=\"$tooltip\" style=\"$style\" onClick=\"$onClick\"/>\n";
		}
	}
}

?>