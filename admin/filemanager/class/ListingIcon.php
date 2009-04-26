<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

include_once('Listing.php');
include_once('EntryIcon.php');

/**
 * This class manages directory listings (icon view).
 *
 * @package FileManager
 * @subpackage class
 * @author Gerd Tentler
 */
class ListingIcon extends Listing {

/* PRIVATE PROPERTIES ************************************************************************** */

	/**
	 * max. file name length
	 *
	 * @var integer
	 */
	var $nameMaxLen;

	/**
	 * cell width in pixels
	 *
	 * @var integer
	 */
	var $cellWidth;

	/**
	 * number of cells in a table row
	 *
	 * @var integer
	 */
	var $cellsPerRow;

	/**
	 * cell counter
	 *
	 * @var integer
	 */
	var $cellCnt;

/* PUBLIC METHODS ****************************************************************************** */

	/**
	 * constructor
	 *
	 * @param FileManager $FileManager		file manager object
	 * @param string $dir					optional: directory path
	 * @return ListingIcon
	 */
	function ListingIcon(&$FileManager, $dir = '') {
		parent::Listing($FileManager, $dir);
		$this->cellsPerRow = floor($this->FileManager->fmWidth / 100);
		$this->cellWidth = number_format(100 / $this->cellsPerRow, 2);
		$this->nameMaxLen = round($this->cellWidth * $this->FileManager->fmWidth / 1000);
	}

	/**
	 * view listing
	 */
	function view() {
		$this->cellCnt = 0;
		$this->sortList();
		$this->viewHeader();
		parent::view();
		$this->viewFooter();
	}

	/**
	 * switch view
	 *
	 * @return ListingDetail
	 */
	function &switchView() {
		$this->FileManager->fmView = 'details';
		return new ListingDetail($this->FileManager, $this->curDir);
	}

/* PROTECTED METHODS *************************************************************************** */

	/**
	 * create new entry
	 */
	function &newEntry() {
		return new EntryIcon($this);
	}

	/**
	 * view header
	 */
	function viewHeader() {
		parent::viewHeader();
		print "<table border=\"0\" cellspacing=\"2\" cellpadding=\"5\" width=\"100%\">\n";
		print "<colgroup>\n";

		for($i = 0; $i < $this->cellsPerRow; $i++) {
			print "<col width=\"{$this->cellWidth}%\"/>\n";
		}
		print "</colgroup>\n";
		print "<tr align=\"center\" valign=\"top\">\n";
	}

	/**
	 * view footer
	 */
	function viewFooter() {
		$class = ($this->searchString != '') ? 'fmSearchResult' : 'fmTD1';
		while($this->cellCnt < $this->cellsPerRow) {
			print "<td class=\"$class\">&nbsp;</td>\n";
			$this->cellCnt++;
		}
		print "</tr>\n";
		print "</table>\n";
		parent::viewFooter();
	}

	/**
	 * view directory up icon
	 */
	function viewDirUp() {
		$Entry = $this->newEntry();
		$Entry->icon = 'cdup';
		$Entry->viewHeader();
		$Entry->viewIcon();
		if($this->searchString == '') print "..\n";
		$Entry->viewFooter();
	}
}

?>