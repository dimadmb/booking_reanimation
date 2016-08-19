<?php
header("Content-Type: text/html;charset=utf-8");
ini_set('display_errors',0);

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/invoice.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/num2str.php";

$hashids_schet = new Hashids\Hashids(config::$schet_salt,32);

$month  = array (1 =>"января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря");



$hash = $_GET['hash'];

$id = $hashids_schet->decode($hash);

if (count($id)==0){ header('Location: /');}

$id = $id[0];


if (user::is_fiz()){
	if (!invoice::checkFiz($id)){
		header("Location: /invoice/$hash");
		return false;
	}
}

$filename = $_SERVER['DOCUMENT_ROOT'] . "/service/report/contract.docx";

$new_name = "/tmp/contract_$hash.docx";
//$new_name = $_SERVER['DOCUMENT_ROOT'] . "/tmp/contract_$hash.docx";

$invoiceData = invoice::getData($id);

$db = mysqli2::connect();
$sql = "select *,

 date_format(pass_date ,'%d.%m.%Y') as pass_date2 \n

 from aa_buyer_fiz where id_schet = $id limit 1";
$res = $db->query($sql);
$r = $res->fetch_assoc();


$replace = array();

$replace['ID'] = $id;

$replace['DAY'] = $invoiceData['dd'];
$replace['MONTH'] = $month[$invoiceData['mm']];
$replace['YEAR'] = $invoiceData['yy'];

$replace['FIO'] = $r['surname'] .' '. $r['name'] . ' ' . $r['patronymic'];

$replace['ADDRESS'] = $r['address'];

$replace['PASSPORT'] = $r['pass_seria'] .' № '. $r['pass_num'] .', выдан '.

	$r['pass_who'] .' ' . $r['pass_date2'] .'г.';


$replace['PHONE'] = $r['phone'];


$invoiceData = invoice::getData($id);

$dataAPI = tur::getDataAPI($invoiceData['id_tur']);
$dataTur = tur::getData($invoiceData['id_tur']);

$last_point = $dataAPI[ count($dataAPI) - 1];

$port1  =$dataAPI[0]->port;
$port2  =$last_point->port;

$time1 = substr($dataAPI[0]->time_stop ,0,5);
$time2 = substr($last_point->time_start ,0,5);

$replace['WAY'] = $dataTur['name'];
$replace['TEPLOHOD'] = $dataTur['teplohod_name'];

$replace['PORT1'] = $port1;
$replace['PORT2'] = $port2;

$replace['TIME1'] = $time1;

$hour = substr($time1,0,2) - 2;
$replace['TIME_REG'] =  $hour . substr($time1,2);

$replace['TIME2'] = $time2;

$replace['DATE1'] = $dataTur['d1'];
$replace['DATE2'] = $dataTur['d2'];

$replace['DAYS'] = $dataTur['days'];
$replace['DAYS_1'] = $dataTur['days']-1;


if (!isset($filename)){ exit('no template');}

$phpWord = new \PhpOffice\PhpWord\TemplateProcessor($filename);

foreach ($replace as $key => $item){
	$phpWord->setValue($key, $item);
}


//заполняем пассажиров

$sql = "select aa_place.* , aa_order.num from aa_schet,aa_order,aa_place where \n

aa_schet.id = $id and aa_schet.id = aa_order.id_schet and aa_order.is_delete=0 and \n
aa_place.id_order = aa_order.id
";
//echo $sql;

$places = mysqli2::sql2array($sql);

$phpWord->cloneRow('NUM', count($places));

$i=1;
$sum = 0;

$dataPrices = tur::getPrices($invoiceData['id_tur']);

//helper::var_dump_pre($dataPrices);

foreach ($places as $place){

	$phpWord->setValue("NUM#$i", $place['num']);

	$phpWord->setValue("SUM#$i", $place['price']);

	$index = $dataPrices['kautas'][$place['num']][0];

	$deck = $dataPrices['tariffs'][0]->prices[$index]->deck_name;

	$class = $dataPrices['tariffs'][0]->prices[$index]->rt_name;

	$phpWord->setValue("CLASS#$i", $class);
	$phpWord->setValue("DECK#$i", $deck);

	$phpWord->setValue("PASS#$i", $place['surname'] .' '. $place['name']. ' '. $place['patronymic']);

	$sum += $place['price'];
	$i++;
}


//exit;

$phpWord->setValue("SUM_ALL", $sum );
$phpWord->setValue("SUM_ALL_PROPIS", num2str($sum) );


header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment;filename="Договор.docx"');
//header('Cache-Control: max-age=0');

$phpWord->saveAs($_SERVER['DOCUMENT_ROOT'] . $new_name);
header("Location: $new_name");