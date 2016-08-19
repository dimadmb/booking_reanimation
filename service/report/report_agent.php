<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";


require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/num2str.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";
function SaveViaTempFile($objWriter){
	$filePath =  $_SERVER['DOCUMENT_ROOT'] . '/tmp/' . rand(0, getrandmax()) . rand(0, getrandmax()) . ".tmp";
	$objWriter->save($filePath);
	readfile($filePath);
	unlink($filePath);
}

ini_set('display_errors',1);

$period = $_POST['period'];

if (strlen($period) > 7) { header('Location: /');}

$hash = $_POST['hash'];
$hashids_user = new Hashids\Hashids(config::$user_salt,32);
$id = $hashids_user->decode($hash);
if (count($id)==0){ header('Location: /');}
$id = $id[0];

$period = explode('.' , $period);


$d1 = " 1 " .  config::$month[ $period[0] ] . " ".$period[1]."г. ";


$last_day = date('t',mktime(0, 0, 0, $period[0], 1, $period[1]));

$d2 =" $last_day " . config::$month[ $period[0] ] . " ".$period[1]."г. ";



$objPHPExcel = PHPExcel_IOFactory::load("report_agent.xlsx");

$objPHPExcel->setActiveSheetIndex(0);
$aSheet = $objPHPExcel->getActiveSheet();
$db = mysqli2::connect();


//$current_user =JFactory::getUser();

$sql = "select name from jdr8t_users where id = $id limit 1";
$r=  $db->query($sql)->fetch_assoc();

$name_agent = $r['name'];


$st  ="Отчет агента № ____";
$aSheet->setCellValue('A2', $st );

$st ="между  $name_agent (Агент) и ООО \"Речное Агентство\" (Компания)";
$aSheet->setCellValue('A3', $st );

$sql = "select * from aa_agent where user_id = {$id} limit 1";
$res = $db->query($sql);
$r = $res->fetch_assoc();

$sql = " select num_dog , date_format(date_dog, '%d.%m.%Y') as date_dog2 from  aa_agent where user_id = $id limit 1";
$r_dog =  $db->query($sql)->fetch_assoc();

$num_dog = $r_dog['num_dog'];
$date_dog = $r_dog['date_dog2'];

if ($num_dog == '') $num_dog = '________';
if ($date_dog == '') $date_dog = '________';


$st ="$name_agent, именуемое в дальнейшем \"Агент\", в лице _______________, ".
    "действующего на основании _______________, представляет, а ООО \"Речное Агентство\",".
	" именуемое в дальнейшем \"Компания\",  в лице Генерального директора Махлонова Д.В., действующего на основании Устава,".
	" принимает настоящий отчет об исполнении агентского поручения по агентскому договору №{$num_dog} от $date_dog";

$aSheet->setCellValue('A5', $st );

$st ="Агентское вознаграждение ". $r['fee'] ."%";
$aSheet->setCellValue('C9', $st );


$st = "за $d1 - $d2";
$aSheet->setCellValue('A12', $st );


// номера кают

$num_kautas = array();
$sql = " select aa_order.num , aa_schet.id from \n

  aa_schet, aa_order where \n
  aa_order.id_schet = aa_schet.id and \n
  aa_order.is_delete = 0 order by num
  ";

$res = $db->query($sql);

while ($r = $res->fetch_assoc()) {
	$num_kautas[$r['id']][] = $r['num'];
}



// оплаты
$sql = "select sum(amount) as ss, invoice_id from aa_1c where is_delete=0 group by invoice_id";
$res = $db->query($sql);
$pays = array();
while ($r  = $res->fetch_assoc() ){ $pays[ $r['invoice_id']] = $r['ss'];}


// суммы
$sql = " select sum(price) as ss , aa_schet.id from \n
	aa_schet, aa_order, aa_place where \n

	aa_schet.status =1 and \n
	aa_place.id_order = aa_order.id and \n
	aa_order.id_schet = aa_schet.id and \n
	aa_order.is_delete = 0 group by aa_schet.id ";

$res = $db->query($sql);
$money = array();
while ($r = $res->fetch_assoc()) {
	$money[$r['id']] = $r['ss'];
}

// комиссии по счетам
$sql = "select * from aa_schet where status = 1  and user_id =". $id;
$res = $db->query($sql);
$fee = array();
while ($r = $res->fetch_assoc()) {
	$fee[$r['id']] = $r['fee'];
	if ( $r['buyer'] == 'fiz') $fee[$r['id']] = 0;
}

// счета для отчета
$ok = array();
foreach ($money as $invoice_id => $sum){
	if (!isset($pays[ $invoice_id ])) continue;
	if (!isset($fee[ $invoice_id ])) continue;

	// если счет оплачен - ищем последнюю оплату
	if (  $pays[ $invoice_id ]  >= floor($sum * (100 - $fee[ $invoice_id]) / 100 )){
		$sql = "select date_format(date_pay, '%c.%Y') as pay from aa_1c where invoice_id=$invoice_id and  is_delete=0  \n
		order by date_pay DESC limit 1";

//		helper::var_dump_pre($sql);


		$res_last_pay = $db->query($sql);
		if ($res_last_pay->num_rows == 1){
			$r_last_pay = $res_last_pay->fetch_assoc();

			if ($r_last_pay['pay'] == $period[0] . '.' . $period[1] ){
				$ok[] = $invoice_id;
			}
		}
	}
}


//helper::var_dump_pre($money);
//helper::var_dump_pre($pays);
//helper::var_dump_pre('1');
//helper::var_dump_pre($ok);
//if (helper::isAdmin()) exit;

if (count($ok) == 0){
	header("Content-Type: text/html;charset=utf-8");
	?>

	<h2>Нет счетов для отчета за указанный период </h2>
	<a href="/report_agent">Вернуться назад</a>
	<?php
	exit;
}


$sql = " Select * from aa_schet where status=1 and id in (". implode(',', $ok) .") and user_id = ". $id;
$res = $db->query($sql);

$col= $res->num_rows;


$row = 16;
$i = 1;

if ($col > 1) $aSheet->insertNewRowBefore($row+1, $col - 1);

$sumF=0;
$sumH=0;
$sumJ=0;


while ($r = $res->fetch_assoc()){

	$aSheet->setCellValue("A$row", $i );

	$id_tur = $r['id_tur'];

	$tur = tur::getData($id_tur);

	$aSheet->setCellValue("B$row", $tur['teplohod_name'] );
	$aSheet->setCellValue("C$row", $tur['d1'] . ' - '. $tur['d2'] );

	$aSheet->setCellValue("D$row", "Заказ на круиз #".$r['id']." от ".$r['timecreate']);

	$st='';

	$invoice_id = $r['id'];



	$aSheet->setCellValue("E$row", implode(',', $num_kautas[ $invoice_id ]) );

	$aSheet->setCellValue("F$row", $money[  $invoice_id ] );

	$sumF +=$money[  $invoice_id ];

	$bonus = $money[  $invoice_id ] *  $fee[  $invoice_id ] / 100;

	$aSheet->setCellValue("H$row", $bonus );
	$sumH += $bonus;

	$aSheet->setCellValue("J$row", $pays[ $invoice_id ]);
	$sumJ += $pays[ $invoice_id ];



	$row++;
	$i++;
}



$aSheet->setCellValue("F$row", $sumF);
$aSheet->setCellValue("H$row", $sumH);
$aSheet->setCellValue("J$row", $sumJ);

$row  = $row + 2;

$st = "Вознаграждение Турагента за {$d1} - {$d2} составило $sumH (". num2str($sumH).")";
$aSheet->setCellValue("A$row", $st);

$row  = $row + 10;
$st ="Сумма уменьшения вознаграждения Турагента $d1 - $d2 составила  0 (Ноль рублей 00 копеек)";
$aSheet->setCellValue("A$row", $st);

$row  = $row + 3;
$st  ="Размер вознаграждения Турагента составляет $sumH (". num2str($sumH).")";
$aSheet->setCellValue("A$row", $st);



$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//выводим заголовки
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Отчет агента.xlsx"');
header('Cache-Control: max-age=0');
//выводим в браузер таблицу с бланком

//$objWriter->save('php://output');
SaveViaTempFile($objWriter);
