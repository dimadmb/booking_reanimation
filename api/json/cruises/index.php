<?php
//header("Content-Type: text/xml;charset=utf-8");

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";

$db = mysqli2::connect();

$turs= tur::getListTurs();


$arr_json = array();


foreach ($turs as $tur){

	

	 


	$tur['is_happy'] = 0;
	$tur['is_special'] =0;

	if ($tur['type_discount'] =='happy') {$tur['is_happy'] = 1;}
	else if  ($tur['type_discount'] =='special') {$tur['is_special'] = 1;}


	$arr_json[$tur['id']]['is_happy'] =  $tur['is_happy'];
	$arr_json[$tur['id']]['is_special'] = $tur['is_special'];


	$arr_json[$tur['id']]['date_start'] =  $tur['date_start2'];
	$arr_json[$tur['id']]['date_stop'] = $tur['date_stop2'];
	$arr_json[$tur['id']]['days'] = $tur['days'];

	$$arr_json[$tur['id']]['ship'] = $tur['teplohod'];
	$arr_json[$tur['id']]['ship_id'] = $tur['id_teplohod'];

	$arr_json[$tur['id']]['route'] = $tur['name'];



}


echo json_encode($arr_json);

