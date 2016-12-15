<?



file_put_contents ("log.txt","массив request\n".date("H:i:s Y-m-d")."\n".print_r($_REQUEST,1)."\n\n", FILE_APPEND | LOCK_EX);

//file_put_contents ("log.txt","массив server\n".date("H:i:s Y-m-d")."\n".print_r($_SERVER,1)."\n\n", FILE_APPEND | LOCK_EX);

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

// полная стоимость
$query = "
select sum(price) as ss , aa_schet.id 
from aa_schet, aa_order, aa_place
where aa_place.id_order = aa_order.id 
and aa_order.id_schet = aa_schet.id 
and aa_order.is_delete = 0 
and aa_order.id_schet = ".$code."
group by aa_schet.id ";
$res_full_price = $db->query($query);
while ($r = $res_full_price->fetch_assoc()) {
	$full_price = $r['ss']; // полная стоимость
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

$amount_to_pay = $full_price*(1-$fee_percent) - $amount ;// к оплате

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
<AGENСYCOMISSION>'.number_format($fee, 2, '.', '').'</AGENСYCOMISSION>
<STARTDATE>20161212000000</STARTDATE>
<ENDDATE>20161215000000</ENDDATE>
<TOURISTLIST>
<TOURIST>
<FIRSTNAME>Иванов</FIRSTNAME>
<LASTNAME>Иван</LASTNAME>
<PATRONYMIC>Иванович</PATRONYMIC>
<BIRTHDATE>19611019000000</BIRTHDATE>
</TOURIST>
</TOURISTLIST>
<PAYER>
<FIRSTNAME>Иванов</FIRSTNAME>
<LASTNAME>Иван</LASTNAME>
<PATRONYMIC>Иванович</PATRONYMIC>
<BIRTHDATE>19611019000000</BIRTHDATE>
</PAYER>
<SERVICELIST>
<SERVICE>Услуги, входящие в тур.
Название отеля
Тип размещения
Тип питания
Авиаперелет
Мед.страховка
Тип трансфера</SERVICE>
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