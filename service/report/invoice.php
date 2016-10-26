<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/invoice.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";


require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/num2str.php";

function SaveViaTempFile($objWriter){
$filePath =  $_SERVER['DOCUMENT_ROOT'] . '/tmp/' . rand(0, getrandmax()) . rand(0, getrandmax()) . ".tmp";
$objWriter->save($filePath);
readfile($filePath);
unlink($filePath);
}



$hashids_schet = new Hashids\Hashids(config::$schet_salt,32);


$hash = $_GET['hash'];

$id = $hashids_schet->decode($hash);

if (count($id)==0){
	header('Location: /');
}

$id = $id[0];

//if ($id == 188) header('Location: /');
//if ($id == 199) header('Location: /');
//if ($id == 202) header('Location: /');




if (user::is_fiz()){
	if (!invoice::checkFiz($id)){
		header("Location: /invoice/$hash");
		return false;
	}
}




$db = mysqli2::connect();

$sql = "select aa_schet.*,

date_format(aa_schet.timecreate, '%d.%m.%Y') as timecreate2,
 aa_buyer_ur.* from aa_schet, aa_buyer_ur where aa_schet.id = aa_buyer_ur.id_schet and aa_schet.id = $id ";

$res = $db->query($sql);
$r = $res->fetch_assoc();

$fee = $r['fee'];
$id_tur = $r['id_tur'];

if ($r['buyer'] == 'ur') {

    $objPHPExcel = PHPExcel_IOFactory::load("invoice_agent.xlsx");

	$objPHPExcel->setActiveSheetIndex(0);
	$aSheet = $objPHPExcel->getActiveSheet();

	$st = "Оплата по счету № $id от {$r['timecreate2']}г";
	$aSheet->setCellValue("B11", $st);


	$st = "Счет на оплату № $id от {$r['timecreate2']}г";
	$aSheet->setCellValue("B13", $st);

	$st = $r['name'] . ", ИНН " . $r['inn'] . ", КПП " . $r['kpp'] . ", " . $r['ur_address'] . ", тел. " . $r['phone'];
	$aSheet->setCellValue("H17", $st);


	$sql = "select price,num, count(*) as cc from aa_order, aa_place

where id_schet = $id and aa_order.id = aa_place.id_order and is_delete=0 group  by num,price order by num, price DESC";

	$res_price = $db->query($sql);
	$col = $res_price->num_rows;

	if ($col > 1) $aSheet->insertNewRowBefore(21, $col - 1);

	$row = 20;

	$all_sum = 0;
	$all_fee_sum = 0;


	while ($r_price = $res_price->fetch_assoc()) {

		$aSheet->mergeCells("B{$row}:C{$row}");
		$aSheet->mergeCells("D{$row}:O{$row}");
		$aSheet->mergeCells("P{$row}:R{$row}");
		$aSheet->mergeCells("S{$row}:T{$row}");
		$aSheet->mergeCells("U{$row}:X{$row}");
		$aSheet->mergeCells("Y{$row}:AC{$row}");
		$aSheet->mergeCells("AD{$row}:AG{$row}");
		$aSheet->mergeCells("AH{$row}:AL{$row}");
		$aSheet->mergeCells("AM{$row}:AR{$row}");
		$aSheet->mergeCells("AS{$row}:AV{$row}");
		$aSheet->mergeCells("AW{$row}:AZ{$row}");
		$aSheet->mergeCells("BA{$row}:BE{$row}");


		$aSheet->setCellValue("B$row", $row - 19);


		$tur = tur::getData($id_tur);

		$turData = $tur['name'] . ', ' . $tur['d1'] . ' - ' . $tur['d2'] . ', ' . $tur['teplohod_name'] .

				', каюта ' . $r_price['num'];

		$aSheet->setCellValue("D$row", $turData);


		$aSheet->setCellValue("U$row", $r_price['price']);

		$aSheet->setCellValue("AS$row", 'Без НДС');
		$aSheet->setCellValue("S$row", 'шт');

		$aSheet->setCellValue("P$row", $r_price['cc']);


		$sum = $r_price['cc'] * $r_price['price'];

		$all_sum += $sum;

		$aSheet->setCellValue("Y$row", $sum);

		$aSheet->setCellValue("AH$row", $fee);

		$fee_sum = $sum * $fee / 100;

		$all_fee_sum += $fee_sum;

		$aSheet->setCellValue("AM$row", $fee_sum);

		$itogo = $sum - $fee_sum;

		$aSheet->setCellValue("BA$row", $itogo);


		$row++;


	}

	$row++;

	$aSheet->setCellValue("Y$row", $all_sum);
	$aSheet->setCellValue("AM$row", $all_fee_sum);

	$itogo = $all_sum - $all_fee_sum;

	$aSheet->setCellValue("BA$row", $itogo);

	$row += 2;

	$aSheet->setCellValue("BA$row", $itogo);
	$row++;

	$aSheet->setCellValue("BA$row", $all_fee_sum);

	$row++;

	$st = "Всего наименований $col , на сумму $itogo RUB";

	$aSheet->setCellValue("B$row", $st);

	$row++;


//$itogo =  floor($itogo);
	$st = num2str($itogo);


	$aSheet->setCellValue("B$row", $st);


} else if ($r['buyer'] == 'fiz') {


	$objPHPExcel = PHPExcel_IOFactory::load("invoice_fiz.xlsx");

	$objPHPExcel->setActiveSheetIndex(0);
	$aSheet = $objPHPExcel->getActiveSheet();

	$st = "Оплата по счету № $id от {$r['timecreate2']}г";
	$aSheet->setCellValue("B11", $st);


	$st = "Счет на оплату № $id от {$r['timecreate2']}г";
	$aSheet->setCellValue("B13", $st);

	$sql = "select * from aa_buyer_fiz where id_schet = $id limit 1";

	$fio = $db->query($sql)->fetch_assoc();

	$fio = $fio['surname'] . ' '.$fio['name'] . ' '.$fio['patronymic'] ;

	$st ="Покупатель: $fio";

	$aSheet->setCellValue("H17", $st);



	$sql = "select price,num, count(*) as cc from aa_order, aa_place

where id_schet = $id and aa_order.id = aa_place.id_order and is_delete=0 group  by num,price order by num, price DESC";

	$res_price = $db->query($sql);
	$col = $res_price->num_rows;

	if ($col > 1) $aSheet->insertNewRowBefore(21, $col - 1);

	$row = 20;

	$all_sum = 0;

	while ($r_price = $res_price->fetch_assoc()) {

		$aSheet->mergeCells("B{$row}:C{$row}");
		$aSheet->mergeCells("D{$row}:X{$row}");
		$aSheet->mergeCells("Y{$row}:AA{$row}");
		$aSheet->mergeCells("AB{$row}:AC{$row}");
		$aSheet->mergeCells("AD{$row}:AG{$row}");
		$aSheet->mergeCells("AH{$row}:AL{$row}");
		$aSheet->mergeCells("AM{$row}:AR{$row}");
		$aSheet->mergeCells("AS{$row}:AW{$row}");



		$aSheet->setCellValue("B$row", $row - 19);

		$tur = tur::getData($id_tur);

		$turData = $tur['name'] . ', ' . $tur['d1'] . ' - ' . $tur['d2'] . ', ' . $tur['teplohod_name'] .

				', каюта ' . $r_price['num'];

		$aSheet->setCellValue("D$row", $turData);


		$aSheet->setCellValue("Y$row", $r_price['cc']);

		$aSheet->setCellValue("AD$row", $r_price['price']);

		$sum = $r_price['cc'] * $r_price['price'];

		$all_sum += $sum;

		$aSheet->setCellValue("AS$row", $sum);

		$aSheet->getRowDimension($row)->setRowHeight(20);

		$row++;

	}

	$row++;
	$aSheet->setCellValue("AS$row", $all_sum);
	$row++;
	$row++;
	$aSheet->setCellValue("AS$row", $all_sum);

	$row++;


	$itogo = num2str($all_sum);
	$st = "Всего наименований $col , на сумму $itogo";

	$aSheet->setCellValue("B$row", $st);



}


$aSheet->getSheetView()->setZoomScale(100);


$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//выводим заголовки
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Счет_#'. $id .'.xlsx"');
header('Cache-Control: max-age=0');
//выводим в браузер таблицу с бланком
//$objWriter->save('php://output');

SaveViaTempFile($objWriter);