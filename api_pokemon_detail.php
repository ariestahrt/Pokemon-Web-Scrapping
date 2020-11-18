<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include_once('simple_html_dom.php');

$pokemon_name = isset($_GET['name']) ? $_GET['name'] : "bulbasaur";

function getStr($string, $start, $end) {
    $str = explode($start, $string);
    $str = explode($end, $str[1]);
    return $str[0];
}

function getInnerText($element){
    return getStr($element, '>', '<');
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://pokemondb.net/pokedex/'.$pokemon_name);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);

$html = new simple_html_dom();
$html->load($result);

$Pokemon = array();

// Get Pokemon Image
$image = $html->find('.grid-col.span-md-6.span-lg-4', 1);
$Pokemon["image"] = $image->children(0)->children(0)->children(0)->src;

// Get Pokedex data of pokemon
$vitalstable = $html->find('table[class=vitals-table]', 0);
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
$vitalstable = $html->find('table[class=vitals-table]', 1);
$vitalstabletr = $vitalstable->children(0);
$Pokemon["catch_rate"] = array(
    "Percentage" => getStr($vitalstabletr->children(1), '<td>', "<"),
    "Detail" => getStr($vitalstabletr->children(1), '<small class="text-muted">', "<")
);
$Pokemon["growth_rate"] = getStr($vitalstabletr->children(4), '<td>', "<");

// Get Base Stats of pokemon
$vitalstable = $html->find('table[class=vitals-table]', 3);
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

if($html->find('.infocard-list-evo', 0)) {
    $Pokemon["HasEvolution"] = true;
    $Pokemon["Evolution"] = [];

    $counter = 0;
    foreach($html->find('div[class=infocard]') as $evolution){
        $Pokemon_Type = [];
        foreach($evolution->find('small', 1)->find('a') as $type){
            array_push($Pokemon_Type, getInnerText($type));
        }

        if($html->find('span[class=infocard infocard-arrow]', $counter)){
            $next_lvl_evo = $html->find('span[class=infocard infocard-arrow]', $counter);
            $next_lvl_evo = getStr($next_lvl_evo, '<small>', '<');    
        }else{
            $next_lvl_evo = "Maks Evolution";
        }

        array_push($Pokemon["Evolution"], array(
            "Name" => getStr(explode('<a class="ent-name"', $evolution)[1], '>', '<'),
            "Image_Url" => getStr($evolution, 'data-src="', '"'),
            "National_Number" => getStr($evolution, '<span class="infocard-lg-data text-muted"><small>', '<'),
            "Type" => $Pokemon_Type,
            "next_lvl_evo" => $next_lvl_evo
        ));

        $counter++;
    }
} else {
    $Pokemon["HasEvolution"] = false;
}

// print_r($Pokemon);

$json = json_encode($Pokemon);
echo $json;

?>