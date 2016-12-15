<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tools.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/invoice.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/Discount.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/libraries/vendor/autoload.php";

ini_set('display_errors',1);

$db = mysqli2::connect();

$hashids_schet = new Hashids\Hashids(config::$schet_salt,32);
$hashids_place = new Hashids\Hashids(config::$place_salt,32);
$hashids_order = new Hashids\Hashids(config::$order_salt,32);

if (!isset($_GET['invoice'])){

	if  (user::is_manager()){
	    require_once "invoices_list_admin.php";
	    return false;
	} else if (user::is_agent() || user::is_fiz()){

		require_once "invoices_list_user.php";
		return false;

	} else {
		header('Location: /');
	}

}

$hash = $_GET['invoice'];

$id = $hashids_schet->decode($hash);




if (count($id)==0){
	header('Location: /');
}

$id = $id[0];

//if ($id == 188) header('Location: /');
//if ($id == 199) header('Location: /');
//if ($id == 202) header('Location: /');

?>
<script>
	document.title = 'Заявка #<?=$id?>';
</script>
<?


$sql = "select * from aa_order where id_schet= $id";
$res_order = $db->query($sql);
//$r_order = $res_order->fetch_assoc();

//$id_tur = $r_order['id_tur'];




$sql ="select user_id, comment_manager, comment_user, permanent, permanent_request, seson_discount,
fee, buyer, date_format(timecreate,'%d.%m.%Y') as timecreate2 from aa_schet where id = $id limit 1";
$res_schet = $db->query($sql);
$r_schet = $res_schet->fetch_assoc();

$fee = (int)$r_schet['fee'];

$permanent = $r_schet['permanent']*1;
$permanent_request = $r_schet['permanent_request']*1;
$seson_discount = $r_schet['seson_discount']*1;


if ($r_schet['buyer'] == 'ur'){

} else if ($r_schet['buyer'] == 'fiz'){

}

$sql_ur ="select * from aa_buyer_ur where id_schet = $id limit 1";
$sql_fiz ="select *,
date_format(birthday,'%d.%m.%Y') as birthday2,
date_format(pass_date,'%d.%m.%Y') as pass_date2 \n
from aa_buyer_fiz where id_schet = $id limit 1";

$res_buyer_fiz = $db->query($sql_fiz);
$r_buyer_fiz  = $res_buyer_fiz->fetch_assoc();

$res_buyer_ur = $db->query($sql_ur);
$r_buyer_ur  = $res_buyer_ur->fetch_assoc();


?>

<style type="text/css">


	.uk-form{
		border: 1px solid #ccc;
		padding: 5px;;
	}
	.uk-form>div{
		margin-top: 10px;;
	}

	.ur-form table td {
		padding: 4px;;
	}
	.ur-form table input {
		width: 100%;
	}

	.ur-form { display: none;}
	.uk-form , .fiz-form{
		margin-top: 15px;;
	}

	[name=comment_manager],
	[name=comment_user]
	{

		box-sizing: border-box;

		width: 100%;
		height: 150px;
		font-size: 20px;
		line-height: 25px;;
	}

	.div_select_agent{
		border  : 2px #ccc solid;
		margin: 10px 0px 10px 0px;
		padding: 10px;;
	}
</style>

<script>
	i18n = {
		months:['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		weekdays:['Пн','Вт','Ср','Чт','Пт','Сб','Вс']
	};
</script>

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

<link rel="stylesheet" href="/bower_components/chosen/chosen.css">
<script src="/bower_components/chosen/chosen.jquery.js"></script>

<h2>
	Заявка #<?=$id?> от <?=$r_schet['timecreate2']?>

	<button title="Аннулировать заявку"

			style="float:right;" class="uk-button uk-button-danger removeInvoice" data-hash="<?=$hash?>" >
		<i class="fa fa-trash" aria-hidden="true"></i>

	</button>
</h2>
<hr>

<?php if (user::is_fiz()){ ?>
<div class="uk-alert uk-alert-danger">
	Внимание! Счет и договор будут доступны после заполнения всех полей!
</div>
<?php } ?>





<div>
	<span>Покупатель: </span>
	<div data-uk-button-radio style="display: inline-block;">
		<button class="uk-button fiz-button">Физ. лицо</button>
		<?php if (user::is_manager() || user::is_agent()){ ?>
		<button class="uk-button ur-button">Агент (юр. лицо)</button>
		<?php } ?>
	</div>
	<div style="float:right;" >
	<a href="/service/report/invoice.php?hash=<?=$hash?>" class="uk-button uk-button-success"> Счет </a>
	</div>
</div>

<?php if (user::is_manager() || user::is_agent()){ ?>
	<form class="uk-form ur-form">
	<input type="hidden" name="buyer" value="ur">
	<input type="hidden" name="id_schet" value="<?=$hash?>">

	<h2>Покупатель - Агентство(юр.лицо)</h2>
	<div>

		<?php if (user::is_manager()){ ?>
			<div class="div_select_agent">
				Счет виден агентству
				<?php
				$sql ="select jdr8t_users.name , jdr8t_users.id from jdr8t_users,aa_agent where jdr8t_users.id = aa_agent.user_id";
				$data = mysqli2::sql2array($sql);
				?>
				<select class="select_agent" name="select_agent">
					<option value="280">Виден только менеджеру</option>
					<?php foreach ($data as $item){

						$selected = '';

						if ( $item['id'] == $r_schet['user_id']){
							$selected = ' selected ';
						}

						?>
						<option <?=$selected?> value="<?=$item['id']?>"><?=$item['name']?></option>
					<?php } ?>
				</select>

				<div style="font-style: italic; color: #ccc;">
					После выбора агентства, возможно заполнить все его параметры из базы данных автоматически
				</div>
			</div>
		<?php } ?>

		<table style="width: 100%;">


				<tr>
					<td>
						<span style="color:#f00; font-weight: bold;">
							Комиссия
						</span>
					</td>
					<td>
						<?php if (user::is_manager()) { ?>
						<input name="fee" value="<?=$fee?>" type="text" placeholder="Комиссия">
						<?php } else if (user::is_agent()) { ?>

							<span style="color:#f00; font-weight: bold;">
									<?=$fee?> %
								</span>
						<?php }?>
					</td>
				</tr>


			<tr>
				<td style="width: 180px;">Наименование агентства</td>
				<td><input value="<?=htmlspecialchars($r_buyer_ur['name'])?>"
							name="name" type="text" placeholder="Наименование агентства"></td>
			</tr>
			<tr>
				<td>Расчетный счет</td>
				<td><input value="<?=htmlspecialchars($r_buyer_ur['rs'])?>"
							name="rs" type="text" placeholder="Расчетный счет"></td>
			</tr>
			<tr>
				<td>Наименование банка</td>
				<td><input value="<?=htmlspecialchars($r_buyer_ur['bank'])?>"
							name="bank" type="text" placeholder="Наименование банка"></td>
			</tr>
			<tr>
				<td>Кор.счет</td>
				<td><input  value="<?=htmlspecialchars($r_buyer_ur['ks'])?>"
							name="ks" type="text" placeholder="Кор.счет"></td>
			</tr>
			<tr>
				<td>БИК</td>
				<td><input value="<?=htmlspecialchars($r_buyer_ur['bik'])?>"
							name="bik"  type="text" placeholder="БИК"></td>
			</tr>

			<tr>
				<td>ИНН</td>
				<td><input value="<?=htmlspecialchars($r_buyer_ur['inn'])?>"
							name="inn" type="text" placeholder="ИНН"></td>
			</tr>
			<tr>
				<td>КПП</td>
				<td><input value="<?=htmlspecialchars($r_buyer_ur['kpp'])?>"
							name="kpp" type="text" placeholder="КПП"></td>
			</tr>
			<tr>
				<td>Юридический Адрес</td>
				<td><input value="<?=htmlspecialchars($r_buyer_ur['ur_address'])?>"
							name="ur_address"  type="text" placeholder="Юридический Адрес"></td>
			</tr>
			<tr>
				<td>Фактический адрес</td>
				<td><input value="<?=htmlspecialchars($r_buyer_ur['fakt_address'])?>"
							name="fakt_address" type="text" placeholder="Фактический адрес"></td>
			</tr>
			<tr>
				<td>Телефон</td>
				<td><input value="<?=htmlspecialchars($r_buyer_ur['phone'])?>"
							name="phone" type="text" placeholder="+7 (XXX) XXX-XX-XX"></td>
			</tr>
		</table>
	</div>
</form>
<?php } ?>



<form class="uk-form fiz-form">

	<input  type="hidden" name="buyer" value="fiz">
	<input type="hidden" name="id_schet" value="<?=$hash?>">

	<h2>Покупатель - Физ. лицо


		<button  class="uk-button uk-button-primary dataBuyer2Turist">
			Покупатель <i class="fa fa-long-arrow-right" aria-hidden="true"></i> Турист
		</button>

		<a href="/service/report/contract.php?hash=<?=$hash?>"
		   style="float:right;"
		   class="uk-button uk-button-primary">
			<i class="fa fa-file-word-o" aria-hidden="true"></i> Договор
		</a>
	</h2>


	<div>
		ФИО

		<input value="<?=$r_buyer_fiz['surname']?>" type="text" name="surname" placeholder="Фамилия">
		<input value="<?=$r_buyer_fiz['name']?>" type="text" name="name" placeholder="Имя">
		<input value="<?=$r_buyer_fiz['patronymic']?>" type="text" name="patronymic" placeholder="Отчество">


	</div>

	<div>
		Адрес прописки<input value="<?=$r_buyer_fiz['address']?>"
		                 name="address" type="text" style="width: 600px;">
	</div>

	<div>
		Дата рождения
		<input type="text" name="birthday" value="<?php if ($r_buyer_fiz['birthday2'] != '00.00.0000') echo $r_buyer_fiz['birthday2'];?>"
		       style="width: 90px;" data-uk-datepicker="{format:'DD.MM.YYYY', i18n: i18n, minDate:'01.01.1910' }"
		       placeholder="Дата  рожд">

		Паспорт

		<input value="<?=$r_buyer_fiz['pass_seria']?>"
				type="text" name="pass_seria" placeholder="Серия" style="width: 50px;">
		<input value="<?=$r_buyer_fiz['pass_num']?>"
				type="text" name="pass_num" placeholder="Номер" style="width: 75px;">

		<input value="<?php if ($r_buyer_fiz['pass_date2'] != '00.00.0000') echo  $r_buyer_fiz['pass_date2']?>"
				type="text" name="pass_date"
		       style="width: 90px;" data-uk-datepicker="{format:'DD.MM.YYYY', i18n: i18n }"

		       placeholder="Выдан" style="width: 100px;">

	</div>

	<div>
		Кем выдан <input value="<?=$r_buyer_fiz['pass_who']?>"
				name="pass_who" type="text" style="width: 600px;">
	</div>

	<div>
		Телефон
		<input value="<?=$r_buyer_fiz['phone']?>"
				type="text" name="phone">
		Email
		<input value="<?=$r_buyer_fiz['email']?>"
				type="text" name="email">
	</div>

<!--	<div>-->
<!--		<span style="color:#F00; font-weight: bold">Цена</span>-->
<!--		<input type="text" style="width: 100px;" maxlength="6">-->
<!--	</div>-->

</form>

<?php
// возможные цены

$invoiceData = invoice::getData($id);

$data = tur::getPrices($invoiceData['id_tur']);

// купленные каюты
$sql ="select aa_order.num , aa_schet.id as schet from aa_schet, aa_order where \n
aa_schet.id =  aa_order.id_schet and aa_schet.id_tur = ".$invoiceData['id_tur']." and status = 1 ";

	//print_r($sql);

$res_buy = $db->query($sql);
$buys = array();
while ($r_buy = $res_buy->fetch_assoc())
{ 
	$buys[]  =  $r_buy['num']; 
}
//  список кают, которые можно добавить





//print_r($tur = tur::getData($invoiceData['id_tur']));

$all_kautas =  $data['kautas'];
$tariffs = $data['tariffs'];
$nums15 = $data['nums15'];

if (user::is_manager())
{

foreach($all_kautas as $num=> $val)
{
	if(in_array($num,$buys)) 	{		continue;	}
	$free_kautas[] = $num;
}

asort($free_kautas);



//print_r($free_kautas);
?>
<div>
<select class="addKautas" data-hash="<?=$hash?>"><?
foreach($free_kautas as $free_kauta)
{
	?>
	<option value="<?=$free_kauta?>"><?=$free_kauta?></option>
	<?
}
?>
</select> 
<button class="uk-button uk-button-primary addKauta">Добавить каюту</button>
</div>
<script>

</script>

<?php 
}	
	while ($r_order = $res_order->fetch_assoc()){

	$id_order = $r_order['id'];
	?>

	<div>
		<h3>Каюта #<?=$r_order['num']?>, <?=$r_order['places']?>-местн. размещение </h3>
	</div>

	<?php if (user::is_manager()) { ?>
		<div style="padding: 15px;">
			<button class="uk-button uk-button-primary addPlace"
			        data-num="<?=$r_order['num']?>"
			        data-hash="<?=$hashids_order->encode($id_order)?>" >
				<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить место в каюту #<?=$r_order['num']?>
			</button>
		</div>
	<?php } ?>



	<?php

	$sql = "Select *,

 date_format(pass_date,'%d.%m.%Y') as pass_date2,
 date_format(birthday,'%d.%m.%Y') as birthday2 \n
 from aa_place where id_order = $id_order";
	$res_place = $db->query($sql);

	while ($r = $res_place->fetch_assoc() ){ ?>

		<form class="uk-form place">


		<?php if (user::is_manager()) { ?>
			<div style="text-align: right;">

				<a href="/service/report/boarding_card.php?place=<?=$hashids_place->encode($r['id'])?>"
						class="uk-button uk-button-primary">
					Посадочный талон
				</a>
			</div>
			<div style="text-align: right;">

				<button  data-hash="<?=$hashids_place->encode($r['id'])?>"
						class="uk-button uk-button-danger removePlace">
					Удалить место
				</button>
			</div>			
			<?php } ?>


			<input type="hidden" name="id" value="<?=$hashids_place->encode($r['id'])?>" >

			<div>
				ФИО

				<input type="text" name="surname" placeholder="Фамилия" value="<?=$r['surname']?>">
				<input type="text"  name="name" placeholder="Имя" value="<?=$r['name']?>">
				<input type="text" name="patronymic" placeholder="Отчество" value="<?=$r['patronymic']?>">


			</div>

			<div>
				Дата рождения
				<input type="text" name="birthday"
				       style="width: 90px;" data-uk-datepicker="{format:'DD.MM.YYYY', i18n: i18n, minDate:'01.01.1910' }"

				       value="<?php if ($r['birthday2'] != '00.00.0000') echo  $r['birthday2'];?>"

				       placeholder="Дата  рожд">
				Паспорт

				<input type="text" name="pass_seria" placeholder="Серия" style="width: 50px;" value="<?=$r['pass_seria']?>">
				<input type="text" name="pass_num" placeholder="Номер" style="width: 75px;" value="<?=$r['pass_num']?>">

				<input type="text" name="pass_date"
				       style="width: 90px;" data-uk-datepicker="{format:'DD.MM.YYYY', i18n: i18n }"

				       placeholder="Выдан" style="width: 100px;"
				       value="<?php if ($r['pass_date2'] != '00.00.0000') echo  $r['pass_date2'];?>">

			</div>
			
			<div>
				Кем выдан <input  name="pass_who" type="text" style="width: 600px;" value="<?=$r['pass_who']?>">
			</div>

			<div>
				<span style="color:#F00; font-weight: bold">Цена</span>

				<?php if (user::is_manager()) { ?>
				<input type="text"  name="price" style="width: 100px;" maxlength="6" value="<?=$r['price']?>">

					<select class="changePrice">

					<?php
					$tarif_name = array(0 => '(взр)', '(дет)', '(пенс)', '(школ)');
					$mass_prices =  $all_kautas[  $r_order['num'] ];
					
					foreach ($tariffs as $key => $tariff){
						foreach ($mass_prices as $index ) {
							$data = $tariff->prices[$index];
							if (strpos($data->rp_name, $r_order['places']. '-') !== false) {


								$PRICE = $data->price_value;

								// делаем скидку если есть
								$sql = "select type_discount from aa_tur where id = {$invoiceData['id_tur']} limit 1";
								$res_tur = $db->query($sql);
								$r_tur = $res_tur->fetch_assoc();
								$type_discount = $r_tur['type_discount'];


								if ($type_discount =='happy'){

									$sql =  "select * from aa_discount where id_tur = {$invoiceData['id_tur']} \n
 											 and num = '{$r_order['num']}' and type_discount='happy' limit 1";
									$res = $db->query($sql);

									if ($res->num_rows==1){ $PRICE = round($PRICE *  (100- config::$discount_happy)/100 ); }
								}

								if ($type_discount =='special'){
									$sql =  "select * from aa_discount where id_tur = {$invoiceData['id_tur']} \n
 											 and num = '{$r_order['num']}' and type_discount='special' limit 1";
									$res = $db->query($sql);

									if ($res->num_rows==1) { $PRICE = round($PRICE * (100 - config::$discount_special) / 100);}
								}

								$title = $PRICE . ' '.$tariff->tariff_name;

								$selected = '';
								if ($r['price'] == $PRICE) $selected = " selected ";
								echo "<option $selected value='$PRICE'>$title</option>";
							}
						}
					}

					?>

					</select>


				<?php } else if (user::is_agent() || user::is_fiz()) { ?>

						<span><?=$r['price']?></span> руб.
				<?php } ?>
				<!--<br> Цена с учётом сезонной скидки <?= Discount::getSesonPercent() ?>%  
				<span style="font-weight:900;"><?=$r['price']* Discount::getSesonKoef()  ?></span> руб. <br>-->
				<br>
				
				
				
			</div>

		</form>

		<?php } ?>






<?php } ?>
<?
// полная стоимость
$query = "
select sum(price) as ss , aa_schet.id 
from aa_schet, aa_order, aa_place
where aa_place.id_order = aa_order.id 
and aa_order.id_schet = aa_schet.id 
and aa_order.is_delete = 0 
and aa_order.id_schet = ".$id."
group by aa_schet.id ";
$res_full_price = $db->query($query);
while ($r = $res_full_price->fetch_assoc()) {
	$full_price = $r['ss']; // полная стоимость
}
?>

<form class="permanent">

	<?
	if (!user::is_manager()) { ?>
<label>
	<input type="checkbox" style="margin:0;" id="permanent" name="permanent" <?= ($permanent > 0 ) ? "disabled checked" : ""; ?> <?= ($permanent_request > 0 ) ? "checked" : ""; ?>  >

	Постоянный клиент - дополнительная скидка <br>
	<span style="font-style:italic; color:red;">Оплата онлайн будет возможна после подтверждения скидки менеджером.</span>
</label>
	<? } ?>
<br>

	<?
	if (user::is_manager()) { ?>
	<?= ($permanent_request > 0 ) ? '<p style="color:red;">Запрос на подтверждение скидки постоянного клиента</p>' : ""; ?> 
	
	<label>
	<input type="checkbox" style="margin:0;" id="permanent_confirm" name="permanent_confirm" <?= ($permanent > 0 ) ? " checked" : ""; ?> >
	 Подтвердить скидку <input type="text" name="permanent_percent" value="5">%
	</label>
	
	<label> Сезонная скидка <input type="text" name="seson_discount" value="<?=$seson_discount;?>">%</label>
	<? }; ?>

</form>
<script>
	jQuery(function ($) {
		$('#permanent').change(function(){
			if( !(<?=$permanent;?> > 0) && $(this).prop("checked"))
			{
				$('a.pay').css({display:"none"});
			}
			else 
			{
				$('a.pay').css({display:"inline-block"});
			}
		});
		
		$(document).ready(function(){
			$('#permanent').trigger("change");
		})
		
	})
</script>

<br>

<?php if (user::is_manager()) { ?>
	<form name="comment">
		<div style="font-style: italic;">Комментарий к заявке ( виден только менеджерам)</div>
		<textarea name="comment_manager"><?= htmlspecialchars($r_schet['comment_manager']) ?></textarea>
	</form>

	<div style="font-style: italic;">Комментарий клиента:</div>

	<div>
		<?php if ($r_schet['comment_user']==''){ echo 'Нет комментария.';
		} else {  echo "<div style='font-weight: bold; margin-bottom: 10px;'>". htmlspecialchars($r_schet['comment_user']) . "</div>";}

		?>
	</div>



<?php } else { ?>

	<form name="comment">
		<div style="font-style: italic;">Комментарий к заявке</div>
		<textarea name="comment_user"><?= htmlspecialchars($r_schet['comment_user']) ?></textarea>
	</form>
<?php } ?>


<div>
	<button class="uk-button uk-button-success uk-width-1-1 uk-button-large saveInvoice">
		Сохранить
	</button>

	<a href="/service/report/invoice.php?hash=<?=$hash?>"
	   style="margin-top: 20px;" class="uk-button uk-button-primary uk-width-1-1 uk-button-large ">
		Получить счет
	</a>
	


	<a target="_blank" href="/pay2/confirm.php?id=<?=$id;?>" style="margin-top: 20px;" class="uk-button uk-button-success uk-width-1-1 uk-button-large pay saveInvoice">
		Оплатить онлайн  
	</a>

	

	<?php if (user::is_fiz() || user::is_manager()) { ?>

	<a href="/service/report/contract.php?hash=<?=$hash?>"
	style="margin-top: 20px;"  class="uk-button uk-button-primary uk-width-1-1 uk-button-large ">
		Получить договор
	</a>


	<?php } ?>


</div>

<script>
	jQuery(function ($) {

		$('.select_agent').chosen({
			search_contains : true,
			width : '600px'
		});

		$('.dataBuyer2Turist').click(function(){
			if (!confirm('Перенести данные покупателя в первого туриста?\nДанные покупателя будут потеряны!')){
				return false;
			}

			$('.fiz-form input[type=text]').each(function(){
				var name = $(this).attr('name');
				var v = $(this).val();
				$('.place:eq(0) [name='+name+']').val( v );
			});
			return false;
		});

		$('.select_agent').change(function(){

			var id  = $(this).val();

			// если менеджер - ничо не делаем
			if (id == 280){ return false;}

			if (!confirm('Заполнить данные покупателя данными агента? Старые данные покупателя будут перезаписаны!')){
				return false;
			}

			var id  = $(this).val();
			var url ="/service/user.php?action=getDataAgentForManager";
			$.post(url,{id:id},function(response){
				if (response.success == 0 ){ return false;}

				for (var i in response.data){
					var $el = $('.ur-form input[type=text][name='+i+']');

					if ( $el.length>0){
						var item = response.data[i];
						$el.val(item);
					}
				}


				$('.ur-form input[type=text][name="name"]').val( $('.select_agent').find('option:selected').html() );



			});

		});

		
		$('.addKauta').click(function(){
			var num = $('.addKautas').val();
			//console.log(val);
			var url = "/service/invoice.php?action=addKauta";
			var hash = $('.addKautas').data('hash');
			console.log(hash);
			$.post(url,{num : num, hash : hash },function(response){
				
				//console.log(response.success);
				//console.log(response.success);
				
				if (response.success == 1){
					alert('Каюта успешно добавлена');
					location.reload();
				} else{
					alert('Каюта не добавлена');
				}

			});
		});
		
		$('.removePlace').click(function(){

			//var num = $(this).data('num'); // номер каюты
			

			if (!confirm('Удалить место? ')){ return false;}


			var url = "/service/invoice.php?action=removePlace";
			var hash = $(this).data('hash');
			
			//console.log(hash); 
			
			//return false;

			$.post(url,{hash : hash},function(response){
				
				console.log(response.success);
				
				if (response.success == 1){
					alert('Место успешно удалено');
					location.reload();
				} else{
					alert('Место не удалось удалить');
				}

			});


		});		
		
		$('.addPlace').click(function(){

			var num = $(this).data('num');

			if (!confirm('Добавить место в каюту #'+ num +'?')){ return false;}


			var url = "/service/invoice.php?action=addPlace";
			var hash = $(this).data('hash');

			$.post(url,{hash : hash},function(response){
				if (response.success == 1){
					alert('Место успешно добавлено');
					location.reload();
				} else{
					alert('Место не удалось добавить');
				}

			});


		});

		$('.removeInvoice').click(function(){
			if (!confirm('Подтверждаете удаление заявки #<?=$id?>?')){ return false;}

			var url = "/service/invoice.php?action=remove";

			var hash = $(this).data('hash');

			$.post(url,{hash : hash},function(response){

				if (response.success == 1){
					alert('Заявка успешно аннулирована');
					document.location.href='/';
				} else{
					alert('Заявку не удалось удалить');
				}

			});
		});

		$('.changePrice').change(function(){

			var $el = $(this)
			var price = $el.val();

			$el.closest('div').find('[name=price]').val(price);


		});



		$('.fiz-button').click(function(){
			$('.fiz-form').show();
			$('.ur-form').hide();
		});

		$('.ur-button').click(function(){
			$('.ur-form').show();
			$('.fiz-form').hide();
		});


		$('.saveInvoice').click(function () {
			var data3,data2,data, url;

			if ($('.fiz-button[aria-checked=true]').length==1){
				data = $('.fiz-form').serialize();
			} else if ($('.ur-button[aria-checked=true]').length==1){
				data = $('.ur-form').serialize();
			}


			if ($('form[name=comment]').length>0){
				data2 = $('form[name=comment]').serialize();
				data += '&'+data2;

			}
			
				data3 = $('form.permanent').serialize();
				data += '&'+data3;

			

			console.log(data);

			url = '/service/invoice.php?action=save';
			$.post(url, data, function (response) {


				if (response.data_buyer==1){
						UIkit.notify({
							message : 'Данные покупателя обновлены!',
							status  : 'info',
							timeout : 1000,
							pos     : 'top-center'
						});

				}

				if (response.data_invoice==1){
					UIkit.notify({
						message : 'Данные счета обновлены!',
						status  : 'info',
						timeout : 1000,
						pos     : 'top-center'
					});

				}
			});



			$('form.place').each(function () {
				data = $(this).serialize();
				url = '/service/place.php?action=save';
				$.post(url, data, function (response) {
					if (response==1){
						UIkit.notify({
							message : 'Обновлены данные туристов',
							status  : 'info',
							timeout : 1000,
							pos     : 'top-center'
						});
					}
				});
			});





	});

		<?php if ($r_schet['buyer'] == 'fiz'){ ?>
		$('.fiz-button').click();
		$('.fiz-button').attr('aria-checked','true');
		$('.fiz-button').addClass('uk-active');
		<?php } else if ($r_schet['buyer'] == 'ur'){ ?>
		$('.ur-button').click();
		$('.ur-button').attr('aria-checked','true');
		$('.ur-button').addClass('uk-active');

		<?php } ?>

	});
</script>
