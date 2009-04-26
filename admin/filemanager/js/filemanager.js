/*********************************************************************************************************
 This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
*********************************************************************************************************/

//--------------------------------------------------------------------------------------------------------
// Configuration
//--------------------------------------------------------------------------------------------------------

var fmFadeSpeed = 15;   // fade speed (0 - 30; 0 = no fading)*

// * Fading was successfully tested only on Windows XP with IE 6 + 7, NN 7, Opera 9 and Firefox 1 - 3.
//   Other browsers might not support this feature.

//--------------------------------------------------------------------------------------------------------
// Get browser information
//--------------------------------------------------------------------------------------------------------

var OP = (navigator.userAgent.indexOf('Opera') != -1);
var IE = (navigator.userAgent.indexOf('MSIE') != -1 && !OP);
var IE4 = document.all;
var DOM = document.getElementById;
var MAC = (navigator.userAgent.indexOf('Mac') != -1);

//--------------------------------------------------------------------------------------------------------
// Global variables
//--------------------------------------------------------------------------------------------------------

var fmMouseX = fmMouseY = 0;
var fmDragging = false;
var fmTimer = fmIv = fmOpacity = 0;
var fmObj, fmSObj;

//--------------------------------------------------------------------------------------------------------
// General functions
//--------------------------------------------------------------------------------------------------------

function fmGetObj(id) {
	var obj = null;
	if(DOM) obj = document.getElementById(id);
	else if(IE4) obj = document.all[id];
	return obj;
}

function fmGetWindowWidth() {
	var winX = 0;
	if(window.innerWidth)
		winX = window.innerWidth;
	else if(document.documentElement && document.documentElement.clientWidth)
		winX = document.documentElement.clientWidth;
	else if(document.body && document.body.clientWidth)
		winX = document.body.clientWidth;
	else winX = screen.width;
	return winX;
}

function fmGetWindowHeight() {
	var winY = 0;
	if(window.innerHeight)
		winY = window.innerHeight;
	else if(document.documentElement && document.documentElement.clientHeight)
		winY = document.documentElement.clientHeight;
	else if(document.body && document.body.clientHeight)
		winY = document.body.clientHeight;
	else winY = screen.height;
	return winY;
}

function fmGetScrollLeft() {
	var scrLeft = 0;
	if(document.documentElement && document.documentElement.scrollLeft)
		scrLeft = document.documentElement.scrollLeft;
	else if(document.body && document.body.scrollLeft)
		scrLeft = document.body.scrollLeft;
	else if(window.pageXOffset) scrLeft = window.pageXOffset;
	return scrLeft;
}

function fmGetScrollTop() {
	var scrTop = 0;
	if(document.documentElement && document.documentElement.scrollTop)
		scrTop = document.documentElement.scrollTop;
	else if(document.body && document.body.scrollTop)
		scrTop = document.body.scrollTop;
	else if(window.pageYOffset) scrTop = window.pageYOffset;
	return scrTop;
}

function fmViewError(msg) {
	var x = Math.round((fmGetWindowWidth() - 400) / 2);
	var y = Math.round((fmGetWindowHeight() - 50) / 2);
	fmOpenDialog(null, 'fmError', msg, null, null, null, x, y);
}

function fmCallOK(msg, url, frmName) {
	var ok = confirm(msg);
	if(ok) {
		if(url) fmCall(url, frmName);
		else if(frmName) document.forms[frmName].submit();
	}
}

function fmCall(url, frmName) {
	if(fmSObj && fmSObj.visibility == 'visible') {
		fmSObj.visibility = 'hidden';
	}
	url.match(/fmCont(\d+)/);
	var cnt = RegExp.$1;
	var listCont = fmGetObj('fmList' + cnt);

	if(listCont) {
		var fmCont = fmGetObj('fmCont' + cnt);
		var logCont = fmGetObj('fmLog' + cnt);
		var ajaxObj = new ajax();
		var listLoad;

		if(fmCont) listLoad = fmViewLoader(fmCont, url);
		ajaxObj.makeRequest(url, fmCallBack.bind(this, ajaxObj, listCont, listLoad, logCont, fmCont), frmName);
	}
}

function fmCallBack(ajaxObj, listCont, listLoad, logCont, fmCont) {
	if(!ajaxObj) return;
	var response = ajaxObj.request.responseText;
	fmViewResponse(response, fmCont, listCont, logCont, listLoad);
}

function fmCheckFile(listLoad) {
	var iFrame = frames.fmFileAction;
	if(iFrame) {
		var url = iFrame.document.location.href;
		var response = iFrame.document.body.innerHTML;

		if(response.indexOf('{\{\/fmLOG\}\}') != -1) {
			if(fmIv) clearInterval(fmIv);
			iFrame.document.body.innerHTML = '';

			url.match(/fmCont(\d+)/);
			var cnt = RegExp.$1;

			var fmCont = fmGetObj('fmCont' + cnt);
			var fmList = fmGetObj('fmList' + cnt);
			var fmLog = fmGetObj('fmLog' + cnt);
			fmViewResponse(response, fmCont, fmList, fmLog, listLoad);
		}
	}
}

function fmGetFile(url) {
	var iFrame = frames.fmFileAction;
	if(iFrame) {
		iFrame.document.location.href = url;
		fmIv = setInterval("fmCheckFile()", 200);
	}
}

function fmViewLoader(fmCont, url) {
	var listLoad = document.createElement('div');
	fmSetOpacity(20, listLoad);
	listLoad.style.position = 'absolute';
	listLoad.style.left = 0;
	listLoad.style.top = 0;
	listLoad.style.backgroundColor = '#000000';
	listLoad.style.width = fmCont.offsetWidth + 'px';
	listLoad.style.height = fmCont.offsetHeight + 'px';
	listLoad.style.display = 'block';
	listLoad.style.zIndex = 69;
	fmCont.appendChild(listLoad);

	var img = document.createElement('img');
	var webPath = url.substring(0, url.lastIndexOf('/'));

	img.src = webPath + '/icons/ajax_loader.gif';
	img.width = 100;
	img.height = 100;
	img.style.position = 'absolute';
	img.style.left = '50%';
	img.style.top = '50%';
	img.style.marginLeft = '-50px';
	img.style.marginTop = '-50px';
	listLoad.appendChild(img);
	return listLoad;
}

function fmViewResponse(response, fmCont, listCont, logCont, listLoad) {
	response = response.replace('{{/fmLOG}}', '');
	var contents = response.split('{{fmLOG}}');

	if(contents[0].match('{{fmERROR}}')) {
		contents[0] = contents[0].replace('{{/fmERROR}}', '');
		var arr = contents[0].split('{{fmERROR}}');
		fmViewError(arr[1]);
		contents[0] = arr[0];
	}

	if(listCont && contents[0]) {
		listCont.innerHTML = contents[0];
	}

	if(logCont && contents[1]) {
		logCont.innerHTML += contents[1];
		logCont.scrollTop = logCont.scrollHeight;
	}

	if(listLoad && fmCont) fmCont.removeChild(listLoad);
}

//--------------------------------------------------------------------------------------------------------
// Event handlers
//--------------------------------------------------------------------------------------------------------

function fmGetMouse(e) {
	var mouseX = fmMouseX;
	var mouseY = fmMouseY;

	if(e && e.pageX != null) {
		fmMouseX = e.pageX;
		fmMouseY = e.pageY;
	}
	else if(event && event.clientX != null) {
		fmMouseX = event.clientX + fmGetScrollLeft();
		fmMouseY = event.clientY + fmGetScrollTop();
	}
	if(fmMouseX < 0) fmMouseX = 0;
	if(fmMouseY < 0) fmMouseY = 0;

	if(fmDragging && fmSObj) {
		var x = parseInt(fmSObj.left + 0);
		var y = parseInt(fmSObj.top + 0);
		fmSObj.left = x + (fmMouseX - mouseX) + 'px';
		fmSObj.top = y + (fmMouseY - mouseY) + 'px';
	}
}

function fmStartDrag(e) {
	if(!DOM && !IE4) return;
	var firedobj = (e && e.target) ? e.target : event.srcElement;
	var topelement = DOM ? "HTML" : "BODY";

	if(DOM && firedobj.nodeType == 3) firedobj = firedobj.parentNode;

	if(firedobj.className == 'fmTH1') {
		firedobj.unselectable = true;

		while(firedobj.tagName != topelement && firedobj.className != "fmDialog")
			firedobj = DOM ? firedobj.parentNode : firedobj.parentElement;

		if(firedobj.className == "fmDialog") {
			fmSObj = firedobj.style;
			fmDragging = true;
		}
	}
}

document.onmousemove = fmGetMouse;
document.onmousedown = fmStartDrag;
document.onmouseup = function() { fmDragging = false; }

//--------------------------------------------------------------------------------------------------------
// Set opacity, fade-in/out
//--------------------------------------------------------------------------------------------------------

function fmSetOpacity(opacity, obj) {
	if(!obj) obj = fmObj;
	if(obj) {
		obj.style.opacity = opacity / 100;
		obj.style.MozOpacity = opacity / 100;
		obj.style.KhtmlOpacity = opacity / 100;
		obj.style.filter = 'alpha(opacity=' + opacity + ')';
	}
}

function fmFadeIn() {
	if(fmSObj) {
		if(fmTimer) clearTimeout(fmTimer);
		fmSObj.visibility = 'visible';
		if(fmFadeSpeed && fmOpacity < 100) {
			fmOpacity += fmFadeSpeed;
			if(fmOpacity > 100) fmOpacity = 100;
			fmSetOpacity(fmOpacity);
			fmTimer = setTimeout('fmFadeIn()', 1);
		}
		else {
			fmOpacity = 100;
			fmSetOpacity(100);
		}
	}
}

function fmFadeOut() {
	if(fmSObj) {
		if(fmTimer) clearTimeout(fmTimer);
		if(fmFadeSpeed && fmOpacity > 0) {
			fmOpacity -= fmFadeSpeed;
			if(fmOpacity < 0) fmOpacity = 0;
			fmSetOpacity(fmOpacity);
			fmTimer = setTimeout('fmFadeOut()', 1);
		}
		else {
			fmOpacity = 0;
			fmSetOpacity(0);
			fmSObj.visibility = 'hidden';
		}
	}
}

//--------------------------------------------------------------------------------------------------------
// View dialog box
//--------------------------------------------------------------------------------------------------------

function fmSetDialogLeft(x) {
	var width = 0;
	if(MAC && IE) fmSObj.width = '100px';
	if(DOM) width = fmObj.offsetWidth;
	else if(IE4) width = fmSObj.pixelWidth;

	var left = x ? x : fmMouseX - width;
	if(left < 0) left = 0;

	fmSObj.left = left + 'px';
}

function fmSetDialogTop(y) {
	var hght = 0;
	var top = y ? y : fmMouseY;

	if(DOM) hght = fmObj.offsetHeight;
	else if(IE4) hght = fmSObj.pixelHeight;

	var winY = fmGetWindowHeight();
	var scrTop = fmGetScrollTop();
	if(top + hght - scrTop > winY) {
		if(hght > top) top = 0;
		else top -= hght;
	}

	fmSObj.top = top + 'px';
}

function fmOpenDialog(url, dialogId, text, fileId, name, perms, x, y) {
	var f, e, i, start, tObj;

	if(fmSObj && fmSObj.visibility == 'visible') fmSObj.visibility = 'hidden';

	fmObj = fmGetObj(dialogId);
	fmSObj = fmObj.style;

	if(f = document.forms[dialogId]) {
		f.reset();

		if(fileId && f.fmObject) f.fmObject.value = fileId;
		if(name && f.fmName) f.fmName.value = name;

		if(dialogId == 'fmNewFile') {
			for(i = 1; i < 10; i++) {
				if(f['fmFile[' + i + ']']) {
					f['fmFile[' + i + ']'].style.display = 'none';
				}
			}
			f.action = url;
			f.target = 'fmFileAction';
			f.onsubmit = function() {
				if(fmSObj) fmSObj.visibility = 'hidden';
				url.match(/fmCont(\d+)/);
				var cnt = RegExp.$1;
				var fmCont = fmGetObj('fmCont' + cnt);
				var listLoad = fmViewLoader(fmCont, url);
				fmIv = setInterval(fmCheckFile.bind(this, listLoad), 200);
			}
		}
		else f.action = "javascript:fmCall('" + url + "', '" + dialogId + "')";

		if(perms) {
			e = f.elements;
			for(i = start = 0; i < e.length && !start; i++) {
				if(e[i].type.toLowerCase() == 'checkbox') start = i;
			}
			for(i = 0; i < 9; i += 3) {
				e[start + i].checked = (perms.substr(i + 1, 1) == 'r') ? true : false;
				e[start + i + 1].checked = (perms.substr(i + 2, 1) == 'w') ? true : false;
				e[start + i + 2].checked = (perms.substr(i + 3, 1) == 'x') ? true : false;
			}
		}
	}

	if(text) {
		if(typeof(text) != 'object') text = [text];
		for(i = 0; i < text.length; i++) {
			tObj = fmGetObj(dialogId + 'Text' + (i ? i + 1 : ''));
			if(tObj && (DOM || IE4)) tObj.innerHTML = text[i];
		}
	}

	fmSetDialogLeft(x);
	fmSetDialogTop(y);
	fmFadeIn();
}

function fmFileInfo(cont, id, name, perms, owner, group, changed, size, thumb, width, height) {
	var html = '<table border="0" cellspacing="1" cellpadding="1"><tr align="left">' +
		'<td class="fmContent"><b>' + fmMsg['name'] + ':</b></td><td class="fmContent" nowrap>' + name + '</td>' +
		'</tr><tr align="left">' +
		'<td class="fmContent"><b>' + fmMsg['permissions'] + ':</b></td><td class="fmContent">' + perms + '</td>' +
		'</tr><tr align="left">' +
		'<td class="fmContent"><b>' + fmMsg['owner'] + ':</b></td><td class="fmContent">' + owner + '</td>' +
		'</tr><tr align="left">' +
		'<td class="fmContent"><b>' + fmMsg['group'] + ':</b></td><td class="fmContent">' + group + '</td>' +
		'</tr><tr align="left">' +
		'<td class="fmContent"><b>' + fmMsg['lastChange'] + ':</b></td><td class="fmContent" nowrap>' + changed + '</td>' +
		'</tr><tr align="left">' +
		'<td class="fmContent"><b>' + fmMsg['size'] + ':</b></td><td class="fmContent">' + size + ' B</td>' +
		((thumb && width > 0 && height > 0) ?
			'</tr><tr align="left"><td colspan="2" height="8"></td></tr><tr>' +
			'<td class="fmTD1" colspan="2" height="' + height + '" align="center" style="padding:5px; border:1px solid #E0E0E0">' +
			'<img src="' + fmWebPath + '/action.php?fmContainer=' + cont + '&fmMode=getThumbnail&fmObject=' + id +
			'&width=' + width + '&height=' + height + '&' + thumb + '" width="' + width + '" height="' + height + '"/></td>' : '') +
		'</tr></table>';

	fmOpenDialog(null, 'fmInfo', html);
}

//--------------------------------------------------------------------------------------------------------
// Add file selector
//--------------------------------------------------------------------------------------------------------

function fmNewFileSelector(cnt) {
	var f = document.forms.fmNewFile;
	if(f && f['fmFile['+cnt+']']) f['fmFile['+cnt+']'].style.display = 'block';
	fmSetDialogTop(fmObj.offsetTop);
}
