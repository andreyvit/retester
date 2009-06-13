SITE_ROOT = '';

function hide_details() {
	$('#sms-response').hide();
	$('#sms_details').hide();
}

function show_details(phone, fee, fee_curr, prefix) {
	$('#sms_phone').html(phone);
	$('#sms_price').html(fee + ' ' + fee_curr);
	$('#sms_prefix').html(prefix);
	$('#sms_details').show();
	$('#sms-response').show();
}

function countries_onchange(e) {
	$('#only_carrier').hide();
	var target = $('#countries');
	if (target.selectedIndex < 1) {
		selected_carrier_index = -1;
	  	$('#carriers_label').show();
	  	$('#carriers').show();
	  	$('#carriers').attr('disabled', 'disabled');
	  	hide_details();
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
		} else {
			$('#carriers')[0].innerHTML = carriers_html;
		}
	  	$('#carriers').removeAttr('disabled');
	  	hide_details();
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
	    	hide_details();
	    	selected_carrier_index = -1;
	    }
	}
}

function carriers_onchange() {
	var target = $('#carriers');
	if (target.val() < 1) {
	  	hide_details();
	  	selected_carrier_index = -1;
	  	selected_carrier_id = -1;
		return;
	}
	var carrier_index = target.val();
	var carrier = all_carriers[carrier_index];
	show_carrier_sms_details(carrier_index, carrier);
}

function sms_allocated(phone, fee, fee_curr, prefix) {
	show_details(phone, fee, fee_curr, prefix);
}

function show_carrier_sms_details(carrier_index, carrier) {
	selected_carrier_index = carrier_index;
	var phone_data = carrier.phones[0];
	$.ajax({
		'url': '/tests/' + $('#test_id').val() + '/allocate-sms',
		'data': { 'carrier_id' : carrier.id },
		'dataType': 'html',
		'beforeSend': function() { $('#sms_error, #sms_details').hide(); $('#sms_progress').show(); },
		'success': function(data) { eval(data); },
		'error': function() { $('#sms_error').html('Ошибка при загрузке.').show(); },
		'complete': function() { $('#sms_progress').hide(); }
	});
}

jQuery(function($) {
	hide_details();
	$('#sms_error, #sms_details, #sms_progress').hide();
	$('#countries').change(countries_onchange);
	$('#carriers').change(carriers_onchange);
	$('#countries').selectedIndex = 0;
	countries_onchange();
});
 