<nav class="uk-navbar">
	<ul class="uk-navbar-nav">

		<li><a href="/"><i class="fa fa-home"></i> Главная</a></li>

		<li><a href="/invoices"><i class="fa fa-list-ol"></i> Заявки</a></li>
		<li><a href="/turs"><i class="fa fa-cog"></i> Туры</a></li>
		<li><a href="/activate"><i class="fa fa-users"></i> Агентства</a></li>

		<li><a href="/pay"><i class="fa fa-usd" aria-hidden="true"></i> Оплаты</a></li>

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