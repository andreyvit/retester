<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

/**
 * This class creates a text editor.
 *
 * @package FileManager
 * @subpackage class
 * @author Gerd Tentler
 */
class Editor {

/* PRIVATE PROPERTIES ************************************************************************** */

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
	 * @return Editor
	 */
	function Editor(&$FileManager) {
		$this->FileManager =& $FileManager;
	}

	/**
	 * view text editor
	 *
	 * @param Entry $Entry		file entry object
	 */
	function view(&$Entry) {
		$this->viewHeader($Entry);
		$this->viewContent($Entry);
		$this->viewFooter();
	}

/* PRIVATE METHODS ***************************************************************************** */

	/**
	 * view header
	 *
	 * @param Entry $Entry		file entry object
	 */
	function viewHeader(&$Entry) {
		global $msg;

		$webPath = $this->FileManager->fmWebPath;
		$url = $webPath . '/action.php?fmContainer=' . $this->FileManager->container;

		print "<form name=\"frmEdit\" class=\"fmForm\" action=\"javascript:fmCall('$url', 'frmEdit')\" method=\"post\">\n";
    	print "<input type=\"hidden\" name=\"fmMode\" value=\"edit\">\n";
    	print "<input type=\"hidden\" name=\"fmObject\" value=\"$Entry->id\">\n";
		print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"4\" width=\"100%\">\n";
		print "<tr>\n";
		print "<td class=\"fmTH1\" align=\"left\">" . $msg['cmdEdit'] . ": $Entry->name</td>\n";
		print "<td class=\"fmTH1\" align=\"right\" nowrap=\"nowrap\">\n";
		print "<a href=\"javascript:fmCall('$url')\" title=\"" . $msg['cmdViewList'] . "\" ";
		print "onMouseOver=\"window.status='" . $msg['cmdViewList'] . "'; return true\" ";
		print "onMouseOut=\"window.status=''\">\n";
		print "<img src=\"$webPath/icons/list.gif\" border=\"0\" width=\"14\" height=\"14\" alt=\"" . $msg['cmdViewList'] . "\"></a>\n";
		print "&nbsp;<a href=\"javascript:document.frmEdit.reset()\" title=\"" . $msg['cmdReset'] . "\" ";
		print "onMouseOver=\"window.status='" . $msg['cmdReset'] . "'; return true\" ";
		print "onMouseOut=\"window.status=''\">\n";
		print "<img src=\"$webPath/icons/reset.gif\" border=\"0\" width=\"14\" height=\"14\" alt=\"" . $msg['cmdReset'] . "\"></a>\n";
		print "<a href=\"javascript:fmCallOK('" . $msg['msgSaveFile'] . "', '', 'frmEdit')\" title=\"" . $msg['cmdSave'] . "\" ";
		print "onMouseOver=\"window.status='" . $msg['cmdSave'] . "'; return true\" ";
		print "onMouseOut=\"window.status=''\">\n";
		print "<img src=\"$webPath/icons/save.gif\" border=\"0\" width=\"14\" height=\"14\" alt=\"" . $msg['cmdSave'] . "\"></a>\n";
		print "</td>\n";
		print "</tr>\n";
		print "<tr>\n";
		print "<td class=\"fmTH2\" colspan=\"2\" align=\"center\">\n";
	}

	/**
	 * view footer
	 */
	function viewFooter() {
		print "</td>\n";
		print "</tr>\n";
		print "</table>\n";
		print "</form>\n";
	}

	/**
	 * view file content
	 *
	 * @param Entry $Entry		file entry object
	 */
	function viewContent(&$Entry) {
		$file = $Entry->getFile();
		if($fp = @fopen($file, 'rt')) {
			$content = @fread($fp, filesize($file));
			@fclose($fp);
		}
		$width = $this->FileManager->fmWidth - 14;
		$height = $this->FileManager->maskHeight;

		print "<textarea name=\"fmText\" style=\"width:{$width}px; height:{$height}px\" ";
		print "wrap=\"off\" class=\"fmField\">" . htmlspecialchars($content) . "</textarea>\n";
	}
}

?>