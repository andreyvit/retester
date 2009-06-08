<?php

global $countries, $js_carriers;

$js_carriers = file_get_contents(dirname(__FILE__).'/../javascripts/carriers.js');
$js_carriers = str_replace('all_carriers = [', '[', $js_carriers);
$js_carriers = str_replace('];', ']', $js_carriers);
$js_carriers = json_decode($js_carriers);

$countries = array();
foreach($js_carriers as $carrier) {
  $countries[] = $carrier->country;
}
$countries = array_unique($countries);

function echo_country_options() {
  global $countries;
  foreach($countries as $country) {
    $country = htmlspecialchars($country);
  	echo sprintf('<option value="%s">%s</option>', $country, $country);
	}
}
?>
