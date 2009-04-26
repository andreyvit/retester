<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

include_once('Entry.php');
include_once('FileSystem.php');

/**
 * This class manages directory listings.
 *
 * @package FileManager
 * @subpackage class
 * @author Gerd Tentler
 */
class Listing {

/* PUBLIC PROPERTIES *************************************************************************** */

	/**
	 * current directory path
	 *
	 * @var string
	 */
	var $curDir;

	/**
	 * previous directory path
	 *
	 * @var string
	 */
	var $prevDir;

	/**
	 * holds current search string
	 *
	 * @var string
	 */
	var $searchString;

	/**
	 * current sort field
	 *
	 * @var string
	 */
	var $sortField = 'isDir';

	/**
	 * current sort order ('asc' or 'desc')
	 *
	 * @var string
	 */
	var $sortOrder = 'asc';

	/**
	 * holds FileSystem object
	 *
	 * @var FileSystem
	 */
	var $FileSystem;

	/**
	 * holds FileManager object
	 *
	 * @var FileManager
	 */
	var $FileManager;

/* PROTECTED PROPERTIES ************************************************************************ */

	/**
	 * holds OS type
	 *
	 * @var string
	 */
	var $sysType;

	/**
	 * holds current listing (entry objects)
	 *
	 * @var array
	 */
	var $entries;

	/**
	 * file extensions
	 *
	 * @var array
	 */
	var $extensions = array(
		'text'		=> '(txt)|([sp]?html?)|(css)|(jse?)|(php\d*)|(pr?l)|(pm)|(cgi)|(inc)|(csv)|(py)|(asp)|(ini)',
		'image'		=> '(gif)|(jpe?g)|(png)|(w?bmp)|(tiff?)|(pict?)|(ico)',
		'archive'	=> '(zip)|([rtj]ar)|(t?gz)|(t?bz2?)|(arj)|(ace)|(lzh)|(lha)|(xxe)|(uue?)|(iso)|(cab)|(r\d+)',
		'program'	=> '(exe)|(com)|(pif)|(bat)|(scr)|(app)',
		'acrobat'	=> '(pd[fx])',
		'word'		=> '(do[ct])|(do[ct]html)',
		'excel'		=> '(xl[stwv])|(xl[st]html)|(slk)'
	);

/* PUBLIC METHODS ****************************************************************************** */

	/**
	 * constructor
	 *
	 * @param FileManager $FileManager		file manager object
	 * @param string $dir					optional: directory path
	 * @return Listing
	 */
	function Listing(&$FileManager, $dir = '') {
		$this->FileManager =& $FileManager;
		$this->FileSystem =& new FileSystem($FileManager);
		$this->curDir = ($dir != '') ? $dir : $this->FileManager->startDir;
		$this->sysType = $this->FileSystem->getSystemType();
	}

	/**
	 * view current listing
	 */
	function view() {
		$subdir = ereg_replace('^' . $this->FileManager->startDir, '', $this->curDir);
		if($subdir || $this->searchString != '') $this->viewDirUp();

		if(is_array($this->entries)) foreach($this->entries as $Entry) {
			$Entry->view();
		}
	}

	/**
	 * refresh listing
	 */
	function refresh() {
		$this->entries = array();
		$this->readDir($this->curDir);
		$this->view();
	}

	/**
	 * sort entries
	 */
	function sortList() {
		$arr = $this->entries;
		$cnt = count($arr);
		$prop = $this->sortField;
		$swap = true;

		while($cnt && $swap) {
			$swap = false;
			for($i = 0; $i < $cnt; $i++) {
				for($j = $i; $j < $cnt - 1; $j++) {
					if($prop == 'isDir') {
						$noDir = ($arr[$j]->icon != 'dir') ? 1 : 0;
						$str1 = strtolower($noDir . $arr[$j]->name);
						$noDir = ($arr[$j + 1]->icon != 'dir') ? 1 : 0;
						$str2 = strtolower($noDir . $arr[$j + 1]->name);
					}
					else {
						$str1 = strtolower($arr[$j]->$prop);
						$str2 = strtolower($arr[$j + 1]->$prop);
					}

					if(($this->sortOrder == 'asc' && $str1 > $str2) ||
						($this->sortOrder == 'desc' && $str1 < $str2)) {
						$temp = $arr[$j];
						$arr[$j] = $arr[$j+1];
						$arr[$j+1] = $temp;
						$swap = true;
					}
				}
			}
			$cnt--;
		}
		$this->entries = $arr;
	}

	/**
	 * get entry by ID
	 *
	 * @param integer $id		entry ID
	 * @return mixed			entry object or false on failure
	 */
	function &getEntry($id) {
		if(is_array($this->entries)) foreach(array_keys($this->entries) as $ind) {
			$Entry =& $this->entries[$ind];
			if($Entry->id == $id) return $Entry;
		}
		return false;
	}

	/**
	 * get entry by file/directory name
	 *
	 * @param string $name		file/directory name
	 * @return mixed			entry object or false on failure
	 */
	function &getEntryByName($name) {
		if(is_array($this->entries)) foreach(array_keys($this->entries) as $ind) {
			$Entry =& $this->entries[$ind];
			if($Entry->name == $name) return $Entry;
		}
		$file = $this->curDir . '/' . $name;
		if(file_exists($file)) return $this->addEntry($file);
		else return false;
	}

	/**
	 * move uploaded file to current directory
	 *
	 * @param string $src		source file path
	 * @param string $newName	new file name
	 * @return boolean
	 */
	function upload($src, $newName) {
		if($this->FileManager->hideSystemFiles && $newName[0] == '.') {
			return false;
		}

		$ext = strtolower(substr($newName, strrpos($newName, '.') + 1));
		if($ext != '' && in_array($ext, $this->FileManager->hideFileTypes)) {
			return false;
		}

		if($this->FileManager->createBackups) {
			$this->createBackup($newName);
		}
		$dst = $this->curDir . '/' . $newName;
		return $this->FileSystem->putFile($src, $dst);
	}

	/**
	 * remove directory
	 *
	 * @param string $dir		directory path
	 * @return boolean
	 */
	function remDir($dir) {
		return $this->FileSystem->removeDir($dir);
	}

	/**
	 * create directory
	 *
	 * @param string $dir		directory path
	 * @return boolean
	 */
	function mkDir($dir) {
		return $this->FileSystem->makeDir($dir);
	}

	/**
	 * perform search
	 *
	 * @param string $text		search string
	 */
	function performSearch($text) {
		$this->searchString = $text;
		$this->refresh();
	}

/* PROTECTED METHODS *************************************************************************** */

	/**
	 * view header
	 */
	function viewHeader() {
		global $msg;

		$webPath = $this->FileManager->fmWebPath;
		$url = $webPath . '/action.php?fmContainer=' . $this->FileManager->container;

		print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"{$this->FileManager->fmWidth}\">\n";
		print "<tr>\n";
		print "<td class=\"fmTH1\" align=\"left\" style=\"padding:4px\">\n";
		print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n";
		print "<tr>\n";
		print "<td class=\"fmTH1\">";
		$this->viewTitle();
		print "</td>\n";
		print "<td class=\"fmTH1\" width=\"18\" align=\"right\" style=\"cursor:pointer\" title=\"{$msg['cmdRefresh']}\" ";
		print "onMouseOver=\"window.status='" . addslashes($msg['cmdRefresh']) . "'; return true\" ";
 		print "onMouseOut=\"window.status=''\" onClick=\"fmCall('$url&fmMode=refresh')\">";
		print "<img src=\"$webPath/icons/refresh.gif\" border=\"0\" width=\"11\" height=\"14\" alt=\"{$msg['cmdRefresh']}\"/>";
		print "</td>\n";

		if(strtolower($this->FileManager->fmView) == 'icons') {
			print "<td class=\"fmTH1\" width=\"18\" align=\"right\" style=\"cursor:pointer\" title=\"{$msg['cmdDetails']}\" ";
			print "onMouseOver=\"window.status='" . addslashes($msg['cmdDetails']) . "'; return true\" ";
	 		print "onMouseOut=\"window.status=''\" onClick=\"fmCall('$url&fmMode=switchView')\">";
			print "<img src=\"$webPath/icons/list_details.gif\" border=\"0\" width=\"11\" height=\"14\" alt=\"{$msg['cmdDetails']}\"/>";
			print "</td>\n";
		}
		else if(strtolower($this->FileManager->fmView) == 'details') {
			print "<td class=\"fmTH1\" width=\"18\" align=\"right\" style=\"cursor:pointer\" title=\"{$msg['cmdIcons']}\" ";
			print "onMouseOver=\"window.status='" . addslashes($msg['cmdIcons']) . "'; return true\" ";
	 		print "onMouseOut=\"window.status=''\" onClick=\"fmCall('$url&fmMode=switchView')\">";
			print "<img src=\"$webPath/icons/list_icons.gif\" border=\"0\" width=\"11\" height=\"14\" alt=\"{$msg['cmdIcons']}\"/>";
			print "</td>\n";
		}

		print "<td class=\"fmTH1\" width=\"22\" align=\"right\" style=\"cursor:pointer\" title=\"{$msg['cmdSearch']}\" ";
		print "onMouseOver=\"window.status='" . addslashes($msg['cmdSearch']) . "'; return true\" ";
 		print "onMouseOut=\"window.status=''\" onClick=\"fmOpenDialog('$url', 'fmSearch', '" . addslashes($msg['cmdSearch']) . "')\">";
		print "<img src=\"$webPath/icons/search.gif\" border=\"0\" width=\"13\" height=\"14\" alt=\"{$msg['cmdSearch']}\"/>";
		print "</td>\n";

		if($this->FileManager->enableNewDir && $this->searchString == '') {
			print "<td class=\"fmTH1\" width=\"22\" align=\"right\" style=\"cursor:pointer\" title=\"{$msg['cmdNewDir']}\" ";
			print "onMouseOver=\"window.status='" . addslashes($msg['cmdNewDir']) . "'; return true\" ";
	 		print "onMouseOut=\"window.status=''\" onClick=\"fmOpenDialog('$url', 'fmNewDir', '" . addslashes($msg['cmdNewDir']) . "')\">";
			print "<img src=\"$webPath/icons/newDir.gif\" border=\"0\" width=\"15\" height=\"14\" alt=\"{$msg['cmdNewDir']}\"/>";
			print "</td>\n";
		}
		else {
			$error = addslashes($msg['cmdNewDir'] . ': ' . $msg['errDisabled']);
			print "<td class=\"fmTH1\" width=\"22\" align=\"right\" onClick=\"fmOpenDialog('', 'fmError', '$error')\" ";
			print "onMouseOver=\"window.status=''; return true\">";
			print "<img src=\"$webPath/icons/newDir_x.gif\" border=\"0\" width=\"15\" height=\"14\"></td>\n";
		}

		if($this->FileManager->enableUpload && $this->searchString == '') {
			print "<td class=\"fmTH1\" width=\"18\" align=\"right\" style=\"cursor:pointer\" title=\"{$msg['cmdUploadFile']}\" ";
			print "onMouseOver=\"window.status='" . addslashes($msg['cmdUploadFile']) . "'; return true\" ";
	 		print "onMouseOut=\"window.status=''\" onClick=\"fmOpenDialog('$url', 'fmNewFile', '" . addslashes($msg['cmdUploadFile']) . "')\">";
			print "<img src=\"$webPath/icons/new.gif\" border=\"0\" width=\"11\" height=\"14\" alt=\"{$msg['cmdUploadFile']}\"/>";
			print "</td>\n";
		}
		else {
			$error = addslashes($msg['cmdUploadFile'] . ': ' . $msg['errDisabled']);
			print "<td class=\"fmTH1\" width=\"18\" align=\"right\" onClick=\"fmOpenDialog('', 'fmError', '$error')\" ";
			print "onMouseOver=\"window.status=''; return true\">";
			print "<img src=\"$webPath/icons/new_x.gif\" border=\"0\" width=\"11\" height=\"14\"></td>\n";
		}

		print "</tr></table>\n";
		print "</tr>\n";
		print "<tr>\n";
		print "<td class=\"fmTH2\">\n";
	}

	/**
	 * view footer
	 */
	function viewFooter() {
		print "</td>\n";
		print "</tr>\n";
		print "</table>\n";
	}

	/**
	 * view title
	 */
	function viewTitle() {
		global $msg;

		if($this->searchString != '') {
			$path = $msg['searchResult'] . ': ' . $this->searchString;
		}
		else $path = ereg_replace('^' . $this->FileManager->startDir, '', $this->curDir);
		if($path == '') $path = '/';

		if(!$this->FileManager->hideSystemType) {
			if(strlen($this->sysType) > 15) {
				$sysType = substr($this->sysType, 0, 15) . '...';
			}
			else $sysType = $this->sysType;

			print "[$sysType] $path";
		}
		else print $path;
	}

	/**
	 * create new entry; this method should be overwritten
	 */
	function &newEntry() {
		return new Entry($this);
	}

	/**
	 * read directory entries
	 *
	 * @param string $dir		directory path
	 * @return boolean
	 */
	function readDir($dir) {
		$startDir = $this->FileManager->startDir;
		if(strncmp($dir, $startDir, strlen($startDir) != 0)) {
			$dir = $startDir;
		}
		$list = $this->FileSystem->readDir($dir);
		if(!$list) return false;

		if(is_array($list)) foreach($list as $row) {
			$Entry = $this->addEntry($row, $dir);
			if(is_object($Entry)) {
				if($this->searchString != '' && $Entry->icon == 'dir') {
					$this->readDir($Entry->path);
				}
			}
			else if(is_string($Entry)) $this->readDir("$dir/$Entry");
		}
		$this->prevDir = $this->curDir;
		return true;
	}

	/**
	 * add listing entry
	 *
	 * @param string $file			file path or entry in FTP listing
	 * @param string $dir			optional: directory path
	 * @return mixed				entry object, directory name or false
	 */
	function &addEntry($file, $dir = '') {
		if($dir == '') $dir = $this->curDir;
		$Entry = $this->createEntry($file, $dir);

		if(is_object($Entry)) {
			$ext = strtolower(substr($Entry->name, strrpos($Entry->name, '.') + 1));
			if($ext != '' && in_array($ext, $this->FileManager->hideFileTypes)) {
				return false;
			}

			$Entry->thumbHash = '';
			$Entry->thumbWidth = $Entry->thumbHeight = 0;

			if(!$Entry->icon) {
				if($this->isType($ext, $this->extensions['text'])) $Entry->icon = 'text';
				else if($this->isType($ext, $this->extensions['image'])) $Entry->icon = 'image';
				else if($this->isType($ext, $this->extensions['archive'])) $Entry->icon = 'archive';
				else if($this->isType($ext, $this->extensions['program'])) $Entry->icon = 'exe';
				else if($this->isType($ext, $this->extensions['acrobat'])) $Entry->icon = 'acrobat';
				else if($this->isType($ext, $this->extensions['word'])) $Entry->icon = 'word';
				else if($this->isType($ext, $this->extensions['excel'])) $Entry->icon = 'excel';
				else $Entry->icon = 'file';

				if(in_array(strtolower($ext), array('jpeg', 'jpg', 'gif', 'png'))) {
					list($width, $height, $type) = $Entry->getThumbSize(
						$this->FileManager->thumbMaxWidth,
						$this->FileManager->thumbMaxHeight
					);
					if($type == 1 || $type == 2 || $type == 3) {
						$Entry->thumbHash = md5($Entry->path);
						$Entry->thumbWidth = $width;
						$Entry->thumbHeight = $height;
					}
				}
			}
			$Entry->id = count($this->entries);
			$this->entries[] =& $Entry;
		}
		return $Entry;
	}

	/**
	 * create listing entry
	 *
	 * @param string $file			file path or entry in FTP listing
	 * @param string $dir			directory path
	 * @return mixed				entry object, directory name or false
	 */
	function &createEntry($file, $dir) {
		if($this->FileManager->ftpHost) {
			$sysType = (stristr($this->sysType, 'winnt') || stristr($this->sysType, 'windows')) ? 'Windows' : 'UNIX';

			if($sysType == 'UNIX') {
				if(preg_match('/^([drwxst\-]{10}) +\d+ +([^ ]+) +([^ ]+) +(\d+) +(\w{3} +\d+ +(\d{2,4} )?[\d\:]{4,5}) +(.+)$/i', $file, $m)) {
					if($m[7] == '..' || $m[7] == '.') return false;
					if($this->searchString == '' || stristr($m[7], $this->searchString)) {
						$Entry =& $this->newEntry();
						$Entry->permissions = $m[1];
						$Entry->owner = $m[2];
						$Entry->group = $m[3];
						$Entry->size = $m[4];
						$Entry->changed = $m[6] ? date('Y-m-d H:i', strtotime($m[5])) : $m[5];
						$Entry->name = $m[7];
						$Entry->icon = ($Entry->permissions[0] == 'd') ? 'dir' : '';
						$Entry->path = $dir . '/' . $Entry->name;
					}
					else if($this->searchString != '' && $m[1][0] == 'd') return $m[7];
				}
			}
			else if($sysType == 'Windows') {
				if(preg_match('/^([\d\.]{10}) +([\d\:]{5}) +(<DIR>)? +([\d\.]*) +(.+)$/i', $file, $m)) {
					if($m[5] == '..' || $m[5] == '.') return false;
					if($this->searchString == '' || stristr($m[5], $this->searchString)) {
						$d = explode('.', $m[1]);
						$t = explode(':', $m[2]);
						$tstamp = mktime($t[0], $t[1], 0, $d[1], $d[0], $d[2]);
						$Entry =& $this->newEntry();
						$Entry->changed = $tstamp ? date('Y-m-d H:i', $tstamp) : $m[1] . ' ' . $m[2];
						$Entry->permissions = $m[3];
						$Entry->size = str_replace('.', '', $m[4]);
						$Entry->name = $m[5];
						$Entry->icon = ($Entry->permissions == '<DIR>') ? 'dir' : '';
						$Entry->path = $dir . '/' . $Entry->name;
					}
					else if($this->searchString != '' && $m[3] == '<DIR>') return $m[5];
				}
			}
		}
		else {
			$filename = basename($file);
			if($filename == '.' || $filename == '..') return false;
			if($this->searchString == '' || stristr($filename, $this->searchString)) {
				$Entry =& $this->newEntry();
				$Entry->owner = @fileowner($file);
				$Entry->group = @filegroup($file);
				$Entry->size = @filesize($file);
				$Entry->changed = date('Y-m-d H:i', @filemtime($file));
				$Entry->name = $filename;
				$Entry->icon = is_dir($file) ? 'dir' : '';
				$Entry->path = $dir . '/' . $Entry->name;
				$Entry->permissions = $Entry->getPerms();
			}
			else if($this->searchString != '' && is_dir($file)) return $filename;
		}

		if(is_object($Entry)) {
			if(!$this->FileManager->hideSystemFiles || $Entry->name[0] != '.') return $Entry;
		}
		return false;
	}

	/**
	 * check file type
	 *
	 * @param string $ext		file extension
	 * @param string $types		list of file types
	 * @return boolean
	 */
	function isType($ext, $types) {
		return preg_match('/^' . $types . '$/i', $ext);
	}

	/**
	 * create backup by renaming original file
	 *
	 * @param string $fileName		file name
	 */
	function createBackup($fileName) {
		$ext = substr($fileName, strrpos($fileName, '.'));
		$name = substr($fileName, 0, strrpos($fileName, '.'));
		$backupName = $fileName;
		$cnt = 0;

		while($this->getEntryByName($backupName)) {
			$cnt++;
			$backupName = $name . "($cnt)$ext";
		}

		if($cnt > 0) {
			$this->FileSystem->rename($this->curDir . '/' . $fileName, $this->curDir . '/' . $backupName);
		}
	}
}

?>