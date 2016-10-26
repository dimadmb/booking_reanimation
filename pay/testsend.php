<meta charset="utf-8" >

<?
$amount = "10.23"; $amountcurr = "RUB"; $currency = "MBC"; $number = "5412";
$description = urlencode("Тестовая оплата на $amount $amountcurr"); $trtype = "1";
$account = "acc001002";
$signature = "$amount:$amountcurr:$currency:$number:$description:"; $signature .= "$trtype:$account:секретный_ключ_1:секретный_ключ_2"; $signature = strtoupper(md5($signature));
?>

<form action="https://secure.paygateway.ru/api/payment/start" method=POST>
<br>"LOGIN" <input type="text" name="LOGIN" value="vodohod">
<br>"PASS" <input type="text" name="PASS" value="volgavolga">
<br>"TYPE" <input type="text" name="TYPE" value="1">
<br>"CODE" <input type="text" name="CODE" value="1">
<br>"AMOUNT"<input type="text" name="AMOUNT" value="539">

	<input type="submit" value="Оплатить"> 
</form>