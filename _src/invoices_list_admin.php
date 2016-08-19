<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.gradient.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.gradient.min.css">

<style type="text/css">

	.gray_date {
		color: #999;
		font-size: 10px;
		font-weight: bold;
	}

	.trRemove {
		background: rgba(255, 0, 0, 0.4) !important;

		display: none;;
	}

	.iDate + i,
	.iAmount + i {

		position: relative;
		z-index: 1;
		left: -22px;
		top: -3px;
		color: #7B7B7B;
		/*cursor: pointer;*/
		width: 0;
	}

	.pays td {
		padding: 2px;
		text-align: right;;
	}

	.pays tr:hover td {

		background: #999;
		color: #FFF

	}

	.doDelPay {
		color: #F00;
		cursor: pointer;
	}

	.classPartPay{
		font-weight: bold;
		color: #ffd966;
		font-size: 16px;
		text-shadow: 1px 1px #000;
	}

	.classFullPay{
		font-weight: bold;
		color: #f00;
		font-size: 16px;
		text-shadow: 1px 1px #000;
	}

	.classOverPay{
		font-weight: bold;
		color: #a34ae0;
		font-size: 16px;
		text-shadow: 1px 1px #000;
	}

	.divRazn{
		font-size: 12px;
		color : #000;
		text-shadow: none;

		white-space: nowrap;
	}


</style>

<?php
/**
 * Created by PhpStorm.
 * User: Dronello
 * Date: 11.04.16
 * Time: 1:00
 */


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

// суммы
$sql = " select sum(price) as ss , aa_schet.id from \n
	aa_schet, aa_order, aa_place where \n
	aa_place.id_order = aa_order.id and \n
	aa_order.id_schet = aa_schet.id and \n
	aa_order.is_delete = 0 group by aa_schet.id ";

$res = $db->query($sql);
$money = array();
while ($r = $res->fetch_assoc()) {
	$money[$r['id']] = $r['ss'];
}




// оплаты

$pays_string = array();
$pays_sum = array();

$sql = "Select *, date_format(date_pay,'%d.%m.%Y') as date_pay2 from aa_1c  where is_delete=0 order by id DESC";

$res = $db->query($sql);

while ($r = $res->fetch_assoc()) {

	$id_schet = $r['invoice_id'];

	if (!isset($pays_string[$id_schet])) $pays_string[$id_schet] = '';

	if (!isset($pays_sum[$id_schet])) $pays_sum[$id_schet] = 0;



	$pays_sum[ $id_schet ] += $r['amount'];

	$pays_string[ $id_schet ] .=
		"<tr>
<td title='" . $r['num_pay'] . "' >". number_format($r['amount'],2,'.',' ') .

		" <i class=\"fa fa-rub\" aria-hidden=\"true\"></i></td>
<td>{$r['date_pay2']}</td>
<td><i data-id='{$r['id']}' class='fa fa-ban doDelPay' aria-hidden='true'></i></td>
</tr>";

}

//helper::var_dump_pre($pays_sum);
//helper::var_dump_pre($money);



//список покупателей по счетам

$sql = " select name,id_schet from aa_buyer_ur";
$res_name_ur = $db->query($sql);
$r_name_ur = array();
while ($r = $res_name_ur->fetch_assoc()) {
	$r_name_ur[$r['id_schet']] = $r['name'];
}

$sql = " select * from aa_buyer_fiz";
$res_name_fiz = $db->query($sql);
$r_name_fiz = array();
while ($r = $res_name_fiz->fetch_assoc()) {
	$r_name_fiz[$r['id_schet']] = $r['surname'] . " " . $r['name'] . " " . $r['patronymic'];
}


$sql = "select aa_schet.id as id_schet ,

comment_manager,
fee,
aa_schet.buyer,
status,
aa_tur.id as id_tur,
aa_tur.name as way,
date_format(aa_schet.timecreate, '%H:%i <span class=gray_date>%d.%m</span>') as timecreate2,

date_format(date_start, '%d.%m') as d1,
date_format(date_stop, '%d.%m') as d2,
aa_teplohod.name as teplohod \n

 from aa_schet,aa_tur, aa_teplohod \n
 where aa_schet.id_tur = aa_tur.id and aa_tur.id_teplohod = aa_teplohod.id  order by aa_schet.id DESC";
$res = $db->query($sql);
?>


<h1>Заявки

	<button class="uk-button showRemoved">
		Показать аннулированые
	</button>
</h1>

<table class="uk-table uk-table-striped">

	<thead>
	<tr>
		<th>Заявка</th>
		<th>Создан</th>
		<th>Покупатель</th>
		<th style="text-align: right;">

			<span class="classPartPay">Аванс</span><br>
			<span class="classFullPay">Оплачено</span><br>
			<span class="classOverPay">Переплата</span>

		</th>
		<th>Каюты</th>
		<th>Даты</th>
		<th>Теплоход</th>
		<th>Комментарий<br>менеджера</th>
		<th>Оплаты</th>
	</tr>
	</thead>

	<?php while ($r = $res->fetch_assoc()) {

		$id = $hashids_schet->encode($r['id_schet']);

		$is_delete = '';
		$hint = '';

		if ($r['status'] == 0) {
			$is_delete = 'trRemove';
			$hint = " title='Удаленная заявка' ";

		}

		?>


		<tr <?= $hint ?> class="<?= $is_delete ?>">
			<td>
				<a target="_blank" href="/invoice/<?= $id ?>">
					#<?= $r['id_schet'] ?>
				</a>
			</td>
			<td><?= $r['timecreate2'] ?></td>

			<td>
				<?php

				if ($r['buyer'] == 'ur') {
					echo $r_name_ur[$r['id_schet']];
				} else {

					echo '<i style="color:#999;" class="fa fa-user" aria-hidden="true"></i> ';
					echo $r_name_fiz[$r['id_schet']];
				}
				?>
			</td>

			<?php

			$id_schet = $r['id_schet'];

			$classOplata = '';

			if ( isset($pays_sum[$id_schet])) {

				if ($pays_sum[$id_schet] > 0) {


					if ($r['buyer'] == 'fiz') $r['fee'] = 0;

					$pays_sum[$id_schet] = round($pays_sum[$id_schet],2);

					$money2 =  round($money[$id_schet] * (100 - $r['fee']) / 100,2);

					if ( $pays_sum[$id_schet] < $money2 ) $classOplata = 'classPartPay';
					else if ( $pays_sum[$id_schet] == $money2 ) $classOplata = 'classFullPay';
					else if ( $pays_sum[$id_schet] > $money2 ) $classOplata = 'classOverPay';
				}
			}
			?>





			<td style="text-align: right; width: 100px;" class="<?=$classOplata ?>">

<!--				--><?php //helper::var_dump_pre($money2) ?>
<!--				--><?php //helper::var_dump_pre( $pays_sum[$id_schet]) ?>
<!--				--><?php
//				$aa = ($pays_sum[$id_schet] - $money2)  ;
//				helper::var_dump_pre( $aa ) ?>

				<?php if (isset($money[$id_schet])) { ?>
					<?= $money[$id_schet] ?> <i class="fa fa-rub" aria-hidden="true"></i>


					<?php if ($classOplata == 'classPartPay'){
						$razn = $money2 - $pays_sum[$id_schet];
						echo "<div class='divRazn'>остаток ". round($razn,2) ."</div>";
					}?>

					<?php if ($classOplata == 'classOverPay'){
						$razn =   $pays_sum[$id_schet] - $money2;
						echo "<div class='divRazn'>переплата ". round($razn,2) ."</div>";
					}?>


				<?php } ?>

			</td>

			<td><?= implode(', ', $num_kautas[$r['id_schet']]); ?></td>
			<td>
				<?= $r['d1'] ?> - <?= $r['d2'] ?>
				<a title="<?= $r['way'] ?>" target="_blank" href="/cruise/<?= $r['id_tur'] ?>">
					<i class="fa fa-external-link" aria-hidden="true"></i>
				</a>

			</td>
			<td><?= $r['teplohod'] ?></td>

			<td>
				<?= htmlspecialchars($r['comment_manager']) ?>
			</td>

			<td style="width:200px;">
				<div class="pays" style="text-align: right;" >
					<table>
						<?php if (isset($pays_string[$r['id_schet']])) echo $pays_string[$r['id_schet']]; ?>
					</table>
				</div>

				<div style="text-align: right;">
					<a data-invoice="<?= $r['id_schet'] ?>" class="showPay" href="#">
						<i class="fa fa-plus" aria-hidden="true"></i> Добавить оплату
					</a>
				</div>


			</td>
		</tr>


	<?php } ?>

</table>


<div id="modalPay" class="uk-modal">

	<div class="uk-modal-dialog">
		<a class="uk-modal-close uk-close"></a>

		<h2>Оплаты по заявке #<span class="payInvoice"></span></h2>


		<input name="invoice" type="hidden">
		<table class="uk-table">

			<tr>
				<td>Сумма</td>
				<td>
					<input name="amount" class="iAmount" type="text" style="width: 90px;">
					<i class="fa fa-rub" aria-hidden="true"></i>

				</td>
			</tr>
			<tr>
				<td>Дата платежа</td>
				<td>
					<input class="iDate" name="date_pay"

					       value="<?= date('d.m.Y') ?>"

					       type="text" data-uk-datepicker="{format:'DD.MM.YYYY', i18n: i18n }" style="width: 90px;">

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

		<div style="text-align: right;">
			<button class="uk-button uk-button-success doPay">
				Добавить
			</button>

		</div>
	</div>
</div>

<script src="/bower_components/uikit/js/components/datepicker.min.js"></script>
<script src="/bower_components/uikit/js/components/form-select.min.js"></script>
<script>
	i18n = {
		months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
		weekdays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
	};
</script>

<script src="/bower_components/mustache.js/mustache.min.js"></script>
<script id="tmplPayInvoice" type="text/mustache">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . "/tmpl/payInvoice.mustache"; ?>

</script>

<script src="/bower_components/uikit/js/components/notify.min.js"></script>

<link rel="stylesheet" href="/bower_components/uikit/css/components/notify.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/notify.gradient.min.css">


<script>
	jQuery(function ($) {
		$('.showRemoved').click(function () {
			$('.trRemove').toggle();
		});


		$(document).on('click', '.doDelPay', function () {
			if (!confirm('Удалить оплату')) {
				return false;
			}
			var id = $(this).data('id');
			var url = 'service/invoice.php?action=doDelPay';

			$.post(url, {id: id}, function (response) {
				if (response.success = 1) {

					$('.doDelPay[data-id=' + id + ']').closest('tr').hide();

					UIkit.notify({
						message: 'Оплата удалена!',
						status: 'info',
						timeout: 1500,
						pos: 'top-center'
					});
				} else {

				}

			});

		});

		var modalPay = UIkit.modal("#modalPay");

		$('.showPay').click(function () {
			var invoice = $(this).data('invoice');

			$('[name=invoice]').val(invoice);

			$('.payInvoice').html(invoice);

			if (modalPay.isActive()) {
				modalPay.hide();
			} else {
				modalPay.show();
			}
			return false;
		});

		$('.doPay').click(function () {

			var data = {};
			data.invoice = $('[name=invoice]').val();
			data.amount = $('[name=amount]').val();
			data.date_pay = $('[name=date_pay]').val();
			data.num_pay = $('[name=num_pay]').val();

			var url = "/service/invoice.php?action=doPay";

			$.post(url, data, function (response) {

				var tmplPayInvoice = document.getElementById('tmplPayInvoice').innerHTML;

				var content = Mustache.render(tmplPayInvoice, {pays: response.data});

				$('[data-invoice=' + data.invoice + ']').closest('td').find('.pays').html(content);

				modalPay.hide();

			});
		});
	});
</script>