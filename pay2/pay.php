<?



file_put_contents ("log.txt","массив request\n".date("H:i:s Y-m-d")."\n".print_r($_REQUEST,1)."\n\n", FILE_APPEND | LOCK_EX);

file_put_contents ("log.txt","массив server\n".date("H:i:s Y-m-d")."\n".print_r($_SERVER,1)."\n\n", FILE_APPEND | LOCK_EX);


require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/Discount.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
$db = mysqli2::connect();

if(!($_REQUEST['LOGIN'] == "rechagent"  && $_REQUEST['PASS'] == "volgavolgareka"  ))
{
	exit("login");
}

if($_REQUEST['TYPE'] == "1" )
{

// запросить счёт и выбрать всю инфу по круизу
$code = $_REQUEST['CODE1'];


// Физик или агентство и id тура 
$query="SELECT buyer , id_tur FROM `aa_schet` WHERE `id` = ".$code;
$res = $db->query($query);
while ($r = $res->fetch_assoc()) {
	$buyer = $r['buyer'];
	$id_tur = $r['id_tur'];
}

	$query="SELECT * FROM `aa_tur` WHERE `id` = ".$id_tur;
	$res = $db->query($query);
	while ($r = $res->fetch_assoc()) {
		$tur = array(
			"name" => $r['name'],
			"date_start" => $r['date_start'],
			"date_stop" => $r['date_stop'],
		);
	}	
	


// Скидка постоянного
$query="SELECT `permanent`,`seson_discount` FROM `aa_schet` WHERE `id` = ".$code;
$res_perm = $db->query($query);
while ($r = $res_perm->fetch_assoc()) {
	$permanent = $r['permanent']; // 
	$seson = $r['seson_discount'];
}
$permanent_koef = ( $permanent != null ) ? ((100 - $permanent) / 100) : 1 ;
$seson_discount =  ( $seson != null) ? ((100 - $seson)/ 100) : 1;

// полная стоимость
$query = "
select price as ss , aa_schet.id 
from aa_schet, aa_order, aa_place
where aa_place.id_order = aa_order.id 
and aa_order.id_schet = aa_schet.id 
and aa_order.is_delete = 0 
and aa_order.id_schet = ".$code."
";
$res_full_price = $db->query($query);
while ($r = $res_full_price->fetch_assoc()) {
	$full_prices[] = $r['ss']; // полная стоимость
}

$full_price = 0;
foreach($full_prices as $fullPricesItem)
{
	$full_price = $full_price + round($fullPricesItem * $seson_discount * $permanent_koef);
}



$amount = "0.00";
// оплачено 
$query ="
SELECT sum(amount) as ss
FROM `aa_1c`
WHERE `invoice_id` = ".$code."
and `is_delete` = 0
group by `invoice_id`
";
$res_amount = $db->query($query);
while ($r = $res_amount->fetch_assoc()) {
	$amount = $r['ss']*1; // оплачено 
}

$query = "SELECT fee FROM `aa_schet` WHERE id = ".$code;
$res_fee = $db->query($query);
while ($r = $res_fee->fetch_assoc()) {
	$fee_percent = $r['fee']/100 ; // коммисия 
}

$amount_to_pay = round($full_price*(1-$fee_percent) - $amount) ;// к оплате

//$amount_to_pay = 5; $full_price = 5;

$fee = $fee_percent * $amount_to_pay;

//number_format(, 2, '.', '')

$answer = '<?xml version="1.0" encoding="windows-1251" ?>
<RESPONSE>
<RESULTCODE>0</RESULTCODE>
<RESULTMESSAGE>OK</RESULTMESSAGE>
<DATE>'.date("YmdHis").'</DATE>
<ADDINFO>
<AGENCY>
<NAME>«Речное Агентство»</NAME>
<BRANCH></BRANCH>
<INN>9710008811</INN>
</AGENCY>
<FULLPRICE>'.number_format($full_price, 2, '.', '').'</FULLPRICE>
<CURRENCY>RUB</CURRENCY>
<AMOUNTTOPAY>'.number_format($amount_to_pay, 2, '.', '').'</AMOUNTTOPAY>
<EXCHANGERATE>1</EXCHANGERATE>
<AMOUNTTOPAYRUB>'.number_format($amount_to_pay, 2, '.', '').'</AMOUNTTOPAYRUB>
<AGENCYCOMISSION>'.number_format($fee, 2, '.', '').'</AGENCYCOMISSION>
<STARTDATE>'.date("Ymd000000", strtotime($tur["date_start"])).'</STARTDATE>
<ENDDATE>'.date("Ymd000000", strtotime($tur["date_stop"])).'</ENDDATE>
<TOURISTLIST>
<TOURIST>
<FIRSTNAME></FIRSTNAME>
<LASTNAME></LASTNAME>
<PATRONYMIC></PATRONYMIC>
<BIRTHDATE>19700101000000</BIRTHDATE>
</TOURIST>
</TOURISTLIST>
<PAYER>
<FIRSTNAME></FIRSTNAME>
<LASTNAME></LASTNAME>
<PATRONYMIC></PATRONYMIC>
<BIRTHDATE>19611019000000</BIRTHDATE>
</PAYER>
<SERVICELIST>
<SERVICE>'.iconv("utf-8","windows-1251",$tur["name"]).'</SERVICE>
</SERVICELIST>
</ADDINFO>
</RESPONSE>	
';
//header('Content-Type: text/xml; charset=utf-8');
die($answer); 
	
?>	
<?
}	

if($_REQUEST['TYPE'] == "2" )
{
	// записать оплату
	
$sql = "INSERT INTO `aa_1c` (`id`, `invoice_id`, `date_pay`, `num_pay`, `amount`, `is_delete`, `timecreate`, `pay_request`) VALUES (NULL, '".$_REQUEST['CODE1']."', CURRENT_TIMESTAMP , 'ONLINE', ". $_REQUEST['AMOUNT']/100 .", '0', CURRENT_TIMESTAMP, '".print_r($_REQUEST,1)."')";

$res = $db->query($sql);	

//die($sql );

$answer = '<?xml version="1.0" encoding="windows-1251" ?>	
<RESPONSE>
<RESULTCODE>0</RESULTCODE>
<RESULTMESSAGE>OK</RESULTMESSAGE>
<DATE>'.date("YmdHis").'</DATE>
<PAYID>'.$_REQUEST['PAYID'].'</PAYID>
</RESPONSE>
';
header('Content-Type: text/xml; charset=utf-8');
die($answer); 
}