<?

ini_set('display_errors',1);

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/Discount.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";


$id = $_GET["id"];

$seson_discount = Discount::getSesonPercent();

$db = mysqli2::connect();




//die($sql);


/// ��������� ������������ �� ������ ����������� �������

$sql = "select * from aa_schet where id=$id";
$res = $db->query($sql);
while ($r = $res->fetch_assoc()) {
	$permanent = $r['permanent'];
	$permanent_request = $r['permanent_request'];
}

if( ($permanent_request*1>0) && ($permanent*1 == 0))
{
	die("������ ����������� ������� �� ������������ ����������, ��������� ������������� ���� ������� ������� � ������� ������.");
}

///  ���� �������� 100% � �������� ������ �����, �� ������ ���������
$amount = 0;
// �������� 
$query ="
SELECT sum(amount) as ss
FROM `aa_1c`
WHERE `invoice_id` = ".$id."
and `is_delete` = 0
group by `invoice_id`
";
$res_amount = $db->query($query);
while ($r = $res_amount->fetch_assoc()) {
	$amount = $r['ss']*1; // �������� 
}


// ������ �����������
$query="SELECT `permanent`,`seson_discount` FROM `aa_schet` WHERE `id` = ".$id;
$res_perm = $db->query($query);
while ($r = $res_perm->fetch_assoc()) {
	$permanent = $r['permanent']; // 
	$seson = $r['seson_discount'];
}
$permanent_koef = ( $permanent != null ) ? ((100 - $permanent) / 100) : 1 ;
$seson_discount =  ( $seson != null) ? ((100 - $seson)/ 100) : 1;


// ������ ���������
$query = "
select sum(price) as ss , aa_schet.id 
from aa_schet, aa_order, aa_place
where aa_place.id_order = aa_order.id 
and aa_order.id_schet = aa_schet.id 
and aa_order.is_delete = 0 
and aa_order.id_schet = ".$id."
group by aa_schet.id ";
$res_full_price = $db->query($query);
while ($r = $res_full_price->fetch_assoc()) {
	$full_price = $r['ss']; // ������ ���������
}

$full_price = round($full_price * $seson_discount * $permanent_koef);


if (!($amount >= $full_price) )
{
$seson_discount = Discount::getSesonPercent();
$sql = "update aa_schet set seson_discount = $seson_discount  where id=$id";
$res = $db->query($sql);
}

//die($sql);
$href = "https://b2c.appex.ru/payment/choice?orderSourceCode=$id&billingCode=Rechnoeagentstvo003";



/// ��������� �����
// �������� �������� ������


header('Location: '.$href);



