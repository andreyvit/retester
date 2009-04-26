<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

/**
 * This class contains file system methods.
 *
 * @package FileManager
 * @subpackage class
 * @author Gerd Tentler
 */
class FileSystem {

/* PRIVATE PROPERTIES ************************************************************************** */

	/**
	 * log messages
	 *
	 * @var string
	 */
	var $messages;

	/**
	 * holds FTP stream
	 *
	 * @var resource
	 */
	var $ftp;

	/**
	 * FTP server
	 *
	 * @var string
	 */
	var $host;

	/**
	 * FTP port number
	 *
	 * @var integer
	 */
	var $port;

	/**
	 * OS type
	 *
	 * @var string
	 */
	var $sysType;

	/**
	 * holds FileManager object
	 *
	 * @var FileManager
	 */
	var $FileManager;

/* PUBLIC METHODS ****************************************************************************** */

	/**
	 * constructor
	 *
	 * @param FileManager $FileManager
	 * @return FileSystem
	 */
	function FileSystem(&$FileManager) {
		$this->FileManager =& $FileManager;
	}

	/**
	 * connect to FTP server
	 *
	 * @param string $host			server
	 * @param integer $port			optional: port number
	 * @param integer $timeout		optional: timeout in seconds
	 * @return boolean
	 */
	function ftpConnect($host, $port = 21, $timeout = 30) {
		$this->host = $host;
		$this->port = $port;

		if($this->ftp = @ftp_connect($host, $port, $timeout)) {
			$this->addMsg("Connected to $host:$port");
			return true;
		}
		$this->addMsg("Could not connect to $host:$port", 'error');
		return false;
	}

	/**
	 * close FTP connection
	 *
	 * @return boolean
	 */
	function ftpClose() {
		if(!$this->ftp) return false;
		if(@ftp_quit($this->ftp)) {
			$this->addMsg("Closed connection to $this->host:$this->port");
			$this->ftp = null;
			return true;
		}
		$this->addMsg("Could not close connection to $this->host:$this->port", 'error');
		return false;
	}

	/**
	 * FTP login
	 *
	 * @param string $user			user name
	 * @param string $password		password
	 * @return boolean
	 */
	function ftpLogin($user, $password) {
		if(!$this->ftp) return false;
		if(@ftp_login($this->ftp, $user, $password)) {
			$this->addMsg("User $user logged in");
			return true;
		}
		$this->addMsg("User $user could not log in", 'error');
		return false;
	}

	/**
	 * switch to passive mode
	 *
	 * @param boolean $mode			true = passive, false = active
	 * @return boolean
	 */
	function ftpPassiveMode($mode) {
		if(!$this->ftp) return false;
		if(@ftp_pasv($this->ftp, $mode)) {
			if($mode) $this->addMsg('Switched to passive mode');
			else $this->addMsg('Switched to active mode');
			return true;
		}
		$this->addMsg('Could not switch passive mode', 'error');
		return false;
	}

	/**
	 * get OS type
	 *
	 * @return string
	 */
	function getSystemType() {
		if($this->sysType) return $this->sysType;

		if($this->checkFtp()) {
			$this->sysType = @ftp_systype($this->ftp);
		}
		else if(!$this->FileManager->ftpHost) {
			$this->sysType = function_exists('php_uname') ? php_uname() : PHP_OS;
		}

		if($this->sysType) {
			if(!$this->FileManager->hideSystemType) {
				$this->addMsg("System type is $this->sysType");
			}
			return $this->sysType;
		}
		$this->addMsg('Could not get system type', 'error');
		return false;
	}

	/**
	 * change directory
	 *
	 * @param string $path		directory path
	 * @return boolean
	 */
	function changeDir($path) {
		if($this->checkFtp()) $ok = @ftp_chdir($this->ftp, $path);
		else $ok = @chdir($path);
		$path = $this->checkPath($path);

		if($ok) {
			$this->addMsg("Changed directory to $path");
			return true;
		}
		$this->addMsg("Could not change directory to $path", 'error');
		return false;
	}

	/**
	 * create directory
	 *
	 * @param string $dir		directory path
	 * @return boolean
	 */
	function makeDir($dir) {
		if($this->checkFtp()) {
			$ok = @ftp_mkdir($this->ftp, $dir);
			/* workaround for PHP bug */
			if(!$ok) $ok = @ftp_nlist($this->ftp, $dir);
		}
		else $ok = @mkdir($dir, 0755);
		$dir = $this->checkPath($dir);

		if($ok) {
			$this->addMsg("Created directory $dir");
			return true;
		}
		$this->addMsg("Could not create directory $dir", 'error');
		return false;
	}

	/**
	 * remove directory
	 *
	 * @param string $dir		directory path
	 * @return boolean
	 */
	function removeDir($dir) {
		if($this->checkFtp()) $ok = @ftp_rmdir($this->ftp, $dir);
		else $ok = @rmdir($dir);
		$dir = $this->checkPath($dir);

		if($ok) {
			$this->addMsg("Removed directory $dir");
			return true;
		}
		$this->addMsg("Could not remove directory $dir - not empty?", 'error');
		return false;
	}

	/**
	 * read directory
	 *
	 * @param string $dir		directory path
	 * @return array			entries
	 */
	function readDir($dir) {
		if($this->checkFtp()) {
			if($wd = @ftp_pwd($this->ftp)) {
				@ftp_chdir($this->ftp, $dir);
				$list = @ftp_rawlist($this->ftp, '.');
				@ftp_chdir($this->ftp, $wd);
			}
			else $list = @ftp_rawlist($this->ftp, $dir);
		}
		else if($dp = @opendir($dir)) {
			$list = array();
			while(($file = @readdir($dp)) !== false) {
				$list[] = $dir . '/' . $file;
			}
			@closedir($dp);
		}
		$dir = $this->checkPath($dir);
		if(!$dir) $dir = '/';

		if(is_array($list)) {
			$this->addMsg("Read directory $dir");
			return $list;
		}
		$this->addMsg("Could not read directory $dir", 'error');
		return false;
	}

	/**
	 * change permissions
	 *
	 * @param string $file		file / directory name
	 * @param integer $mode		permissions
	 * @return boolean
	 */
	function changePerms($file, $mode) {
		if($this->checkFtp()) {
			if(!function_exists('ftp_chmod')) {
				function ftp_chmod($ftp, $mode, $file) {
					return @ftp_site($ftp, sprintf('CHMOD %o %s', $mode, $file));
				}
			}
			$ok = @ftp_chmod($this->ftp, $mode, $file);
		}
		else $ok = @chmod($file, $mode);
		$file = $this->checkPath($file);

		if($ok) {
			$this->addMsg(sprintf('Changed permissions of %s to %o', $file, $mode));
			return true;
		}
		$this->addMsg(sprintf('Could not change permissions of %s to %o', $file, $mode), 'error');
		return false;
	}

	/**
	 * get file from FTP server
	 *
	 * @param string $src		source path (remote)
	 * @param string $dst		destination path (local)
	 * @return string			local file path
	 */
	function getFile($src, $dst) {
		if($this->checkFtp()) {
			$ok = @ftp_get($this->ftp, $dst, $src, FTP_BINARY);
		}
		else {
			$dst = $src;
			$ok = is_file($src);
		}
		$src = $this->checkPath($src);

		if($ok) {
			$this->addMsg("Got file $src");
			return $dst;
		}
		$this->addMsg("Could not get file $src", 'error');
		return false;
	}

	/**
	 * upload file
	 *
	 * @param string $src		source path (local / temp)
	 * @param string $dst		destination path (remote / target dir)
	 * @return boolean
	 */
	function putFile($src, $dst) {
		if($this->checkFtp()) $ok = @ftp_put($this->ftp, $dst, $src, FTP_BINARY);
		else $ok = @move_uploaded_file($src, $dst);
		$dst = $this->checkPath($dst);

		if($ok) {
			$this->addMsg("Saved file $dst");
			return true;
		}
		$this->addMsg("Could not save file $dst", 'error');
		return false;
	}

	/**
	 * delete file
	 *
	 * @param string $file		file path
	 * @return boolean
	 */
	function deleteFile($file) {
		if($this->checkFtp()) $ok = @ftp_delete($this->ftp, $file);
		else $ok = @unlink($file);
		$file = $this->checkPath($file);

		if($ok) {
			$this->addMsg("Deleted file $file");
			return true;
		}
		$this->addMsg("Could not delete file $file", 'error');
		return false;
	}

	/**
	 * write file data
	 *
	 * @param string $path		file path
	 * @param string $data		file data
	 * @return boolean
	 */
	function writeFile($path, &$data) {
		if($data == '') return false;
		if(get_magic_quotes_gpc()) $data = stripslashes($data);
		$ok = false;

		if($this->checkFtp()) {
			$srcPath = $this->FileManager->tmpDir . '/' . basename($path);

			if($fp = @fopen($srcPath, 'wt')) {
				$ok = @fwrite($fp, $data);
				@fclose($fp);

				if($ok && is_file($srcPath)) {
					return $this->putFile($srcPath, $path);
				}
			}
		}
		else if($fp = @fopen($path, 'wt')) {
			$ok = @fwrite($fp, $data);
			@fclose($fp);
		}
		$path = $this->checkPath($path);

		if($ok) {
			$this->addMsg("Saved file $path");
			return true;
		}
		$this->addMsg("Could not save file $path", 'error');
		return false;
	}

	/**
	 * rename file
	 *
	 * @param string $src		source path
	 * @param string $dst		destination path
	 * @return boolean
	 */
	function rename($src, $dst) {
		if($this->checkFtp()) $ok = @ftp_rename($this->ftp, $src, $dst);
		else $ok = @rename($src, $dst);
		$src = $this->checkPath($src);
		$dst = $this->checkPath($dst);

		if($ok) {
			$this->addMsg("Renamed $src => $dst");
			return true;
		}
		$this->addMsg("Could not rename $src => $dst", 'error');
		return false;
	}

	/**
	 * get log messages
	 *
	 * @return string
	 */
	function getMessages() {
		if($this->messages == '') return '';
		$time = '<div class="fmLogTime">' . date('Y-m-d H:i:s') . "</div>\n";
		$log = $time . '<div class="fmLog">' . $this->messages . "</div>\n";
		$this->messages = '';
		return $log;
	}

	/**
	 * add log message
	 *
	 * @param string $text		message text
	 * @param string $type		optional: message type
	 */
	function addMsg($text, $type = '') {
		switch(strtolower($type)) {
			case 'info':	$color = 'blue'; break;
			case 'error':	$color = 'red'; break;
			default: 		$color = 'green';
		}
		$this->messages .= "<span class=\"fmContent\" style=\"color:$color\">$text</span><br/>\n";
	}

/* PRIVATE METHODS ***************************************************************************** */

	/**
	 * check if FTP connection exists; create new one if necessary
	 *
	 * @return boolean
	 */
	function checkFtp() {
		if($this->FileManager->ftpHost && !$this->ftp) {
			if($this->ftpConnect($this->FileManager->ftpHost, $this->FileManager->ftpPort, 600)) {
				if($this->ftpLogin($this->FileManager->ftpUser, $this->FileManager->ftpPassword)) {
					$this->ftpPassiveMode($this->FileManager->ftpPassiveMode);
				}
			}
		}
		return $this->ftp;
	}

	/**
	 * remove start directory from file path
	 *
	 * @param string $path		file path
	 * @return string
	 */
	function checkPath($path) {
		return substr($path, strlen($this->FileManager->startDir), strlen($path));
	}
}

?>