SITE_ROOT = '';
function choice_value(opt){
  if(opt.value){
    return opt.value;
  }else{
    return opt.text;
  }
}

function selected_value(sel){
  return choice_value(sel[sel.selectedIndex]);
}

function countries_onchange(e) {
	$('#only_carrier').hide();
	var target = $('#countries');
	if (target.selectedIndex < 1) {
	  selected_carrier_index = -1;
  	$('#carriers_label').show();
  	$('#carriers').show();
  	$('#carriers').attr('disabled', 'disabled');
  	$('#sms_details').hide();
		return;
	}
	var country = $(':selected', target).text();
	var carriers_html = "<option value=\"\">--выберите вашего оператора--</option>";
	var option_count = 0;
	var first_carrier, first_carrier_index;
	var text = "";
	for(var i in all_carriers) {
		var carrier = all_carriers[i];
		text += carrier.country + " ";
		if (carrier.country == country) {
  		carriers_html += '<option value=\"' + i + '\">' + carrier.carrier + '</option>';
  		first_carrier = carrier;
  		first_carrier_index = i;
    	option_count++;
  	}
	}
	carriers_html += '</select>';
	if (option_count > 1) {
	  selected_carrier_index = -1;
  	$('#carriers_label').show();
  	$('#carriers').show();
  	if($('#carriers').outerHTML){ // For IE
  	  var select = "<select id=\"carriers\" name=\"carrier\" style=\"width: 200px;\" onchange='carriers_onchange()'>";
  	  $('#carriers').outerHTML = select + carriers_html + "</select>";
	  }else{
	    $('#carriers')[0].innerHTML = carriers_html;
    }
  	$('#carriers').removeAttr('disabled');
  	$('#sms_details').hide();
	} else {
  	$('#carriers').hide();
  	$('#carriers').attr('disabled', 'disabled');
	  if (option_count > 0) {
    	$('#carriers_label').show();
    	$('#only_carrier')[0].innerHTML = '<strong>&nbsp;'+first_carrier.carrier+'</strong>';
    	$('#only_carrier').show();
      show_carrier_sms_details(first_carrier_index, first_carrier);
      selected_carrier_index = first_carrier_index;
    } else {
    	$('#sms_details').hide();
    	selected_carrier_index = -1;
    }
	}
}

function is_checked(x) {
  if(x.checked == true) return true;
  if(x.value == true) return true;
  return false;
}

function confirm_go_forward() {
  if(!check_submit()) return false;
  if(!is_checked($('#id_policy'))){
    alert('Вы не согласились с правилами использования.');
    return false;
  }
  return submit_wmid();
}

function confirm_go_to() {
    if(!check_submit()) return;
    var wmid = $('#wmid').value;
    var url = SITE_ROOT + 'getwmid.php?purse=' + escape(wmid);
    purses_executor.set_requestor(YourSway.AjaxRequestor(url));
    purses_executor.schedule_request();
}

function go_to_confirm(wm) {
    if(wm == ''){
        alert('Purse is incorrect. Type another one.');
        return confirm_go_back();
    }
    var wmid = $('#wmid').value;
    var carrier = all_carriers[selected_carrier_index];
    $('#id_confirm_wm')[0].innerHTML = wmid;
    $('#id_confirm_source')[0].innerHTML = wm;
    $('#id_confirm_country')[0].innerHTML = carrier.country;
    $('#id_confirm_carrier')[0].innerHTML = carrier.carrier;
    $('#id_form_wm').hide();
    $('#id_form_confirm').show();
}

function confirm_go_back() {
  $('#id_form_wm').show();
  $('#id_form_confirm').hide();
}

function carriers_onchange() {
	var target = $('#carriers');
	if (target.val() < 1) {
  	$('#sms_details').hide();
  	selected_carrier_index = -1;
  	selected_carrier_id = -1;
		return;
	}
  var carrier_index = target.val();
  var carrier = all_carriers[carrier_index];
  show_carrier_sms_details(carrier_index, carrier);
}

function show_carrier_sms_details(carrier_index, carrier) {
  selected_carrier_index = carrier_index;
  var phone_data = carrier.phones[0];
  $('#sms_phone')[0].innerHTML = phone_data.phone;
  $('#sms_price')[0].innerHTML = phone_data.fee + ' ' + phone_data.fee_curr;
  // $('#sms_prefix')[0].innerHTML = ;
  $('#sms_details').show();
}

function check_submit(){
	var wmid = $('#wmid').value;
	if (wmid == '' || wmid.length != 13) {
	  alert('Пожалуйста, введите номер кошелька.');
	  return false;
  }
  if (selected_carrier_index < 0) {
    alert('Пожалуйста, выберите страну и оператора.');
    return false;
  }
  return true;
}

function submit_wmid() {
	var wmid = $('#wmid').value;
	var country = selected_value($('#countries'));
	var carrier = selected_value($('#carriers'));
	carrier_id = all_carriers[selected_carrier_index].id;
	var url = SITE_ROOT + 'allocatesms.php?wmid=' + escape(wmid) + '&carrier=' + carrier_id;
	wmid_executor.set_requestor(YourSway.AjaxRequestor(url));
	wmid_executor.schedule_request();
	return true;
}

function submit_transfer() {
    var password = $('#password').value;
    var url = SITE_ROOT + 'transfer.php?password=' + escape(password);
    transfer_executor.set_requestor(YourSway.AjaxRequestor(url));
    transfer_executor.schedule_request();
    return false;
}

jQuery(function($) {
  $('#countries').change(countries_onchange);
  $('#carriers').change(carriers_onchange);
  $('#countries').selectedIndex = 0;
  countries_onchange();
});
