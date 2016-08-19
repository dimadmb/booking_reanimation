<?php
/**
 * Created by PhpStorm.
 * User: Dronello
 * Date: 10.04.16
 * Time: 14:22
 */


header("Content-Type: text/html;charset=utf-8");
ini_set('display_errors',0);

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";


if (!isset($_POST['ship'])) exit;
if (!isset($_POST['way'])) exit;
if (!isset($_POST['name'])) exit;
if (!isset($_POST['phone'])) exit;

$mail = new PHPMailer();

$mail->CharSet = 'UTF-8';
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host = "smtp.yandex.ru"; // SMTP server
$mail->SMTPAuth = true;                  // enable SMTP authentication
$mail->SMTPSecure = "ssl";
$mail->Port = 465;                    // set the SMTP port for the GMAIL server
$mail->Username = "test-rech-agent@yandex.ru"; // SMTP account username
$mail->Password = 'qq12345678';        // SMTP account password
$mail->SetFrom('test-rech-agent@yandex.ru', 'Ваше речное агентство');



//$mail->addBCC('noandrew@mail.ru');


$mail->Subject="Заявка с сайта booking.rech-agent.ru";

ob_start();


?>

	
	<table style="border-collapse: collapse;">
		<tr>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Теплоход</td>
			<td colspan="3" style='padding:2px 5px 2px 5px; border:1px #ccc solid;'><?=$_POST['ship']?></td>
		</tr>
		<tr>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Маршрут</td>
			<td colspan="3" style='padding:2px 5px 2px 5px; border:1px #ccc solid;'><?=$_POST['way']?></td>
		</tr>
		<tr>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Даты</td>
			<td colspan="3" style='padding:2px 5px 2px 5px; border:1px #ccc solid;'><?=$_POST['date1']?></td>
		</tr>

		<tr>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Каюта</td>
			<td colspan="3" style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>№<?=$_POST['kauta']?></td>
		</tr>


		<tr>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Имя</td>
			<td colspan="3" style='padding:2px 5px 2px 5px; border:1px #ccc solid;'><?=$_POST['name']?></td>
		</tr>
		<tr>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Телефон</td>
			<td colspan="3" style='padding:2px 5px 2px 5px; border:1px #ccc solid;'><?=$_POST['phone']?></td>
		</tr>

		<tr>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Email</td>
			<td colspan="3" style='padding:2px 5px 2px 5px; border:1px #ccc solid;'><?=$_POST['email']?></td>
		</tr>

		<tr>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Кол-во взрослых</td>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'><?=$_POST['count_adult']?></td>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Кол-во детей</td>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'><?=$_POST['count_child']?></td>
		</tr>
		<tr>
			<td style='padding:2px 5px 2px 5px; border:1px #ccc solid;'>Комментарий</td>
			<td colspan="3" style='padding:2px 5px 2px 5px; border:1px #ccc solid;'><?=$_POST['comment']?></td>
		</tr>
	</table>
<?php

$body = ob_get_contents();
ob_end_clean();

$mail->MsgHTML($body);


$mail->AddAddress('info@rech-agent.ru', 'Речное агентство');
$mail->addBCC('noandrew@mail.ru', 'AAA');

if ($mail->Send()) echo '1';
