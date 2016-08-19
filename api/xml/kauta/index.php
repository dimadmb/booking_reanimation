<?php

if (!isset($_GET['cruise'])) {

	$id_tur = 0;
} else {
	$id_tur = (int)$_GET['cruise'];
}


if ($id_tur == 0) {

	header("Content-Type: text/html;charset=utf-8");
	?>
	<h1>Неверный параметр "<b>cruise</b>"</h1>
	<p>Правильный пример

		<a href="/xml/kauta?cruise=3339">http://<?= $_SERVER['HTTP_HOST'] ?>/xml/kauta?cruise=3339</a>
	</p>
	<?php

	exit;
}



require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";


$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
<root  xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></root>');


$db = mysqli2::connect();
$sql = "Select aa_kauta.* from aa_kauta,aa_tur where  \n
aa_kauta.id_teplohod = aa_tur.id_teplohod and aa_tur.id=$id_tur order by num";

$res = $db->query($sql);

if ($res->num_rows==0){

	header("Content-Type: text/html;charset=utf-8");
	?>
	<h1>Нет кают!</h1>

	<?php

	exit;

}

header("Content-Type: text/xml;charset=utf-8");


$url_key = "?pauth=" . config::$keyVodohod;
$url_teplohod = "http://cruises.vodohod.com/agency/json-prices.htm";

$url = $url_teplohod . $url_key . "&cruise=$id_tur";

$data = file_get_contents($url);
$data = json_decode($data);

$room_all = $data->room_all;
$tariffs = $data->tariffs;

$kautas = array();

$nums15 = array();
$sql15 = "select num,type_discount from aa_discount where id_tur=$id_tur ";

$res15 = $db->query($sql15);

while ($r = $res15->fetch_assoc()){
	$nums15[ $r['num'] ] = $r['type_discount'];
}

foreach ($room_all as $id_price => $rooms) {
	foreach ($rooms as $room) {
		$kautas[$room][] = $id_price;
	}
}

$tarif_name = array(0 => 'price_adult', 'price_child', 'price_old_age','price_school');

ksort($kautas);


$sql = "select * from aa_tur where id= $id_tur limit 1";
$res_tur= $db->query($sql);
$tur = $res_tur->fetch_assoc();

foreach ($kautas as $num => $id_prices) {

	$prices = $tariffs[0]->prices;
	$data = $prices[$id_prices[0]];


	$kauta = $xml->addChild('kauta');
	$kauta->addAttribute('number', $num);

	$xml_price = $kauta->addChild('price');

	$category = array();

	foreach ($id_prices as $id) {

		foreach ($tariffs as $key => $tariff) {
			$prices = $tariff->prices;
			$data = $prices[$id];

			if (strpos($data->rp_name, '1-') !== false) {
				$category[1][$key] = $data;
			} else if (strpos($data->rp_name, '2-') !== false) {
				$category[2][$key] = $data;
			} else if (strpos($data->rp_name, '3-') !== false) {
				$category[3][$key] = $data;
			} else if (strpos($data->rp_name, '4-') !== false) {
				$category[4][$key] = $data;
			}
		}
	}


	for ($i = 1; $i <= 4; $i++) {

		if (isset($category[$i])) {


			$place = $xml_price->addChild("place");
			$place->addAttribute('count_place',$i);

			foreach( $category[$i] as $tarif_index => $cat){

				$price = $cat->price_value;

				if ($tur['type_discount']=='happy' && $price>0
					&& (isset($nums15[$num]))	&& $nums15[$num]=='happy'){
					$price = round($price * 0.8);

				} else if ($tur['type_discount']=='special' && $price>0
						&& (isset($nums15[$num])) && $nums15[$num]=='special'){
					$price = round($price * 0.9);
				}

				$place->addAttribute($tarif_name[$tarif_index],$price);


			}







		}
	}
}


echo $xml->asXML();