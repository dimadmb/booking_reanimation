<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tools.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";

ini_set('display_errors',1);


?>
<link rel="stylesheet" href="/bower_components/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/uikit.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.gradient.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.gradient.min.css">

<style type="text/css">

	.iDate+i,
	.iAmount+i{
git
		position: relative;
		z-index: 1;
		left: -22px;
		top: 0px;
		color: #7B7B7B;
		/*cursor: pointer;*/
		width: 0;
	}

</style>


<?php

$hashids_user = new Hashids\Hashids(config::$user_salt,32);

$current_user =JFactory::getUser();

$hash = $hashids_user->encode($current_user->id);


?>

<h1>Отчет агента (Beta)</h1>

<div>

	<form class="uk-form" method="post" action="/">

		<input type="hidden" name="hash" value="<?=$hash?>" >
		Выберите период

		<select name="period">
			<?php for ($i = 4 ; $i<=10;$i++) { ?>
				<option value="<?=$i?>.<?=config::$year?>">
					<?=config::$month0[$i]?> <?=config::$year?>
				</option>
			<?php } ?>
		</select>

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


<div style=" margin-top: 15px; " >
	Пустые бланки отчетов
	<ul>
		<li><a href="/service/report/report_agent.xlsx">Пустой бланк для отчета агента</a></li>
		<li><a href="/service/report/report_agent_act.xlsx">Пустой бланк для акта выполненных работ</a></li>

	</ul>
</div>

<script src="/bower_components/uikit/js/components/datepicker.min.js"></script>
<script src="/bower_components/uikit/js/components/form-select.min.js"></script>

<script>
	i18n = {
		months:['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		weekdays:['Пн','Вт','Ср','Чт','Пт','Сб','Вс']
	};
</script>

