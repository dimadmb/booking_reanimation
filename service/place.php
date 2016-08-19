<?php

//header("Content-Type: application/json;charset=utf-8");
header("Content-Type: text/html;charset=utf-8");

ini_set('display_errors',1);

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";

$hashids_place = new Hashids\Hashids(config::$place_salt,32);

$action  = $_GET['action'];

$db = mysqli2::connect();


switch($action) {
	case 'save':
		$id =$_POST['id'];
		$id = $hashids_place->decode($id);


		if (count($id)==0){ exit;}

		$id= $id[0];

		if  (user::is_manager()) {
			$price = $_POST['price'];
		}

		$name= $_POST['name'];
		$surname= $_POST['surname'];
		$patronymic= $_POST['patronymic'];
		$birthday= $_POST['birthday'];
		$pass_seria= $_POST['pass_seria'];
		$pass_num= $_POST['pass_num'];
		$pass_date= $_POST['pass_date'];
		$pass_who= $_POST['pass_who'];

		$stmt = $db->stmt_init();



		if  (user::is_manager()) {

			$sql = "update  aa_place set price=?, name=?, surname=?,
patronymic=?, pass_seria=?,pass_num=?,pass_who=?,
birthday = STR_TO_DATE(?,'%d.%m.%Y'),
pass_date = STR_TO_DATE(?,'%d.%m.%Y') where id = ? limit 1";

			$stmt->prepare($sql);
			$stmt->bind_param('sssssssssi',
					$price,$name,$surname,$patronymic,$pass_seria,$pass_num,$pass_who,$birthday,$pass_date,$id
			);

		} else if  (user::is_agent() || user::is_fiz() ){

			$sql = "update  aa_place set \n
name=?,
surname=?,
patronymic=?,
pass_seria=?,
pass_num=?,
pass_who=?,
birthday = STR_TO_DATE(?,'%d.%m.%Y'),
pass_date = STR_TO_DATE(?,'%d.%m.%Y') where id = ? limit 1";

			$stmt->prepare($sql);
			$stmt->bind_param('ssssssssi',
					$name,$surname,$patronymic,$pass_seria,$pass_num,$pass_who,$birthday,$pass_date,$id);


		}


		$stmt->execute();







		echo $stmt->affected_rows;






	break;
}
