<style type="text/css">



	.divWrapper {
		background: rgba(255, 255, 255, 0.65) none repeat scroll 0 0;
		border-radius: 5px;
		box-shadow: 0 0 6px 2px rgba(0, 0, 0, 0.1);
		padding: 35px 30px 10px 30px;
		width: 400px;

		margin: 0 auto !important;
	}
</style>


<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";

ini_set('display_errors',1);


if (

	// #TODO сделать проверку полей

isset($_POST['email']) &&
isset($_POST['phone']) &&
isset($_POST['pass']) &&
isset($_POST['pass2']) &&
isset($_POST['g-recaptcha-response'])

) {

	$errors = array();
	helper::var_dump_pre($_POST);

	$email = $_POST['email'];
	$phone = $_POST['phone'];
	$pass = $_POST['pass'];
	$pass2 = $_POST['pass2'];

	if ($email=='') $errors[]='Неправильный email';
	if ($phone=='') $errors[]='Неправильный телефон';

	if (mb_strlen($pass) <8 ) $errors[]='Пароль меньше 8 символов';

	if ($pass !== $pass2) $errors[]='Пароли не совпадают';


	if (count($errors) > 0){

		echo '<div class="uk-alert uk-alert-danger"><ul>';
		foreach ($errors as $error){

			echo "<li> $error";
		}

		echo '</ul></div>';

	} else {


$newuser = new JUser;


$newuser->name = $_POST['email'];
$newuser->username = $_POST['email'];
$newuser->password = JUserHelper::hashPassword($_POST['pass']);
$newuser->email = $_POST['email'];

$newuser->registerDate = date('Y-m-d H:i:s');

$newuser->block = 0;
$newuser->sendEmail = 1;


$newuser->groups = array(11);


$save = $newuser->save();

if ($save){

	$db = mysqli2::connect();

	$sql = "insert into aa_fiz set \n
user_id = ?,
phone = ?
";

	$stmt = $db->stmt_init();
	$stmt->prepare($sql);
	$stmt->bind_param('is',
			$newuser->id,
			$phone
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
} else { ?>

<div class="uk-alert uk-alert-danger"> Пользователя не удалось создать!
	<ul>
		<?php
		$errors = $newuser->getErrors();
		foreach ($errors as $e) {
			echo "<li> $e";
		}

		echo '</ul></div>';
		}

}
} // if $_POST



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



<script src="/bower_components/validationEngine/js/jquery.validationEngine.js"></script>
<script src="/bower_components/validationEngine/js/languages/jquery.validationEngine-ru.js"></script>

<h1>Регистрация физического лица</h1>



		<div class="divWrapper">
<form class="uk-form signup-fiz-form" action="/signup_fiz" method="post">

	<div class="uk-alert uk-alert-danger">
		Все поля обязательны для заполнения!
	</div>


	<table style="margin: 0 auto;">

		<tr>
			<td style="width: 200px;">
				Email <br>
				<span style="font-style: italic; color:#CCC;">Будет использоваться как логин при входе в личный кабинет</span>
			</td>
			<td><input class="validate[required,custom[email]]"
					<?php if (isset($_POST['email'])) echo 'value="'. htmlspecialchars($_POST['email']).'"'; ?>
					   name="email" type="text" placeholder="user@mail.ru"></td>

		</tr>

		<tr>
			<td>Телефон</td>
			<td><input class="validate[required]"
					<?php if (isset($_POST['phone'])) echo 'value="'. htmlspecialchars($_POST['phone']).'"'; ?>
					   name="phone" type="text" placeholder="+7 (XXX) XXX-XX-XX"></td>
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
					   name="pass2" type="text" placeholder="не менее 8 символов"></td>
		</tr>

	<tr>
		<td colspan="2" style="text-align: center;">

	<div style="width :305px;height: 78px; margin : 15px auto;" class="g-recaptcha"

	     data-callback="enableBtn"
	     data-sitekey="6LcrXR4TAAAAALnbWyBxTn5vKNpeoqo9iWozsEOI">

<!--		<div style="background: #f9f9f9 none repeat scroll 0 0;border: 1px solid #d3d3d3; width:300px; height: 74px;"></div>-->

	</div>

			<div style="">
				<button class="button-signup uk-button uk-button-success uk-width-1-1 uk-button-large" disabled>
					Регистрация
				</button>
			</div>
		</td>
		</tr>

	</table>




</form>
		</div>


<script src='https://www.google.com/recaptcha/api.js'></script>
<script src="/bower_components/validationEngine/js/jquery.validationEngine.js"></script>
<script src="/bower_components/validationEngine/js/languages/jquery.validationEngine-ru.js"></script>
<script src="/bower_components/jquery.maskedinput/src/jquery.maskedinput.js"></script>
<script>
	function enableBtn(){
		console.log('ok cap');
		jQuery('.button-signup').removeAttr('disabled');
	}

	jQuery(function($){



		$('[name=phone]').mask("+7(999)999-99-99");

		$(".signup-fiz-form").validationEngine({
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

		$('.signup-fiz-form').submit(function(){
			var res = $(".signup-fiz-form").validationEngine('validate');
			return res;
		});
	});
</script>

