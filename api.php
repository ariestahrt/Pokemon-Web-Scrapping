<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include_once('simple_html_dom.php');
function getStr($string, $start, $end) {
    $str = explode($start, $string);
    $str = explode($end, $str[1]);
    return $str[0];
}

function getInnerText($element){
    return getStr($element, '>', '<');
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://pokemondb.net/pokedex/all');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);

$html = new simple_html_dom();
$html->load($result);

$tableContent = $html->find("tbody");
$tableContent = $tableContent[0];

$Pokemon_List = array();

foreach($tableContent->find('tr') as $element){

    if($element->find('td[class=cell-name]',0)->find('small')){
        $alt_name = getInnerText($element->find('td[class=cell-name]',0)->find('small', 0));
    }else{
        $alt_name = null;
    }

    $Pokemon_Type = [];
    foreach($element->find('td[class=cell-icon]')[0]->find('a') as $type){
        $type = getStr($type, '>', '<');
        array_push($Pokemon_Type, $type);
    }

    $Pokemon = array(
        "IMG" => getStr($element, 'data-src="', '"'),
        "ID" => getInnerText($element->find('span[class=infocard-cell-data]')[0]),
        "NAME" => getInnerText($element->find('a[class=ent-name]')[0]),
        "ALT_NAME" => $alt_name,
        "TYPE" => $Pokemon_Type,
        "TOTAL" => getInnerText($element->find('td[class=cell-total]')[0]),
        "HP" => getInnerText($element->find('td[class=cell-num]')[0]),
        "ATTACK" => getInnerText($element->find('td[class=cell-num]')[1]),
        "DEFENSE" => getInnerText($element->find('td[class=cell-num]')[2]),
        "SP_ATTACK" => getInnerText($element->find('td[class=cell-num]')[3]),
        "SP_DEFENSE" => getInnerText($element->find('td[class=cell-num]')[4]),
        "SPEED" => getInnerText($element->find('td[class=cell-num]')[5]),
    );

    array_push($Pokemon_List, $Pokemon);

    // print_r($Pokemon_List);
}

$json = json_encode($Pokemon_List);
echo $json;

?>