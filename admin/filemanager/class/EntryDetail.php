<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

include_once('Entry.php');

/**
 * This class manages file entries (detailed view).
 *
 * @package FileManager
 * @subpackage class
 * @author Gerd Tentler
 */
class EntryDetail extends Entry {

/* PUBLIC METHODS ****************************************************************************** */

	/**
	 * constructor
	 *
	 * @param Listing $Listing
	 * @return EntryDetail
	 */
	function EntryDetail(&$Listing) {
		parent::Entry($Listing);
	}

	/**
	 * view entry
	 */
	function view() {
		$this->viewHeader();
		$this->viewIcon();
		$this->viewName();
		$this->viewSize();
		$this->viewModified();
		$this->viewActionIcons();
		$this->viewFooter();
	}

	/**
	 * view header
	 */
	function viewHeader() {
		$class = ($this->Listing->searchString != '') ? 'fmSearchResult' : 'fmTD1';
		print "<tr class=\"$class\" align=\"center\" valign=\"top\" ";
		print "onMouseOver=\"this.className='fmTD2'\" onMouseOut=\"this.className='$class'\">\n";
	}

	/**
	 * view footer
	 */
	function viewFooter() {
		print "</tr>\n";
	}

	/**
	 * view icon
	 */
	function viewIcon() {
		list($action, $tooltip) = $this->getIconAction();
		$style = $action ? 'cursor:pointer' : '';
		$icon = $this->FileManager->fmWebPath . '/icons/' . $this->icon . '.gif';

		print "<td class=\"fmContent\" title=\"$tooltip\" style=\"$style\" ";
     	print "onMouseOver=\"window.status='" . addslashes($tooltip) . "'; return true\" ";
     	print "onMouseOut=\"window.status=''\" ";
     	print "onClick=\"$action\">";
    	print "<img src=\"$icon\" border=\"0\" width=\"12\" height=\"10\" alt=\"$tooltip\"/>";
    	print "</td>\n";
	}

/* PROTECTED METHODS *************************************************************************** */

	/**
	 * view file name
	 */
	function viewName() {
		$cont = $this->FileManager->container;
		$name = addslashes($this->name);
		$onClick  = "fmFileInfo('$cont', '$this->id', '$name', '$this->permissions', '$this->owner', '$this->group', ";
		$onClick .= "'$this->changed', '$this->size', '$this->thumbHash', '$this->thumbWidth', '$this->thumbHeight')";
		print "<td class=\"fmContent\" align=\"left\" style=\"cursor:pointer\" onClick=\"$onClick\">$this->name</td>\n";
	}

	/**
	 * view file size
	 */
	function viewSize() {
		if($this->size < 1000) $size = $this->size . ' B';
		else {
			$size = $this->size / 1024;
			if($size > 999) $size = number_format($size / 1024, 1) . ' MB';
			else $size = number_format($size, 1) . ' KB';
		}
		print "<td class=\"fmContent\" align=\"right\">$size</td>\n";
	}

	/**
	 * view last modification date
	 */
	function viewModified() {
		print "<td class=\"fmContent\">$this->changed</td>\n";
	}

	/**
	 * view action icons
	 */
	function viewActionIcons() {
		print "<td class=\"fmTD2\" nowrap=\"nowrap\">\n";
		parent::viewActionIcons();
		print "</td>\n";
	}
}

?>