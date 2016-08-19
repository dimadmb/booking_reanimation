<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";

$current_user =JFactory::getUser();
$username =  $current_user->name;



$db = mysqli2::connect();

$fee = user::getFee();

?>

<h2 style="text-align: right">
	Приветствуем, <?=$username;?>

	<?php if (user::is_agent()){ ?>
	[<span style="color:#F00;">комиссия <?=$fee?> %</span>]
	<?php } ?>


</h2>
<div>

</div>
<nav class="uk-navbar">
	<ul class="uk-navbar-nav">

		<li><a href="/"><i class="fa fa-home"></i> Главная</a></li>
		<li><a href="/invoices"><i class="fa fa-list-ol"></i> Заявки</a></li>

		<li><a href="/report_agent"><i class="fa fa-list-ol"></i> Отчет агента</a></li>


	</ul>


	<?php
	$userToken = JSession::getFormToken();
	$button_logout =
		'<a class="uk-button uk-button-danger" href="index.php?option=com_users&task=user.logout&' .
		$userToken . '=1">Выйти <i class="fa fa-sign-out"></i></a>.';
	?>

	<div class="uk-navbar-content uk-navbar-flip uk-hidden-small">
		<div class="uk-button-group">
			<?=$button_logout?>

		</div>
	</div>

</nav>