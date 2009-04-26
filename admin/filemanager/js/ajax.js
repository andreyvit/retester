/*********************************************************************************************************
 This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
*********************************************************************************************************/

/*
  add bind method to functions so we can bind an object to a function
*/
Function.prototype.bind = function() {
	var _this = this, args = [], object = arguments[0];
	for(var i = 1; i < arguments.length; i++) args.push(arguments[i]);
	return function() {
		return _this.apply(object, args);
	}
}

function ajax() {

/* Member variables *******************************************************************/

	this.callBack = null;
	this.xmlPath = '';
	this.xmlDoc = null;
	this.request = null;

/* AJAX functions *********************************************************************/

	/*
	  url      = path to XML document, optional
	  callBack = callback function, optional
	  frmName  = form name, optional
	  async    = asynchronous mode (true (default) or false), optional
	*/
	this.makeRequest = function(url, callBack, frmName, async) {
		if(url) this.xmlPath = url;
		if(callBack) this.callBack = callBack;
		if(typeof(async) != 'boolean') async = true;

		if(this.xmlPath) {
			if(window.XMLHttpRequest) {
		        this.request = new XMLHttpRequest();

		        if(this.request.overrideMimeType) {
		            this.request.overrideMimeType('text/xml');
		        }
		    }
		    else if(window.ActiveXObject) {
		        try {
		            this.request = new ActiveXObject('Msxml2.XMLHTTP');
		        }
		        catch(e) {
		            try {
		                this.request = new ActiveXObject('Microsoft.XMLHTTP');
		            }
		            catch(e) {}
		        }
		    }
		    if(this.request) {
		    	var mt = (new Date()).getTime();
		    	url += ((url.indexOf('?') == -1) ? '?' : '&') + 'preventCache=' + mt;
			    if(async) this.request.onreadystatechange = this.requestHandler.bind(this);

			    if(frmName) {
			    	var param = this.getFormData(frmName);
				    this.request.open('POST', url, async);
				    this.request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			    	this.request.setRequestHeader('Connection', 'close');
				    this.request.send(param);
			    }
			    else {
			    	this.request.open('GET', url, async);
			    	this.request.setRequestHeader('Connection', 'close');
				    this.request.send(null);
			    }
			    if(!async) this.getResponse();
		    }
		}
	}

	this.requestHandler = function() {
	    if(this.request && this.request.readyState == 4) {
			switch(this.request.status) {
				case 200:
					this.getResponse();
					return true;
				case 12029:
				case 12030:
				case 12031:
				case 12152:
				case 12159:
					this.request.onreadystatechange = function() {};
					this.makeRequest();
					return false;
				default:
					this.xmlDoc = null;
					alert('Server returned ' + this.request.status);
					return false;
			}
	    }
	}

	this.getResponse = function() {
		this.xmlDoc = this.request.responseXML;

		if(typeof(this.callBack) == 'function') {
			this.callBack();
		}
	}

/* Retrieve form data *****************************************************************/

	/*
	  frmName      = from name
	  omitDisabled = omit disabled fields (true or false)
	  omitContent  = omit values with this content, optional
	  omitField    = omit fields if name starts with this string, optional
	  cache        = cached selection (array), optional
	*/
	this.getFormData = function(frmName, omitDisabled, omitContent, omitField, cache) {
		var param = '';
		var f = document.forms[frmName];

		if(f) {
			var elem, val, ind;

			for(var i = 0; i < f.elements.length; i++) {
				elem = f.elements[i];
				if(cache && cache[elem.name]) val = '' + cache[elem.name];
				else val = this.getFieldValue(elem);

				if(val) {
					if(!omitDisabled || !elem.disabled) {
						if(!omitContent || val != omitContent) {
							ind = elem.name.indexOf(omitField);

							if(!omitField || ind == -1 || ind > 0) {
								val = val.replace(/^\s+/, '');
								val = val.replace(/\s+$/, '');
								if(param) param += '&';
								param += elem.name + '=' + escape(val);
							}
						}
					}
				}
			}
		}
		return param;
	}

	/*
	  field = form field (object)
	*/
	this.getFieldValue = function(field) {
		var val = '';

		switch(field.type.toLowerCase()) {
			case 'text':
			case 'textarea':
			case 'hidden':
			case 'password':
				val = field.value;
				break;
			case 'select-one':
			case 'select-multiple':
				val = field.options[field.selectedIndex].value;
				break;
			case 'checkbox':
			case 'radio':
				val = field.checked ? field.value : '';
				break;
		}
		return val;
	}
}