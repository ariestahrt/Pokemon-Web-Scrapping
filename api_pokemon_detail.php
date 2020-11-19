<?php

// error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include_once('simple_html_dom.php');

$is_alt_name = false;
$alt_class_tag = "";
$pokemon_name = isset($_GET['name']) ? $_GET['name'] : "Charizard";
$alt_name = "";

if(isset($_GET['alt_name'])){
    if($_GET['alt_name'] == 'no_alt_name') $is_alt_name = false;
    else $is_alt_name = true;
}

if($is_alt_name) $alt_name = $_GET['alt_name'];

// $is_alt_name = true;
// $alt_name = "Mega Charizard X";

function getStr($string, $start, $end) {
    $str = explode($start, $string);
    $str = explode($end, $str[1]);
    return $str[0];
}

function inStr($string, $find){ // not CS
    if (strpos(strtolower($string), strtolower($find)) !== false) {
        return true;
    }else{
        return false;
    }
}

function getInnerText($element){
    return getStr($element, '>', '<');
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://pokemondb.net/pokedex/'.strtolower($pokemon_name));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);


$result = curl_exec($ch);

$html = new simple_html_dom();
$html->load($result);

$Pokemon = array();

// Get Tab
if($is_alt_name){
    $tab_list = $html->find('div[class=tabs-tab-list]', 0);
    $alt_class_tag = "";
    foreach($tab_list->find('a') as $tab_name){
        // echo $tab_name."\n";
        if(inStr($tab_name, $alt_name)){
            $alt_class_tag = $tab_name->href;
            break;
        }
    }
}

// Get Pokemon Image
if($is_alt_name){
    $image = $html->find($alt_class_tag, 0);
    $image = getStr(strval($image), '<img src="', '"');
    $Pokemon["image"] = $image;
}else{
    $image = $html->find('.grid-col.span-md-6.span-lg-4', 1);
    $Pokemon["image"] = $image->children(0)->children(0)->children(0)->src;
}

// Get Pokedex data of pokemon

if($is_alt_name){
    $vitalstable = $html->find($alt_class_tag, 0)->find('table[class=vitals-table]', 0);
}else{
    $vitalstable = $html->find('table[class=vitals-table]', 0);
}

$vitalstabletr = $vitalstable->children(0);
$Pokemon["national_number"] = getStr($vitalstabletr->children(0), '<strong>', "<");
$Pokemon["type"] = [];
foreach($vitalstabletr->children(1)->find('a') as $type) array_push($Pokemon["type"], getInnerText($type));
$Pokemon["species"] = getStr($vitalstabletr->children(2), '<td>', "<");
$Pokemon["height"] = getStr($vitalstabletr->children(3), '<td>', "<");
$Pokemon["weight"] = getStr($vitalstabletr->children(4), '<td>', "<");
$Pokemon["abilities"] = [];
foreach($vitalstabletr->children(5)->find('a') as $ability) array_push($Pokemon["abilities"], getInnerText($ability));


// Get Training data of pokemon

if($is_alt_name){
    $vitalstable = $html->find($alt_class_tag, 0)->find('table[class=vitals-table]', 1);
}else{
    $vitalstable = $html->find('table[class=vitals-table]', 1);
}

$vitalstabletr = $vitalstable->children(0);
$Pokemon["catch_rate"] = array(
    "Percentage" => getStr($vitalstabletr->children(1), '<td>', "<"),
    "Detail" => getStr($vitalstabletr->children(1), '<small class="text-muted">', "<")
);
$Pokemon["growth_rate"] = getStr($vitalstabletr->children(4), '<td>', "<");

// Get Base Stats of pokemon

if($is_alt_name){
    $vitalstable = $html->find($alt_class_tag, 0)->find('table[class=vitals-table]', 3);
}else{
    $vitalstable = $html->find('table[class=vitals-table]', 3);
}

$vitalstabletr = $vitalstable->children(0);

$Pokemon["hp"] = array(
    "current" => getInnerText($vitalstabletr->children(0)->children(1)), 
    "min" => getInnerText($vitalstabletr->children(0)->children(3)), 
    "max" => getInnerText($vitalstabletr->children(0)->children(4)), 
);
$Pokemon["attack"] = array(
    "current" => getInnerText($vitalstabletr->children(1)->children(1)), 
    "min" => getInnerText($vitalstabletr->children(1)->children(3)), 
    "max" => getInnerText($vitalstabletr->children(1)->children(4)), 
);
$Pokemon["defense"] = array(
    "current" => getInnerText($vitalstabletr->children(2)->children(1)), 
    "min" => getInnerText($vitalstabletr->children(2)->children(3)), 
    "max" => getInnerText($vitalstabletr->children(2)->children(4)), 
);
$Pokemon["sp_attack"] = array(
    "current" => getInnerText($vitalstabletr->children(3)->children(1)), 
    "min" => getInnerText($vitalstabletr->children(3)->children(3)), 
    "max" => getInnerText($vitalstabletr->children(3)->children(4)), 
);
$Pokemon["sp_defense"] = array(
    "current" => getInnerText($vitalstabletr->children(4)->children(1)), 
    "min" => getInnerText($vitalstabletr->children(4)->children(3)), 
    "max" => getInnerText($vitalstabletr->children(4)->children(4)), 
);
$Pokemon["speed"] = array(
    "current" => getInnerText($vitalstabletr->children(5)->children(1)), 
    "min" => getInnerText($vitalstabletr->children(5)->children(3)), 
    "max" => getInnerText($vitalstabletr->children(5)->children(4)), 
);

$Pokemon["Stats_Total"] = getStr($vitalstable, '<td class="cell-total"><b>', '<');

// Get Evolution data of pokemon
if($html->find('.infocard-list-evo', 0)) {
    // $selected_evo_element=$html->find('div[class=infocard-list-evo]', 0); // default
    $evo_element = "";
    foreach($html->find('div[class=infocard-list-evo]') as $evo){
        $evo_element .= strval($evo);
        $evo_html = new simple_html_dom();
        $evo_html->load($evo);
        $evo = $evo_html->find('a')->href;
        // $evo = $evo->find('a')->href;
        // print_r(gettype($evo));
        echo strval($evo);
    }
    $Pokemon["HasEvolution"] = true;
    $Pokemon["Evolution"] = strval(str_replace("<span class=\"img-fixed img-sprite\" data-","<img ", $evo_element));
} else {
    $Pokemon["HasEvolution"] = false;
}

// print_r($Pokemon);

$json = json_encode($Pokemon);
// echo $json;

?>