<?php

header("Content-Type: application/json;charset=utf-8");

ini_set('display_errors',1);

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";

$action  = $_GET['action'];

$db = mysqli2::connect();

switch($action) {
	case 'activate':

		$fee = (int)$_POST['fee'];
		$num_dog = (int)$_POST['num_dog'];

		$date_dog =$_POST['date_dog'];

		$user_id = (int)$_POST['user_id'];

		$sql = "update aa_agent set fee = $fee , num_dog= $num_dog,
date_dog = STR_TO_DATE('date_dog','%d.%m.%Y') where user_id = $user_id limit 1 ";

		$db->query($sql);

		$sql = "update jdr8t_users set block=0 where id = $user_id limit 1 ";
		$db->query($sql);


		break;

	case 'save':

		$user_id = (int)$_POST['user_id'];

		$name = $_POST['name'];
		$value = $_POST['value'];


		$sql = "update aa_agent set $name = '$value' where user_id = $user_id limit 1";

		if ($name == 'date_dog'){

			$sql = "update aa_agent set $name = STR_TO_DATE('$value','%d.%m.%Y') where user_id = $user_id limit 1";

		}

		$db->query($sql);

		$out['success'] = $db->affected_rows;

		echo json_encode($out);

		break;


	case 'getDataAgentForManager':

		$out['success'] = 0;

		if (!user::is_manager()){ die(json_encode($out)); }

		$id = (int)$_POST['id'];

		$sql = "select * from aa_agent where user_id = $id limit 1";
		$data  = mysqli2::sql2array($sql);

		$data = $data[0];

		$out['success'] = 1;
		$out['data']  = $data;

		echo json_encode($out);


		break;


}


