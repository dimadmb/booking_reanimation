<?php
/**
 * Created by PhpStorm.
 * User: Dronello
 * Date: 27.04.16
 * Time: 13:31
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tools.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";

?>




<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.gradient.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.gradient.min.css">


<script src="/bower_components/uikit/js/components/datepicker.min.js"></script>
<script src="/bower_components/uikit/js/components/form-select.min.js"></script>

<script src="/bower_components/json2/json2.js"></script>


<script src="/bower_components/uikit/js/components/notify.min.js"></script>

<link rel="stylesheet" href="/bower_components/uikit/css/components/notify.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/notify.gradient.min.css">

<script>
	i18n = {
		months:['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		weekdays:['Пн','Вт','Ср','Чт','Пт','Сб','Вс']
	};
</script>



<style type="text/css">
	.iDate+i,
	.iAmount+i{
		position: relative;
		z-index: 1;
		left: -22px;
		top: -3px;
		color: #7B7B7B;
		width: 0;
	}
</style>

<?php

	$db = mysqli2::connect();

	if (
		isset($_POST['invoice']) &&
		isset($_POST['amount']) &&
		isset($_POST['date_pay']) &&
		isset($_POST['num_pay'])

	){

		$invoice = (int)$_POST['invoice'];
		$amount = $_POST['amount'];
		$date_pay = $_POST['date_pay'];
		$num_pay = $_POST['num_pay'];
		$sql = "insert into aa_1c set  invoice_id= ?, amount = ?, date_pay= STR_TO_DATE( ?,'%d.%m.%Y'), num_pay = ?";


		$stmt = $db->stmt_init();
		$stmt->prepare($sql);
		$stmt->bind_param('iiss', $invoice,$amount,$date_pay,$num_pay);

		$stmt->execute();
	}


	$sql_schet ="select id as id, id as name from aa_schet where status=1 order by id DESC";


?>

<h1>Оплаты</h1>



<form action="/pay" method="post">

	<table class="uk-table" >
		<tr>
			<td>Счет</td>
			<td>
				<?php echo tools::createSelect($sql_schet,'invoice'); ?>

			</td>
		</tr>
		<tr>
			<td>Сумма</td>
			<td>
				<input name="amount"  class="iAmount"  type="text" style="width: 90px;">
				<i class="fa fa-rub" aria-hidden="true"></i>

			</td>
		</tr>
		<tr>
			<td>Дата платежа</td>
			<td>
				<input class="iDate" name="date_pay"
				       value = "<?=date('d.m.Y')?>"
				       type="text" data-uk-datepicker="{format:'DD.MM.YYYY', i18n: i18n }" style="width: 90px;" >
				<i class="fa fa-calendar"></i>
			</td>
		</tr>
		<tr>
			<td>Комментарий (платежка, прочее...)</td>
			<td>
				<input type="text" name="num_pay">
			</td>
		</tr>

	</table>

	<div>
		<button class="uk-button uk-button-success">
			Добавить
		</button>

	</div>
</form>


<table class="uk-table uk-table-striped">

	<thead>
		<tr>
			<th>Заявка</th>
			<th>Сумма</th>
			<th>Дата</th>
			<th>комментарий</th>
		</tr>
	</thead>

<?php
$sql = "Select *, date_format(date_pay,'%d.%m.%Y') as date_pay2 from aa_1c order by invoice_id DESC,id DESC";
$data = mysqli2::sql2array($sql);

foreach ($data as $item){
	
	?>
	<tr>
		<td><?=$item['invoice_id']?></td>
		<td><?=$item['amount']?> <i class="fa fa-rub" aria-hidden="true"></i></td>
		<td><?=$item['date_pay2']?></td>
		<td><?=$item['num_pay']?></td>
	</tr>
	<?php 
}
?>

</table>
