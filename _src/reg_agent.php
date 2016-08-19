<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";

ini_set('display_errors',1);
?>

<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.gradient.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.gradient.min.css">


<!--<script src="/bower_components/uikit/js/components/datepicker.min.js"></script>-->
<!--<script src="/bower_components/uikit/js/components/form-select.min.js"></script>-->

<!--<script src="/bower_components/json2/json2.js"></script>-->


<!--<script src="/bower_components/uikit/js/components/notify.min.js"></script>-->

<link rel="stylesheet" href="/bower_components/uikit/css/components/notify.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/notify.gradient.min.css">

<style type="text/css">
	.signup-agent-form td {
		padding: 3px;
	}

	.signup-agent-form input{
		width : 50%;
	}

	.divFiles a {
		display: block;
		font-size: 14px;;
	}
</style>

<?php

if (
isset($_POST['name'])

) {

	$newuser = new JUser;


	$newuser->name = $_POST['name'];
	$newuser->username = $_POST['email'];
	$newuser->password = JUserHelper::hashPassword($_POST['pass']);
	$newuser->email = $_POST['email'];

	$newuser->registerDate = date('Y-m-d H:i:s');

	$newuser->block = 1;
	$newuser->sendEmail = 1;


	$newuser->groups = array(10);


	$save = $newuser->save();

	if ($save){

	$db = mysqli2::connect();

	$sql ="insert into aa_agent set \n

user_id = ?,

bank = ?,
rs = ?,
ks = ?,
bik = ?,
inn = ?,
kpp = ?,
ur_address = ?,
fakt_address = ?,
phone = ?
";

	$stmt = $db->stmt_init();
	$stmt->prepare($sql);
	$stmt->bind_param('isssssssss',

			$newuser->id,

			$_POST['bank'],
			$_POST['rs'],
			$_POST['ks'],
			$_POST['bik'],
			$_POST['inn'],
			$_POST['kpp'],
			$_POST['ur_address'],
			$_POST['fakt_address'],
			$_POST['phone']


	);

	$stmt->execute();
		?>
			<div class="uk-alert uk-alert-success">
				Пользователь успешно создан!
			</div>
			<div>
				<a href="/">Перейти на главную страницу</a>
			</div>
		<?php
			return;
	} else {

		?>
		<div class="uk-alert uk-alert-danger">
			Пользователя не удалось создать!
			<ul>
				<?php
				$errors = 	$newuser->getErrors();
				foreach ( $errors as $e){
					echo "<li>".$e;
				}

				echo '</ul>';
				echo '</div>';
	}



}

?>

<h1>Регистрация агентства</h1>

<div class="divFiles">
	<a href="/docs/agent.doc"><i class="fa fa-file-word-o" aria-hidden="true"></i> Агентский договор</a>
	<a href="/docs/add1.doc"><i class="fa fa-file-word-o" aria-hidden="true"></i> Приложение №1 к
		агентскому договору</a>
	<a href="/docs/add2.doc"><i class="fa fa-file-word-o" aria-hidden="true"></i> Приложение №2 к
		агентскому договору</a>
	<a href="/service/report/blank_report_agent.xlsx">
		<i class="fa fa-file-excel-o" aria-hidden="true"></i> Приложение №3 к агентскому договору
	</a>


</div>


<div class="uk-alert uk-alert-danger">
	Все поля обязательны для заполнения!
</div>

<form class="uk-form signup-agent-form" action="/signup" method="post">

	<table style="width: 100%;">
		<tr>
			<td style="width: 180px;">Наименование агентства</td>
			<td><input class="validate[required]"

			           <?php if (isset($_POST['name'])) echo 'value="'. htmlspecialchars($_POST['name']).'"'; ?>

			           name="name" type="text" placeholder="Наименование агентства"></td>
		</tr>

		<tr>
			<td>Наименование банка</td>
			<td><input class="validate[required]"

						<?php if (isset($_POST['bank'])) echo 'value="'. htmlspecialchars($_POST['bank']).'"'; ?>
					name="bank" type="text" placeholder="Наименование банка"></td>
		</tr>

		<tr>
			<td>Расчетный счет</td>
			<td><input class="validate[required,minSize[20]]"
						placeholder="XXXXXXXXXXXXXXXXXXXX"
						<?php if (isset($_POST['rs'])) echo 'value="'. htmlspecialchars($_POST['rs']).'"'; ?>
			           name="rs" type="text" placeholder="Расчетный счет" maxlength="20"></td>
		</tr>

		<tr>
			<td>Кор.счет</td>
			<td><input class="validate[required,minSize[20]]"
						placeholder="301XXXXXXXXXXXXXXXXX"

						<?php if (isset($_POST['ks'])) echo 'value="'. htmlspecialchars($_POST['ks']).'"'; ?>
			            name="ks" type="text" placeholder="Кор.счет" maxlength="20" ></td>
		</tr>
		<tr>
			<td>БИК</td>
			<td><input class="validate[required]"

						<?php if (isset($_POST['bik'])) echo 'value="'. htmlspecialchars($_POST['bik']).'"'; ?>
			           name="bik"  type="text" placeholder="XXXXXXXXX" maxlength="9"></td>
		</tr>

		<tr>
			<td>ИНН</td>
			<td><input class="validate[required]"

						<?php if (isset($_POST['inn'])) echo 'value="'. htmlspecialchars($_POST['inn']).'"'; ?>
			           name="inn" type="text" placeholder="ИНН"></td>
		</tr>
		<tr>
			<td>КПП</td>
			<td><input class="validate[required]"

						<?php if (isset($_POST['kpp'])) echo 'value="'. htmlspecialchars($_POST['kpp']).'"'; ?>
			           name="kpp" type="text" placeholder="КПП"></td>
		</tr>
		<tr>
			<td>Юридический Адрес</td>
			<td><input class="validate[required]"
						<?php if (isset($_POST['ur_address'])) echo 'value="'. htmlspecialchars($_POST['ur_address']).'"'; ?>
			           name="ur_address"  type="text" placeholder="Юридический Адрес"></td>
		</tr>
		<tr>
			<td>Фактический адрес</td>
			<td><input class="validate[required]"
						<?php if (isset($_POST['fakt_address'])) echo 'value="'. htmlspecialchars($_POST['fakt_address']).'"'; ?>
			           name="fakt_address" type="text" placeholder="Фактический адрес"></td>
		</tr>
		<tr>
			<td>Телефон</td>
			<td><input class="validate[required]"
						<?php if (isset($_POST['phone'])) echo 'value="'. htmlspecialchars($_POST['phone']).'"'; ?>
			           name="phone" type="text" placeholder="+7 (XXX) XXX-XX-XX"></td>
		</tr>

		<tr>
			<td>
				Email <br>
				<span style="font-style: italic; color:#CCC;">Будет использоваться как логин при входе в личный кабинет</span>
			</td>
			<td><input class="validate[required,custom[email]]"
						<?php if (isset($_POST['email'])) echo 'value="'. htmlspecialchars($_POST['email']).'"'; ?>
			           name="email" type="text" placeholder="user@mail.ru"></td>

		</tr>

		<tr>
			<td>Пароль</td>
			<td><input class="validate[required,minSize[8]]"
			           id="pass"
						<?php if (isset($_POST['pass'])) echo 'value="'. htmlspecialchars($_POST['pass']).'"'; ?>
			           name="pass" type="text" placeholder="не менее 8 символов"></td>
		</tr>

		<tr>
			<td>Пароль еще раз</td>
			<td><input class="validate[required,minSize[8],equals[pass]"
						<?php if (isset($_POST['pass2'])) echo 'value="'. htmlspecialchars($_POST['pass2']).'"'; ?>
			           name="[pass2]" type="text" placeholder="не менее 8 символов"></td>
		</tr>
	</table>

	<div>
		<button class="button-signup uk-button uk-button-success">
			Регистрация
		</button>
	</div>
</form>



<div class="uk-alert uk-alert-danger">
	Внимание! Бронивание кают возможно после подтверждения учетной записи менеджером. После регистрации свяжитесь с менеджером.
</div>


<script src="/bower_components/validationEngine/js/jquery.validationEngine.js"></script>
<script src="/bower_components/validationEngine/js/languages/jquery.validationEngine-ru.js"></script>

<script>
	jQuery(function($){

		$(".signup-agent-form").validationEngine({
			focusFirstField : false,
			showPrompts: false,
			onFieldSuccess: function(e){
				$(e).removeClass('uk-form-danger');
				$(e).addClass('uk-form-success');
			},
			onFieldFailure: function(e){
				$(e).removeClass('uk-form-success');
				$(e).addClass('uk-form-danger')
			}
		});

		$('.signup-agent-form').submit(function(){
			var res = $(".signup-agent-form").validationEngine('validate');
			return res;
		});
	});
</script>