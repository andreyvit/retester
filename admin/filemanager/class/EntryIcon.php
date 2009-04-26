<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

include_once('Entry.php');

/**
 * This class manages file entries (icon view).
 *
 * @package FileManager
 * @subpackage class
 * @author Gerd Tentler
 */
class EntryIcon extends Entry {

/* PUBLIC METHODS ****************************************************************************** */

	/**
	 * constructor
	 *
	 * @param Listing $Listing
	 * @return EntryIcon
	 */
	function EntryIcon(&$Listing) {
		parent::Entry($Listing);
	}

	/**
	 * view entry
	 */
	function view() {
		$this->viewHeader();
		$this->viewIcon();
		$this->viewName();
		$this->viewActionIcons();
		$this->viewFooter();
	}

	/**
	 * view header
	 */
	function viewHeader() {
		$class = ($this->Listing->searchString != '') ? 'fmSearchResult' : 'fmTD1';

		if($this->Listing->cellCnt >= $this->Listing->cellsPerRow) {
			$this->Listing->cellCnt = 0;
			print "</tr>\n<tr align=\"center\" valign=\"top\">\n";
		}
	    print "<td class=\"$class\" ";
	    print "onMouseOver=\"this.className='fmTD2'\" onMouseOut=\"this.className='$class'\">\n";
	    $this->Listing->cellCnt++;
	}

	/**
	 * view footer
	 */
	function viewFooter() {
		print "</td>\n";
	}

	/**
	 * view icon
	 */
	function viewIcon() {
		list($action, $tooltip) = $this->getIconAction();
		$styles = array();
		if($this->thumbHash) $styles[] = 'border:1px solid #E0E0E0';
		if($action) $styles[] = 'cursor:pointer';
		$style = join('; ', $styles);

    	print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"width:100%; height:60px\">\n";
    	print "<tr>\n";
    	print "<td align=\"center\" style=\"$style\" title=\"$tooltip\" onClick=\"$action\" ";
     	print "onMouseOver=\"window.status='" . addslashes($tooltip) . "'; return true\" ";
     	print "onMouseOut=\"window.status=''\">\n";

	    if($this->thumbHash) {
			$cont = $this->FileManager->container;
			$url = $this->FileManager->fmWebPath . "/action.php?fmContainer=$cont";
			$width = round($this->FileManager->fmWidth * $this->Listing->cellWidth / 100) - 16;
      		list($width, $height) = $this->getThumbSize($width, 54);
      		$thumbnail = "$url&fmMode=getThumbnail&fmObject={$this->id}&width=$width&height=$height&{$this->thumbHash}";
      		print "<img src=\"$thumbnail\" border=\"0\" width=\"$width\" height=\"$height\" alt=\"$tooltip\"/>\n";
    	}
    	else {
			$icon = $this->FileManager->fmWebPath . '/icons/' . $this->icon . '_big.gif';
      		print "<img src=\"$icon\" border=\"0\" width=\"32\" height=\"32\" alt=\"$tooltip\"/>\n";
    	}
    	print "</td>\n";
    	print "</tr>\n";
    	print "</table>\n";
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

		print "<a href=\"javascript:$onClick\" class=\"fmLink\">";
	    if(strlen($this->name) > $this->Listing->nameMaxLen) {
	    	print substr($this->name, 0, $this->Listing->nameMaxLen) . '...';
	    }
	    else print $this->name;
	    print "</a><br/><br/>\n";
	}
}

?>