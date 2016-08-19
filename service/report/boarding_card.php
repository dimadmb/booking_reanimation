<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";

function SaveViaTempFile($objWriter){
	$filePath =  $_SERVER['DOCUMENT_ROOT'] . '/tmp/' . rand(0, getrandmax()) . rand(0, getrandmax()) . ".tmp";
	$objWriter->save($filePath);
	readfile($filePath);
	unlink($filePath);
}

ini_set('display_errors',0);
$db = mysqli2::connect();

$hashids_place = new Hashids\Hashids(config::$place_salt,32);

$place_id = $_GET['place'];
$place_id = $hashids_place->decode($place_id);

if (count($place_id)==0){ exit;}
$place_id = $place_id[0];

$sql = "select \n

aa_schet.id as id_schet,
aa_tur.id as id_tur,
aa_tur.name as way,
date_format(birthday,'%d.%m.%Y') as birthday2,
date_format(pass_date,'%d.%m.%Y') as pass_date2,
date_format(date_start,'%d.%m.%Y') as d1,
date_format(date_stop,'%d.%m.%Y') as d2,
aa_place.*, aa_order.*,  aa_teplohod.name as teplohod from \n

aa_place,aa_order,aa_tur,aa_teplohod, aa_schet where \n

aa_place.id = $place_id and \n

aa_place.id_order = aa_order.id and \n

aa_order.id_schet = aa_schet.id and \n

aa_schet.id_tur = aa_tur.id and \n

aa_tur.id_teplohod = aa_teplohod.id

";


$res = $db->query($sql);

$r = $res->fetch_assoc();





$objPHPExcel = PHPExcel_IOFactory::load("boarding_card.xlsx");

$objPHPExcel->setActiveSheetIndex(0);
$aSheet = $objPHPExcel->getActiveSheet();

$dataTur = tur::getData($r['id_tur']);

$dataAPI = tur::getDataAPI($r['id_tur']);

$last_point = $dataAPI[ count($dataAPI) - 1];

$port1  =$dataAPI[0]->port;
$port2  =$last_point->port;

$time1 = substr($dataAPI[0]->time_stop ,0,5);
$time2 = substr($last_point->time_start ,0,5);

if ($r['id_tur'] == 9000){
	$time1='10:00';
	$time2='20:00';
}
if ($r['id_tur'] == 9001){
	$time1='12:30';
	$time2='20:00';
}
if ($r['id_tur'] == 9002){
	$time1='17:30';
	$time2='20:00';
}

$hour = substr($time1,0,2) - 2;
$time_reg =  $hour . substr($time1,2);

$date1 = $dataTur['d1'];
$date2 = $dataTur['d2'];

$aSheet->setCellValue(   'D12', $date1 .' '. $time_reg );
$aSheet->setCellValue(   'J12', $date1 .' '. $time1 );

$aSheet->setCellValue(   'D14', $time1 );
$aSheet->setCellValue(   'D15', $port1 );

$aSheet->setCellValue(   'J14', $port2 );


$dataPrices = tur::getPrices($r['id_tur']);

$index = $dataPrices['kautas'][$r['num']][0];

$deck = $dataPrices['tariffs'][0]->prices[$index]->deck_name;
$class = $dataPrices['tariffs'][0]->prices[$index]->rt_name;


$aSheet->setCellValue(   'D16', $deck );
$aSheet->setCellValue(   'D17', $class );


$aSheet->setCellValue(   'A7', "номер договора ". $r['id_schet'] );

$aSheet->setCellValue(   'D10', $r['teplohod'] );

$aSheet->setCellValue(   'D11', $r['way'] );
$aSheet->setCellValue(   'D13', $r['d1'] );
$aSheet->setCellValue(   'J13', $r['d2'] . ' '. $time2 );

$aSheet->setCellValue(   'J15', $r['num'] );

$aSheet->setCellValue(   'J16', $r['places'] .'-местное размещение' );

$aSheet->setCellValue(   'G18', $r['surname'] .' '. $r['name'] . ' '. $r['patronymic']. ', дата рождения: '. $r['birthday2'] .' г' );


$aSheet->setCellValue(   'G19',
	'Паспорт РФ, серия '.
	$r['pass_seria']
	.', номер '.$r['pass_num'].', выдан '.$r['pass_date2'].' г, кем выдан: '.$r['pass_who']
);


$sql = "select * from aa_buyer_fiz where id_schet = " . $r['id_schet'] . " limit 1";
$res_buyer = $db->query($sql);
$r_buyer = $res_buyer->fetch_assoc();

$aSheet->setCellValue(   'D32', $r_buyer['surname'] .' '. $r_buyer['name'] . ' '. $r_buyer['patronymic'] );
$aSheet->setCellValue(   'D33', $r_buyer['phone'] );

//создаем объект класса-писателя
//require_once './../../excelphp/Classes/PHPExcel/Writer/Excel5.php';

//exit;

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//выводим заголовки
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Посадочный талон.xlsx"');
header('Cache-Control: max-age=0');
//выводим в браузер таблицу с бланком

//$objWriter->save('php://output');
SaveViaTempFile($objWriter);
