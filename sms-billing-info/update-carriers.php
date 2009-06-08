<?php

function add_tariffs(&$carriers){
  $f = file("tariffs.csv");

  $complains = array();
  $country = "";
  foreach($f as $line) {
    $args = explode(';', trim($line));
    if(count($args) == 2 && $args[1] == ''){
      $country = cut_parens($args[0]);
    }
    if(count($args) == 6 && $args[0] != 'Номер'){
      #print $country . ';' .  implode(';', $args) . "\n";
      $args[1] = cut_parens($args[1]);
      $key = $country . ';' . $args[1];
      if(array_key_exists($key, $carriers)){
        $carriers[$key]['phones'][$args[0]] = array(
          'phone' => $args[0],
          'paid' => preg_replace('/,/', '.', $args[2]),
          'paid_curr' => strtoupper($args[3]),
          'fee' => preg_replace('/,/', '.', $args[4]),
          'fee_curr' => strtoupper($args[5])
        );
      }else{
        if(!array_key_exists($key, $complains)){
          print "No id found for carrier: \"$key\" (phone: $args[0])\n";
          $complains[$key] = $complains;
        }
      }
    }
  }
  foreach($carriers as $key => $data) {
    if(!count($data['phones'])){
      print "No phones found for carrier: \"$key\"\n";
      unset($carriers[$key]);
    }
  }
}

function cut_parens($s){
  $s = reset(explode(' (', $s));
  $s = reset(explode('(', $s));
  return $s;
}

function get_carriers(){
  $f = file("carriers.csv");

  $carriers = array();

  foreach($f as $line) {
    $args = explode(',', trim($line));
    if(count($args) == 3 && $args[0] != 'id'){
      $args[1] = cut_parens($args[1]);
      $args[2] = cut_parens($args[2]);
      #print implode(';', $args) . "\n";
      $key = $args[2] . ';' . $args[1];
      #print "added carrier: \"$key\"\n";
      $carriers[$key] = array(
        'country' => $args[2],
        'carrier' => $args[1],
        'id' => $args[0],
        'phones' => array()
      );
    }
  }

  add_tariffs($carriers);
  
  return $carriers;
}

function save_array($f, $indent, $array){
  fwrite($f, "array(");
  $first = true;
  foreach($array as $k => $v){
    if($first){
      $first = false;
    }else{
      fwrite($f, ",");
    }
    fwrite($f, "\n");
    fwrite($f, $indent);
    if(is_array($v)){
      fwrite($f, "\"$k\" => ");
      save_array($f, $indent . "  ", $v);
    }else{
      fwrite($f, "\"$k\" => \"$v\"");
    }
  }
  fwrite($f, ")");
}

function update_js($carriers){
  $f = fopen("../javascripts/carriers.js", "wt");
  fwrite($f, "all_carriers = [\n");
  $first = true;
  sort($carriers);
  foreach($carriers as $k => $v){
    if($first){
      $first = false;
    }else{
      fwrite($f, ",");
    }
    fwrite($f, "\n");
    fwrite($f,"  {\n");
    $country = $v['country'];
    $carrier = $v['carrier'];
    $id = $v['id'];
    fwrite($f,"    \"country\": \"$country\",\n");
    fwrite($f,"    \"carrier\": \"$carrier\",\n");
    fwrite($f,"    \"id\":      \"$id\",\n");
    $first_phone = true;
    fwrite($f, "    \"phones\": [");
    foreach($v['phones'] as $phone_data) {
      if ($first_phone)
        $first_phone = false;
      else
        fwrite($f, ",");
      fwrite($f, "\n");
      $phone = $phone_data['phone'];
      $fee = $phone_data['fee'];
      $fee_curr = $phone_data['fee_curr'];
      fwrite($f, "     {\"phone\": \"$phone\",\n");
      fwrite($f, "      \"fee\": \"$fee\",\n");
      fwrite($f, "      \"fee_curr\": \"$fee_curr\"}");
    }
    fwrite($f, "\n");
    fwrite($f, "    ]\n");
    fwrite($f,"  }");
    
  }
  fwrite($f,"\n]\n");
}

function update_php($carriers){
  $f = fopen("carriers.php", "wt");
  fwrite($f, "<? \$carriers = ");
  save_array($f, "  ", $carriers);
  fwrite($f, "; ?>");
  fclose($f);
}

$carriers = get_carriers();

$idcarriers = array();
foreach($carriers as $key => $data) {
  $idcarriers[$data['id']] = $data;
}

update_js($idcarriers);
// update_php($idcarriers);

$cn = count($carriers);
$pn = 0;
foreach($carriers as $key => $data) {
  $pn += count($data['phones']);
}

print "Found $cn carriers, $pn carrier-phone pairs\n"

?>