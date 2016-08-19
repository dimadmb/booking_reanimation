<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";

ini_set('display_errors', 1);

$db = mysqli2::connect();

$sql = "select jdr8t_users.* , fee, num_dog, date_format(date_dog,'%d.%m.%Y') as date_dog2 from jdr8t_users , aa_agent \n
 where jdr8t_users.id = aa_agent.user_id \n
 order by id DESC ";
$res = $db->query($sql);
?>

<link rel="stylesheet" href="/bower_components/uikit/css/uikit.min.css">

<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.gradient.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.gradient.min.css">

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
		months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
		weekdays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
	};
</script>


<style type="text/css">

	.iDate + i,
	.iAmount + i {
		position: relative;
		z-index: 1;
		left: -22px;
		top: -3px;
		color: #7B7B7B;
		width: 0;
	}

	.reportAgent{
		border: 2px #ccc solid;
		padding: 5px;
		margin: 10px 0px 10px 0px;
	}
</style>

<div class="reportAgent">
<form class="uk-form" method="post" action="/">

	Агент


	<select name="hash">
	<?php
				$sql ="select jdr8t_users.name , jdr8t_users.id from jdr8t_users,aa_agent where jdr8t_users.id = aa_agent.user_id";
				$data = mysqli2::sql2array($sql);

	$hashids_user = new Hashids\Hashids(config::$user_salt,32);



				?>


		<?php foreach ($data as $item){

			$selected = '';

			if ( $item['id'] == $r_schet['user_id']){
				$selected = ' selected ';
			}

			$hash = $hashids_user->encode($item['id']);

			?>
			<option <?=$selected?> value="<?=$hash?>"><?=$item['name']?></option>
		<?php } ?>
	</select>






	Выберите период

	<select name="period">
		<?php for ($i = 4 ; $i<=10;$i++) { ?>
			<option value="<?=$i?>.<?=config::$year?>">
				<?=config::$month0[$i]?> <?=config::$year?>
			</option>
		<?php } ?>
	</select>

	<br>

	<button
			onclick="jQuery(this).closest('form').attr('action','/service/report/report_agent.php'); "
			class="uk-button uk-button-success"  style="margin-left: 15px;">
		<i class="fa fa-bars" aria-hidden="true"></i> Отчет агента
	</button>

	<button
			onclick="jQuery(this).closest('form').attr('action','/service/report/report_agent_act.php'); "
			class="uk-button uk-button-primary" style="margin-left: 15px;">
		<i class="fa fa-list" aria-hidden="true"></i> Акт
	</button>

</form>

</div>



<table class="uk-table uk-table-striped">
	<thead>
	<tr>
		<th>Агент</th>
		<th>Email / логин</th>
		<th>Комиссия, %</th>

		<th>Номер<br>договора</th>
		<th>Дата<br>договора</th>

		<th></th>
	</tr>
	</thead>

	<tbody>
	<?php while ($r = $res->fetch_assoc()) { ?>

		<tr>
		<tr>
			<td><?= $r['name'] ?></td>
			<td><?= $r['email'] ?></td>
			<td>
				<input maxlength="2" data-user-id="<?=$r['id']?>" name="fee"
				       style="width: 40px;" type="text" value="<?= $r['fee'] ?>">
			</td>
			<td>
				<input maxlength="3" data-user-id="<?=$r['id']?>" name="num_dog"

				       <?php if ($r['num_dog'] !=0) echo " value='{$r['num_dog']}' ";?>

				       type="text" style="width:40px;">
			</td>
			<td>
				<input class="iDate" name="date_dog" data-user-id="<?=$r['id']?>" name="date_dog"

						<?php if ($r['date_dog2'] !='00.00.0000') echo " value='{$r['date_dog2']}' ";?>

				       type="text" data-uk-datepicker="{format:'DD.MM.YYYY', i18n: i18n }" style="width: 90px;">
				<i class="fa fa-calendar"></i>
			</td>

			<td>
				<?php if ($r['block'] == 1) { ?>
					<button class="activate-button uk-button uk-button-success">Активировать</button>
				<?php } ?>
			</td>
		</tr>


	<?php } ?>
	</tbody>
</table>

<script>
	jQuery(function ($) {

		$('[data-user-id]').on('hide.uk.datepicker',function(e){

			if (!confirm('Обновить дату договора?')) { return false;}

			var data = {
				user_id : $(this).data('user-id'),
				name  : $(this).attr('name'),
				value  :  $(this).val()	};

			var url  = '/service/user.php?action=save';

			$.post(url,data,function(response){
				if (response.success=1){
					UIkit.notify({
						message: 'Данные агентства обновлены!',
						status: 'info',
						timeout: 1500,
						pos: 'top-center'
					});
				}
			});


		});

		$('[data-user-id]').keyup(function(e){
			if (e.keyCode == 13){

				//if (!confirm('Обновить данные ?')) { return false;}

				var data = {
					user_id : $(this).data('user-id'),
					name  : $(this).attr('name'),
					value  :  $(this).val()	};

				var url  = '/service/user.php?action=save';
				$.post(url,data,function(response){
					if (response.success=1){
						UIkit.notify({
							message: 'Данные агентства обновлены!',
							status: 'info',
							timeout: 1500,
							pos: 'top-center'
						});
					}
				});


			}
		});

		$('.activate-button').click(function () {
			var $fee = $(this).closest('tr').find('input[name=fee]');
			var $num_dog = $(this).closest('tr').find('input[name=num_dog]');
			var $date_dog = $(this).closest('tr').find('input[name=date_dog]');

			var data = {
				user_id: $fee.data('user-id'),
				fee: $fee.val(),
				num_dog: $num_dog.val(),
				date_dog: $date_dog.val()
			};

			var url = '/service/user.php?action=activate';
			$.post(url, data, function (response) {
				document.location.href = '/activate';
			});
		});
	});
</script>