<?php
header("Content-Type: application/json;charset=utf-8");
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";


$action = $_GET['action'];

$db = mysqli2::connect();


switch ($action) {
	case 'checkFiz' :

		break;

	case 'doDelPay':

		if (!user::is_manager()) die(json_encode(array('success' => 0)));

		$db = mysqli2::connect();

		$id = (int)$_POST['id'];

		$sql = "update aa_1c set is_delete = 1 where id=$id and is_delete = 0 limit 1";

		$res = $db->query($sql);

		$out['success'] = $db->affected_rows;

		echo json_encode($out);
		break;

	case 'doPay':

		if (!user::is_manager()) die(json_encode(array('success' => 0)));

		$db = mysqli2::connect();

		$sql = "insert into aa_1c set  invoice_id= ?, amount = ?, date_pay= STR_TO_DATE( ?,'%d.%m.%Y'), num_pay = ?";


		$invoice = (int)$_POST['invoice'];
		$amount = $_POST['amount'];

		// запятую меняем на точку чтобы не парится

		$amount = str_replace(',', '.', $amount);


		$date_pay = $_POST['date_pay'];
		$num_pay = $_POST['num_pay'];


		$stmt = $db->stmt_init();
		$stmt->prepare($sql);
		$stmt->bind_param('idss', $invoice, $amount, $date_pay, $num_pay);

		$stmt->execute();
		$out['success'] = $stmt->affected_rows;


		$sql = "Select *, date_format(date_pay,'%d.%m.%Y') as date_pay2 \n

		from aa_1c where invoice_id = $invoice and is_delete=0 order by id DESC";

		$data = mysqli2::sql2array($sql);


		$out['data'] = $data;

		echo json_encode($out);
		break;

	case 'addPlace':

		if (!user::is_manager()) die(json_encode(array('success' => 0)));


		$hashids = new Hashids\Hashids(config::$order_salt, 32);

		$hash = $_POST['hash'];

		$hash = $hashids->decode($hash);

		if (count($hash) == 0) die(json_encode(array('success' => 0)));

		$id = $hash[0];

		$db = mysqli2::connect();

		$sql = "insert into aa_place set id_order= $id";

		$res = $db->query($sql);

		$out['success'] = $db->affected_rows;

		echo json_encode($out);


		break;

	case 'create':


		if (user::is_guest()) {

			$out['success'] = 0;
			$out['msg'] = 'Заявка не создана, пожалуйста зарегистрируйтесь или войдите под своим логином!';

			echo json_encode($out);
			exit;
		}


		$db = mysqli2::connect();

		$id_tur = (int)$_POST['id_tur'];

		$kautas = json_decode($_POST['data']);

		$current_user = JFactory::getUser();
		$user_id = $current_user->id;


		$sql = "insert into aa_schet set id_tur=$id_tur, user_id = $user_id, owner = $user_id ";
		$res = $db->query($sql);

		$id = $db->insert_id;

		// добавляем 2 типа покупателей

		// если покупатель агент - вставляем сразу его данные

		if (user::is_agent()) {


			$sql = "insert into aa_buyer_ur(
id_schet,
name,
bank,
rs,
ks,
bik,
inn,
kpp,
ur_address,
fakt_address,
phone
) (select $id,
name,
bank,
rs,
ks,
bik,
inn,
kpp,
ur_address,
fakt_address,
phone from \n
aa_agent,  jdr8t_users where aa_agent.user_id =  $user_id and  jdr8t_users.id= aa_agent.user_id limit 1)";

			$db->query($sql);

			$fee = user::getFee();

			$sql = "update aa_schet set buyer= 'ur', fee = $fee where id = $id limit 1";
			$db->query($sql);


		} else {

			$sql = "insert into aa_buyer_ur set id_schet = $id";
			$db->query($sql);

		}


		$sql = "insert into aa_buyer_fiz set id_schet = $id";
		$db->query($sql);

		// TODO  - добавить проверку кают


		$data = tur::getPrices($id_tur);
		$tariffs = $data['tariffs'];
		$all_kautas = $data['kautas'];

		$nums15 = $data['nums15'];


		foreach ($kautas as $kauta => $places) {
			$sql = "insert into aa_order set id_schet = $id, num ='$kauta', places=$places";
			$db->query($sql);
			$id_order = $db->insert_id;

			// находим тарифы у каюты
			$mass_prices = $all_kautas[$kauta];

			// пробегаемся по взрослым тарифам и ищем свое размещение
			$prices = $tariffs[0]->prices;

			foreach ($mass_prices as $index) {
				if (strpos($prices[$index]->rp_name, $places . '-') !== false) {
					$PRICE = $prices[$index]->price_value;
				}
			}

			// делаем скидку если есть
			$sql = "select type_discount from aa_tur where id = $id_tur limit 1";
			$res_tur = $db->query($sql);
			$r_tur = $res_tur->fetch_assoc();
			$type_discount = $r_tur['type_discount'];

			if ($type_discount == 'happy') {
				$sql =  "select * from aa_discount where id_tur = $id_tur and num = '$kauta' and type_discount='happy' limit 1";
				$res = $db->query($sql);
				if ($res->num_rows == 1){
					$PRICE = round($PRICE * (100 - config::$discount_happy) / 100);
				}
			} else if ($type_discount == 'special') {
				$sql =  "select * from aa_discount where id_tur = $id_tur and num = '$kauta' and type_discount='special' limit 1";
				$res = $db->query($sql);
				if ($res->num_rows == 1){
					$PRICE = round($PRICE * (100 - config::$discount_special) / 100);
				}
			}

			for ($i = 1; $i <= $places; $i++) {
				$sql = "insert into aa_place set id_order = $id_order,  price = $PRICE  ";
				$db->query($sql);
			}

		}

		$out['success'] = 1;
		$out['num'] = $id;
		$hashids = new Hashids\Hashids(config::$schet_salt, 32);
		$out['hash'] = $hashids->encode($id);

		echo json_encode($out);
		break;

	case 'save':

		$out =  array();

		$db = mysqli2::connect();

		$id_schet = $_POST['id_schet'];

		$hashids_schet = new Hashids\Hashids(config::$schet_salt, 32);
		$id_schet = $hashids_schet->decode($id_schet);

		if (count($id_schet) == 0) {
			break;
		}

		$id_schet = $id_schet[0];

		$buyer = $_POST['buyer'];

		if ($buyer == 'ur') {

			$stmt = $db->stmt_init();

			if (user::is_manager()) {
				$fee = (int)$_POST['fee'];
				$comment_manager = $_POST['comment_manager'];
				$user_id = $_POST['select_agent'];

				$sql = "update aa_schet set user_id = ?, buyer = ?,fee = ?, comment_manager = ?  where id = ? limit 1";
				$stmt->prepare($sql);
				$stmt->bind_param('isisi', $user_id, $buyer, $fee, $comment_manager, $id_schet);
				$stmt->execute();
			} else {

				$comment_user = $_POST['comment_user'];
				$sql = "update aa_schet set buyer = ?, comment_user = ?  where id = ? limit 1";
				$stmt->prepare($sql);
				$stmt->bind_param('ssi', $buyer,$comment_user, $id_schet);
				$stmt->execute();
			}

			$out['data_invoice'] = $stmt->affected_rows;


			$sql = "update aa_buyer_ur set \n
name =?,
bank = ?,
rs = ?,
ks = ?,
bik = ?,
inn = ?,
kpp = ?,
ur_address = ?,
fakt_address = ?,
phone = ? \n
where id_schet = ? limit 1";

			$stmt = $db->stmt_init();
			$stmt->prepare($sql);
			$stmt->bind_param('ssssssssssi',

				$_POST['name'],
				$_POST['bank'],
				$_POST['rs'],
				$_POST['ks'],
				$_POST['bik'],
				$_POST['inn'],
				$_POST['kpp'],
				$_POST['ur_address'],
				$_POST['fakt_address'],
				$_POST['phone'],
				$id_schet

			);

			$stmt->execute();

		} else if ($buyer == 'fiz') {

			$stmt = $db->stmt_init();

			if (user::is_manager()) {
				$comment_manager = $_POST['comment_manager'];

				$sql = "update aa_schet set buyer = ?, comment_manager = ?  where id = ? limit 1";
				$stmt->prepare($sql);
				$stmt->bind_param('ssi', $buyer, $comment_manager, $id_schet);
				$stmt->execute();
			} else {

				$comment_user = $_POST['comment_user'];
				$sql = "update aa_schet set buyer = ? , comment_user = ?  where id = ? limit 1";
				$stmt->prepare($sql);
				$stmt->bind_param('ssi', $buyer, $comment_user, $id_schet);
				$stmt->execute();
			}


			$sql = "update aa_buyer_fiz set \n
name = ?,
surname = ?,
patronymic = ?,
address = ?,
birthday = STR_TO_DATE(?,'%d.%m.%Y'),
pass_seria = ?,
pass_num = ?,
pass_date = STR_TO_DATE(?,'%d.%m.%Y'),
pass_who = ?,
phone = ?,
email = ? \n
where id_schet = ? limit 1";

			$stmt = $db->stmt_init();
			$stmt->prepare($sql);
			$stmt->bind_param('sssssssssssi',


				$_POST['name'],
				$_POST['surname'],
				$_POST['patronymic'],
				$_POST['address'],
				$_POST['birthday'],
				$_POST['pass_seria'],
				$_POST['pass_num'],
				$_POST['pass_date'],
				$_POST['pass_who'],
				$_POST['phone'],
				$_POST['email'],
				$id_schet);

			$stmt->execute();


		}
		$out['data_buyer'] = $stmt->affected_rows;
		echo json_encode($out);
		break;

	case 'remove':
		$hashids = new Hashids\Hashids(config::$schet_salt, 32);
		$hash = $_POST['hash'];
		$hash = $hashids->decode($hash);

		if (count($hash) == 0) {
			$out['success'] = 0;
			echo json_encode($out);
			exit;
		}

		$id = $hash[0];

		$db = mysqli2::connect();

		$sql = "update aa_schet set status = 0 where id= $id limit 1";
		$db->query($sql);

		$out['success'] = $db->affected_rows;

		echo json_encode($out);


		break;
}