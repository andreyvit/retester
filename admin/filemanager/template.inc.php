<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

function fmCloseButton() {
	return	'<table border="0" cellspacing="0" cellpadding="0" width="16" height="16"><tr>' .
			'<td class="fmTH3" align="center"' .
			' onMouseOver="this.className=\'fmTH4\'"' .
			' onMouseOut="this.className=\'fmTH3\'"' .
			' onMouseDown="this.className=\'fmTH5\'"' .
			' onMouseUp="this.className=\'fmTH4\'"' .
			' onClick="fmFadeOut()">&times;</td>' .
			'</tr></table>';
}

?>
<script src="<?php print $fmWebPath; ?>/js/ajax.js" type="text/javascript"></script>
<script src="<?php print $fmWebPath; ?>/js/filemanager.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php print $fmWebPath; ?>/css/filemanager.css" type="text/css">


<script type="text/javascript">
fmWebPath = '<?php print addslashes($fmWebPath); ?>';
fmMsg = new Array();
fmMsg['name'] = '<?php print addslashes($msg['name']); ?>';
fmMsg['permissions'] = '<?php print addslashes($msg['permissions']); ?>';
fmMsg['owner'] = '<?php print addslashes($msg['owner']); ?>';
fmMsg['group'] = '<?php print addslashes($msg['group']); ?>';
fmMsg['size'] = '<?php print addslashes($msg['size']); ?>';
fmMsg['lastChange'] = '<?php print addslashes($msg['lastChange']); ?>';
</script>

<div id="fmInfo" class="fmDialog">
<table border="0" cellspacing="0" cellpadding="0"><tr>
<td class="fmTH1" style="padding:4px; cursor:move" align="left"><?php print $msg['fileInfo']; ?></td>
<td class="fmTH1" style="padding:2px; cursor:move" align="right"><?php print fmCloseButton(); ?></td>
</tr><tr>
<td class="fmTH1" colspan="2" style="padding:1px">
<div id="fmInfoText" class="fmTD2" style="padding:4px"></div></td>
</tr></table>
</div>

<div id="fmError" class="fmDialog">
<table border="0" cellspacing="0" cellpadding="0" width="400"><tr>
<td class="fmTH1" style="padding:4px; cursor:move" align="left"><?php print $msg['error']; ?></td>
<td class="fmTH1" style="padding:2px; cursor:move" align="right"><?php print fmCloseButton(); ?></td>
</tr><tr>
<td class="fmTH3" colspan="2" style="padding:4px">
<div id="fmErrorText" class="fmError"></div></td>
</tr></table>
</div>

<div id="fmRename" class="fmDialog">
<form name="fmRename" class="fmForm" method="post">
<input type="hidden" name="fmMode" value="rename">
<input type="hidden" name="fmContainer" value="">
<input type="hidden" name="fmObject" value="">
<table border="0" cellspacing="0" cellpadding="0"><tr>
<td id="fmRenameText" class="fmTH1" style="padding:4px; cursor:move" align="left" nowrap="nowrap"></td>
<td class="fmTH1" style="padding:2px; cursor:move" align="right"><?php print fmCloseButton(); ?></td>
</tr><tr>
<td class="fmTH3" colspan="2" align="center" style="padding:4px">
<input type="text" name="fmName" size="40" maxlength="60" class="fmField" value=""/><br/>
<input type="submit" class="fmButton" value="<?php print $msg['cmdRename']; ?>"/>
</td>
</tr></table>
</form>
</div>

<div id="fmDelete" class="fmDialog">
<form name="fmDelete" class="fmForm" method="post">
<input type="hidden" name="fmMode" value="delete">
<input type="hidden" name="fmObject" value="">
<table border="0" cellspacing="0" cellpadding="0"><tr>
<td id="fmDeleteText" class="fmTH1" style="padding:4px; cursor:move" align="left" nowrap="nowrap"></td>
<td class="fmTH1" style="padding:2px; cursor:move" align="right"><?php print fmCloseButton(); ?></td>
</tr><tr>
<td class="fmTH3" colspan="2" align="center" style="padding:4px">
<div id="fmDeleteText2" class="fmTD3"></div>
<input type="submit" class="fmButton" value="<?php print $msg['cmdDelete']; ?>"/>
</td>
</tr></table>
</form>
</div>

<div id="fmPerm" class="fmDialog">
<form name="fmPerm" class="fmForm" method="post">
<input type="hidden" name="fmMode" value="permissions">
<input type="hidden" name="fmObject" value="">
<table border="0" cellspacing="0" cellpadding="0"><tr>
<td id="fmPermText" class="fmTH1" style="padding:4px; cursor:move" align="left" nowrap="nowrap"></td>
<td class="fmTH1" style="padding:2px; cursor:move" align="right"><?php print fmCloseButton(); ?></td>
</tr><tr>
<td class="fmTH3" colspan="2" align="center" style="padding:4px">
<table border="0" cellspacing="2" cellpadding="4"><tr align="center">
<td class="fmTH2"><?php print $msg['owner']; ?></td>
<td class="fmTH2"><?php print $msg['group']; ?></td>
<td class="fmTH2"><?php print $msg['other']; ?></td>
</tr><tr align="left">
<?php
for($i = 0; $i < 9; $i += 3) {
?>
    <td class="fmTD2" nowrap="nowrap">
    <input type="checkbox" name="fmPerms[<?php print $i; ?>]" value="1"/> <?php print $msg['read']; ?><br/>
    <input type="checkbox" name="fmPerms[<?php print $i+1; ?>]" value="1"/> <?php print $msg['write']; ?><br/>
    <input type="checkbox" name="fmPerms[<?php print $i+2; ?>]" value="1"/> <?php print $msg['execute']; ?>
    </td>
<?php
}
?>
</tr></table>
<input type="submit" class="fmButton" value="<?php print $msg['cmdChangePerm']; ?>"/>
</td>
</tr></table>
</form>
</div>

<div id="fmNewFile" class="fmDialog">
<form name="fmNewFile" class="fmForm" method="post" enctype="multipart/form-data">
<input type="hidden" name="fmMode" value="newFile">
<table border="0" cellspacing="0" cellpadding="0"><tr>
<td id="fmNewFileText" class="fmTH1" style="padding:4px; cursor:move" align="left" nowrap="nowrap"></td>
<td class="fmTH1" style="padding:2px; cursor:move" align="right"><?php print fmCloseButton(); ?></td>
</tr><tr>
<td class="fmTH3" colspan="2" align="center" style="padding:4px">
<input type="file" name="fmFile[0]" size="20" class="fmField" onClick="fmNewFileSelector(1)" onChange="fmNewFileSelector(1)"/>
<input type="file" name="fmFile[1]" size="20" class="fmField" onClick="fmNewFileSelector(2)" onChange="fmNewFileSelector(2)" style="display:none"/>
<input type="file" name="fmFile[2]" size="20" class="fmField" onClick="fmNewFileSelector(3)" onChange="fmNewFileSelector(3)" style="display:none"/>
<input type="file" name="fmFile[3]" size="20" class="fmField" onClick="fmNewFileSelector(4)" onChange="fmNewFileSelector(4)" style="display:none"/>
<input type="file" name="fmFile[4]" size="20" class="fmField" onClick="fmNewFileSelector(5)" onChange="fmNewFileSelector(5)" style="display:none"/>
<input type="file" name="fmFile[5]" size="20" class="fmField" onClick="fmNewFileSelector(6)" onChange="fmNewFileSelector(6)" style="display:none"/>
<input type="file" name="fmFile[6]" size="20" class="fmField" onClick="fmNewFileSelector(7)" onChange="fmNewFileSelector(7)" style="display:none"/>
<input type="file" name="fmFile[7]" size="20" class="fmField" onClick="fmNewFileSelector(8)" onChange="fmNewFileSelector(8)" style="display:none"/>
<input type="file" name="fmFile[8]" size="20" class="fmField" onClick="fmNewFileSelector(9)" onChange="fmNewFileSelector(9)" style="display:none"/>
<input type="file" name="fmFile[9]" size="20" class="fmField" style="display:none"/>
<div class="fmTH3" style="font-weight:normal; text-align:left; border:none">
<input type="checkbox" name="fmReplSpaces" value="1"<?php if($replSpacesUpload) print ' checked="checked"'; ?>/>
file name =&gt; file_name<br/>
<input type="checkbox" name="fmLowerCase" value="1"<?php if($lowerCaseUpload) print ' checked="checked"'; ?>/>
FileName =&gt; filename
</div>
<input type="submit" class="fmButton" value="<?php print $msg['cmdUploadFile']; ?>"/>
</td>
</tr></table>
</form>
</div>

<div id="fmNewDir" class="fmDialog">
<form name="fmNewDir" class="fmForm" method="post">
<input type="hidden" name="fmMode" value="newDir">
<table border="0" cellspacing="0" cellpadding="0"><tr>
<td id="fmNewDirText" class="fmTH1" style="padding:4px; cursor:move" align="left" nowrap="nowrap"></td>
<td class="fmTH1" style="padding:2px; cursor:move" align="right"><?php print fmCloseButton(); ?></td>
</tr><tr>
<td class="fmTH3" colspan="2" align="center" style="padding:4px">
<input type="text" name="fmName" size="40" maxlength="60" class="fmField"/><br/>
<input type="submit" class="fmButton" value="<?php print $msg['cmdNewDir']; ?>"/>
</td>
</tr></table>
</form>
</div>

<div id="fmSearch" class="fmDialog">
<form name="fmSearch" class="fmForm" method="post">
<input type="hidden" name="fmMode" value="search"/>
<table border="0" cellspacing="0" cellpadding="0"><tr>
<td id="fmSearchText" class="fmTH1" style="padding:4px; cursor:move" align="left" nowrap="nowrap"></td>
<td class="fmTH1" style="padding:2px; cursor:move" align="right"><?php print fmCloseButton(); ?></td>
</tr><tr>
<td class="fmTH3" colspan="2" align="center" style="padding:4px">
<input type="text" name="fmName" size="40" maxlength="60" class="fmField"/><br/>
<input type="submit" class="fmButton" value="<?php print $msg['cmdSearch']; ?>"/>
</td>
</tr></table>
</form>
</div>

<iframe name="fmFileAction" style="display:none"></iframe>
