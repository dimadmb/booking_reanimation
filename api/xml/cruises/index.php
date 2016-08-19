<?php
header("Content-Type: text/xml;charset=utf-8");

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";

$db = mysqli2::connect();

$turs= tur::getListTurs();


$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
<root  xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></root>');


foreach ($turs as $tur){

	$cruise = $xml->addChild('cruise');

	$cruise->addAttribute('id', $tur['id']);


	$tur['is_happy'] = 0;
	$tur['is_special'] =0;

	if ($tur['type_discount'] =='happy') {$tur['is_happy'] = 1;}
	else if  ($tur['type_discount'] =='special') {$tur['is_special'] = 1;}


	$cruise->addAttribute('is_happy', $tur['is_happy']);
	$cruise->addAttribute('is_special', $tur['is_special']);


	$cruise->addAttribute('date_start', $tur['date_start2']);
	$cruise->addAttribute('date_stop', $tur['date_stop2']);
	$cruise->addAttribute('days', $tur['days']);

	$cruise->addAttribute('ship', $tur['teplohod']);
	$cruise->addAttribute('ship_id', $tur['id_teplohod']);

	$cruise->addAttribute('route', $tur['name']);



}


echo $xml->asXML();

