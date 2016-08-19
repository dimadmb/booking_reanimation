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
$d2 = " 31 " . config::$month[ $period[0] ] . " ".$period[1]."г. ";


$objPHPExcel = PHPExcel_IOFactory::load("report_agent_act.xlsx");

$objPHPExcel->setActiveSheetIndex(0);
$aSheet = $objPHPExcel->getActiveSheet();

$db = mysqli2::connect();

$num = "_______";

$st = "Акт оказанных услуг по реализации турпродукта $num от  ". date('d.m.Y');


$aSheet->setCellValue('A1', $st );

$sql = "select name from jdr8t_users where id = $id limit 1";
$r=  $db->query($sql)->fetch_assoc();

$name_agent = $r['name'];

$sql = " select num_dog , date_format(date_dog, '%d.%m.%Y') as date_dog2 from  aa_agent where user_id = $id limit 1";
$r_dog =  $db->query($sql)->fetch_assoc();

$num_dog = $r_dog['num_dog'];
$date_dog = $r_dog['date_dog2'];

if ($num_dog == '') $num_dog = '________';
if ($date_dog == '') $date_dog = '________';

$st ="ООО «Речное Агентство», именуемое в дальнейшем «Компания», с одной стороны и $name_agent".
	", именуемое в дальнейшем «Агент» с другой стороны, далее именуемые «Стороны», согласно агентскому договору №{$num_dog} от $date_dog".
	" за период  период c $d1 по $d2  составили настоящий Акт оказанных услуг по реализации турпродукта.";



$aSheet->setCellValue('B4', $st );


$sql = "select * from aa_agent where user_id = {$id} limit 1";
$res = $db->query($sql);
$r = $res->fetch_assoc();

$data = array();
$data[]=  $name_agent;
$data[]=  "ИНН ". $r['inn'];
$data[]=  $r['ur_address'];
$data[]=  "p/c ".$r['rs'];
$data[]=  "в банке ". $r['bank'];
$data[]=  'БИК '. $r['bik'];
$data[]=  "к/с ". $r['ks'];

$aSheet->setCellValue('B16', implode(', ', $data) );

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

		$res_last_pay = $db->query($sql);
		if ($res_last_pay->num_rows == 1){
			$r_last_pay = $res_last_pay->fetch_assoc();

			if ($r_last_pay['pay'] == $period[0] . '.' . $period[1] ){
				$ok[] = $invoice_id;
			}
		}
	}
}

if (count($ok) == 0){
	header("Content-Type: text/html;charset=utf-8");
	?>

	<h2>Нет счетов для отчета за указанный период </h2>
	<a href="/report_agent">Вернуться назад</a>
	<?php
	exit;
}


$sum = 0;
$sql = " Select * from aa_schet where id in (". implode(',', $ok).") and status=1 and user_id = ". $id;
$res = $db->query($sql);
while ($r = $res->fetch_assoc()){ $sum +=   $money[ $r['id'] ] *  $r['fee']  / 100;}


$st  ="Размер вознаграждения Агента составляет $sum (". num2str($sum) .")";
$aSheet->setCellValue('B6', $st  );





$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//выводим заголовки
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Акт оказанных услуг.xlsx"');
header('Cache-Control: max-age=0');
//выводим в браузер таблицу с бланком

//$objWriter->save('php://output');
SaveViaTempFile($objWriter);
