<?php
header("Content-Type: text/html;charset=utf-8");

ini_set('display_errors', 1);

?>


<link rel="stylesheet" href="/bower_components/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/uikit.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.gradient.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.gradient.min.css">


<style>

	/*потом удалить*/
	#aside{
		display: none;
	}

	#content{
		width: 100%;;
	}

	.old_price {
		font-weight: normal;;
		text-decoration: line-through;
		color: #ccc;;
	}

	.reChangeBackground th {
		background: #999;
		color: #FFF;
		vertical-align: middle;
	}

	table.kautas tr:hover td span,
	table.kautas tr:hover td {
		/*background: #ccc;*/
		/*color :#FFF;*/
	}

	#table_request {
		width: 100%;
		border-collapse: collapse;

	}

	#table_request td {
		width: 50%;
		padding: 8px;;
	}

	#table_request input,
	#table_request select {
		width: 100%;;
	}

	#modalBuy button {
		margin-top: 30px;
		display: none;
	}

	.doDelete {
		width: 70px;;
		display: none;
	}

	.doBron button{
		font-weight: bold;
	}

	.turInfo td {
		padding: 3px;
	}

	#modalGuest form{
		float:left;
	}

	.nobr{
		white-space: nowrap;
	}
</style>


<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tools.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";



$db = mysqli2::connect();

$id_tur = (int)$_GET['tur'];

if ($id_tur == 0) return false;




$tur = tur::getData($id_tur);




//$url_key = "?pauth=" . config::$keyVodohod;
//$url_teplohod = "http://cruises.vodohod.com/agency/json-prices.htm";
//
//$url = $url_teplohod . $url_key . "&cruise=$id_tur";
//
//$data = file_get_contents($url);
//$data = json_decode($data);
//
//$room_all = $data->room_all;
//$tariffs = $data->tariffs;
//
//$kautas = array();
//
//foreach ($room_all as $id_price => $rooms) {
//	foreach ($rooms as $room) {
//		$kautas[$room][] = $id_price;
//	}
//}

?>

<div style="padding: 10px; background:#f9f9f9; border: 2px #CCC solid; ">

	<table class="turInfo">
		<tr>
			<td><i class="fa fa-ship" aria-hidden="true"></i> Теплоход</td>
			<td><span id="ship_name_original"><?= $tur['teplohod_name'] ?></span></td>
		</tr>
		<tr>
			<td><i class="fa fa-road" aria-hidden="true"></i> Круиз</td>
			<td>
				<?php if ($tur['type_discount'] == 'happy') { ?>
					<i class="fa fa-smile-o is_hot" title="Счастливый круиз - скидка 20%"></i>
				<?php } ?>
				<span id="name_tur_original">
                 <?= $tur['name'] ?>
            </span>

			</td>
		</tr>
		<tr>
			<td><i class="fa fa-calendar" aria-hidden="true"></i> Даты</td>
			<td id="date_tur_original"><?= $tur['date_start2'] ?> - <?= $tur['date_stop2'] ?></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
		</tr>
	</table>

</div>

<?php
$data = tur::getPrices($id_tur);

if ($data === false){ ?>
	<h1>Продажи путевок на выбранный тур завершены</h1>
	<a href="/"><i class="fa fa-undo" aria-hidden="true"></i> Вернуться на главную</a>
	<?php
	return;
}

$tariffs = $data['tariffs'];
$kautas = $data['kautas'];
$nums15 = $data['nums15'];


// купленные каюты
$sql ="select aa_order.num , aa_schet.id as schet from aa_schet, aa_order where \n
aa_schet.id =  aa_order.id_schet and aa_schet.id_tur = {$tur['id']} and status = 1 ";

$res_buy = $db->query($sql);

$buys = array();
while ($r_buy = $res_buy->fetch_assoc()){ $buys[ $r_buy['num'] ]  =  $r_buy['schet']; }

?>



<table class="kautas uk-table uk-table-striped">

	<thead>
	<tr>
		<th>Hомер</th>
		<th>Категория</th>
		<th>Палуба</th>
		<!--        <th>Борт</th>-->
		<th>1-мест<br>размещение</th>
		<th>2-мест<br>размещение</th>
		<th>3-мест<br>размещение</th>
		<th>4-мест<br>размещение</th>
		<th></th>
	</tr>
	</thead>

	<tbody>

	<?php

	$side = array(0 => 'Левый', 'Правый');

	$tarif_name = array(
		'Тариф Взрослый'=> '(взр)',
		'Тариф Детский' => '(дет)',
		'Тариф Пенсионный' => '(пенс)',
		'Тариф Школьный' => '(школ)',
		'Тариф Студенческий' => '(студент)'
	);




	foreach ($kautas as $num => $id_prices) {
		$prices = $tariffs[0]->prices;
		$data = $prices[$id_prices[0]];

		?>
		<tr>
			<td><?= $num ?></td>
			<td><?= $data->rt_name ?></td>
			<td><?= $data->deck_name ?></td>


			<?php

			// ищем 4 цены

			$category = array();

			foreach ($id_prices as $id) {

				foreach ($tariffs as $key => $tariff) {

					$tariff_name =  $tariff->tariff_name;
					$prices = $tariff->prices;
					$data = $prices[$id];

					if (strpos($data->rp_name, '1-') !== false) {
						$category[1][$tariff_name] = $data;
					} else if (strpos($data->rp_name, '2-') !== false) {
						$category[2][$tariff_name] = $data;
					} else if (strpos($data->rp_name, '3-') !== false) {
						$category[3][$tariff_name] = $data;
					} else if (strpos($data->rp_name, '4-') !== false) {
						$category[4][$tariff_name] = $data;
					}
				}
			}

			$places = array();
			for ($i = 1; $i <= 4; $i++) {

				$st = '-';
				if (isset($category[$i])) {

					$places[] = $i;
					$st = '';
					foreach ($category[$i] as $tarif_index => $cat) {

						$price = $cat->price_value;

						if ($tur['type_discount'] == 'happy' &&
								in_array($num, $nums15) &&
								$price > 0) {
							$priceNEW = round($price *  (100- config::$discount_happy)/100 );
							$price = tools::nf($price);
							$priceNEW = tools::nf($priceNEW);

							$st .= "<span class='old_price'>$price</span><br>";
							$st .= "<span class='nobr'>$priceNEW руб. {$tarif_name[$tarif_index]}</span><br>";

						} else if ($tur['type_discount'] == 'special' &&
								in_array($num, $nums15) &&
								$price > 0) {

								$priceNEW = round($price * (100- config::$discount_special)/100 );
								$price = tools::nf($price);
								$priceNEW = tools::nf($priceNEW);

								$st .= "<span class='old_price'>$price</span><br> $priceNEW ";
								$st .= " руб. {$tarif_name[$tarif_index]}<br>";

						} else {
							$st .= $price;
							$st .= " руб. {$tarif_name[$tarif_index]}<br>";
						}


					}

				} ?>

				<td><?= $st ?>
				</td>


				<?php
			}
			?>

			<td>
				<?php
				if ($tur['type_discount'] == 'happy' && in_array($num, $nums15)) {
					$class = 'uk-button-success';
					$Text = 'Купить';
					$typeOrder = 'Buy';


				} else if ($tur['type_discount'] == 'special' && in_array($num, $nums15)) {
					$class = 'uk-button-success';
					$Text = 'Купить';
					$typeOrder = 'Buy';

				} else {
						$class = 'uk-button-primary';
						$Text = 'Заявка';
						$typeOrder = 'Request';

				}


				if (user::is_manager()){

					$class = 'uk-button-success';
					$Text = 'Купить';
					$typeOrder = 'Buy';

				}

				if (isset($buys[$num])){

					?>
						<button class="uk-button" disabled >

							<?php if (user::is_manager()){ ?>
							#<?=$buys[$num]?>

							<?php } else if (user::is_agent()){ ?>

							бронь

							<?php } ?>


						</button>
					<?php
				} else {


					echo "<button
data-places = " . implode(',', $places) . "
data-type-order='$typeOrder' data-num='$num' class='uk-button $class '>$Text</button>";
				}


				if ($typeOrder == 'Buy') {
					?>

					<button data-num="<?= $num ?>" class="doDelete uk-button uk-button-danger">
						<i class="fa fa-trash"></i>

					</button>
					<?php
				}

				?>
			</td>

		</tr>
		<?php
	} ?>

	</tbody>
</table>

<div id="modalGuest" class="uk-modal">

	<div class="uk-modal-dialog">
		<a class="uk-modal-close uk-close"></a>

		<h2>Пожалуйста, войдите под своим логином</h2>

		<?php require $_SERVER['DOCUMENT_ROOT'] . "/_src/login_form.php"; ?>

		<div style="clear: both;"></div>

		<h2>или зарегистрируйтесь</h2>

		<a style="margin-top: 5px; margin-left: 5px;" class="uk-button uk-button-primary" href="/signup_fiz">
			<i class="fa fa-user-plus" aria-hidden="true"></i> Регистрация для физических лиц</a>

		<a style="margin-top: 5px; margin-left: 15px;" class="uk-button uk-button-primary" href="/signup">
			<i class="fa fa-building" aria-hidden="true"></i> Регистрация для агентств</a>



		</div>
</div>

<div id="modalBuy" class="uk-modal">
	<div class="uk-modal-dialog">
		<a class="uk-modal-close uk-close"></a>

		<h2>Выберите размещение в каюте №<span class="headerSelectKauta"></span></h2>

		<button data-place='1' class="uk-button uk-button-success uk-width-1-1 uk-button-large">
			<b>1-местное размещение</b>
		</button>

		<button data-place='2' class="uk-button uk-button-success uk-width-1-1 uk-button-large">
			<b>2-местное размещение</b>
		</button>

		<button data-place='3' class=" uk-button uk-button-success uk-width-1-1 uk-button-large">
			<b>3-местное размещение</b>
		</button>

		<button data-place='4' class=" uk-button uk-button-success uk-width-1-1 uk-button-large">
			<b>4-местное размещение</b>
		</button>
	</div>
</div>

<div id="modalRequest" class="uk-modal">
	<div class="uk-modal-dialog">
		<a class="uk-modal-close uk-close"></a>

		<h2>Заявка на каюту №<span class="headerSelectKauta"></span></h2>

		<div class="data_tur" style="margin-top: -10px;"></div>

		<hr>

		<form class="uk-form">

			<input type="hidden" name="ship">
			<input type="hidden" name="way">
			<input type="hidden" name="date1">

			<input type="hidden" name="kauta">

			<table id="table_request">
				<tr>
					<td>Как Вас зовут</td>
					<td><input type="text" name="name"></td>
				</tr>
				<tr>
					<td>Телефон</td>
					<td>
						<input

								type="text" name="phone" placeholder="+7 (XXX) XXX-XX-XX">
					</td>
				</tr>
				<tr>
					<td>Email</td>
					<td><input type="text" name="email" placeholder="user @ mail.ru"></td>
				</tr>
				<tr>
					<td>Количество взрослых</td>
					<td>
						<select name="count_adult">

							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Количество детей</td>
					<td>
						<select name="count_child">
							<option value="0">0</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
                    <textarea placeholder="Пожелания, если имеются" name="comment"
                              style="width: 100%;resize: none;"
                    ></textarea>
					</td>

				</tr>


			</table>
		</form>

		<div style="text-align: right;">
			<button class="uk-button uk-button-primary sendRequest">
				<i class="fa fa-envelope"></i>
				<i class="fa fa-cog fa-spin" style="display: none;"></i>
				&nbsp;Отправить
			</button>
		</div>
	</div>
</div>




<script id="tmplDoBron" type="text/mustache">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . "/tmpl/doBron.mustache"; ?>
</script>

<!--<script src="/bower_components/jquery/dist/jquery.min.js"></script>-->
<script src="/bower_components/StickyTableHeaders/js/jquery.stickytableheaders.js"></script>

<!--<script src="/bower_components/uikit/js/uikit.min.js"></script>-->
<script src="/bower_components/mustache.js/mustache.min.js"></script>
<script src="/bower_components/jquery.maskedinput/src/jquery.maskedinput.js"></script>

<script src="/bower_components/json2/json2.js"></script>
<script>
	jQuery(function ($) {

		$('[name=phone]').mask("+7(999)999-99-99");


		AA = {selectKauta: {}};


		tmplDoBron = document.getElementById('tmplDoBron').innerHTML;

		contentTmplDoBron = Mustache.render(tmplDoBron);

//		$('table').stickyTableHeaders({
//			marginTop: -183,
//		});

		$(document).on('click','.buttonBron',function(){



			var url = '/service/invoice.php?action=create';
			var data = JSON.stringify(AA.selectKauta);
			$.post(url , {data:data, id_tur : <?=$tur['id']?> },function(response){
     			if (response.success ==0){
					alert(response.msg);
				} else{
					alert('Создан счет #' +response.num);
					url  = '/invoice/'+response.hash;
					console.log(url);
					document.location.href = url;
				}
			});


		});

		$(document).on('click','.sendRequest',function(){

			$(this).attr('disabled','disabled');
			$('.fa-envelope').hide();
			$('.fa-cog').show();


			var data = $('.uk-form:visible').serialize();



			var url  = '/service/mailer.php';

			$.post(url,data,function(response){


				$('.fa-envelope').show();
				$('.fa-cog').hide();

				if (response == 1){
					alert('Ваша заявка отправлена');
				}




				$(this).removeAttr('disabled');
				var modal = UIkit.modal("#modalRequest");
				if (modal.isActive()) {
					modal.hide();
				} else {
					modal.show();
				}


			});



		});

		$('.doDelete').click(function () {
			$(this).hide().closest('td').find('[data-type-order]').show();

			var num = $(this).data('num');
			delete AA.selectKauta[num];

			var mass = [];
			for (var i in AA.selectKauta) {
				var mesto = 'места';
				if (AA.selectKauta[i]  ==1){ mesto = 'место';}
				var st = i + ' [' + AA.selectKauta[i] + ' '+mesto+']';
				mass.push(st);
			}

			if (mass.length == 0) {
				$('.doBron').hide();
				// показать заявки
				$('[data-type-order=Request]').show();
			}
			else {$('.SelectKauta').html(mass.join(','));}


		});

		$(document).on('click', '[data-place]',function () {
			var place = $(this).data('place') * 1;

			var $buttonBron = $('[data-num="'+ AA.currentNum+'"]');

			$buttonBron.hide().closest('td').find('.doDelete').show();

			UIkit.modal("#modalBuy").hide();


			if ($('.doBron').length == 0) {
				$('body').append(contentTmplDoBron);
			} else {
				$('.doBron').show();
			}


			AA.selectKauta[AA.currentNum] = place;

			var mass = [];
			for (var i in AA.selectKauta) {

				var mesto = 'места';
				if (AA.selectKauta[i]  ==1){ mesto = 'место';}

				var st = i + ' [' + AA.selectKauta[i] + ' '+mesto+']';
				mass.push(st);
			}
			$('.SelectKauta').html(mass.join(', '));

			// скрыть заявки
			$('[data-type-order=Request]').hide();


			delete AA.currentNum;


		});


		$('button[data-type-order]').click(function () {
			$('.doBron').hide();
			var num = $(this).data('num');

			AA.currentNum = num;

			var type = $(this).data('type-order');

			$('.headerSelectKauta').html(num);

			var name_tur = $('#name_tur_original').html();
			var date_tur = $('#date_tur_original').html();
			var ship_name = $('#ship_name_original').html();

			$('.data_tur').html(
				'<b>' + date_tur + '</b> ' + name_tur
			);

			$('[name=ship]').val(ship_name);
			$('[name=way]').val(name_tur);
			$('[name=date1]').val(date_tur);
			$('[name=kauta]').val(num);


			$('#modalBuy button').hide();

			if (type == 'Buy') {

				<?php if (user::is_guest()) { ?>

					var modalGuest = UIkit.modal("#modalGuest");
					if (modalGuest.isActive()) {
						modalGuest.hide();
					} else {
						modalGuest.show();
					}

					return false;

				<?php } else { ?>


					var places = $(this).data('places');
					if (places != '') {

						places = places + '';

						places = places.split(',');
						for (var i = 0; i <= places.length; i++) {
							$('[data-place=' + places[i] + ']').show();
						}
					}

				<?php } ?>
			}





			var modal = UIkit.modal("#modal" + type);
			if (modal.isActive()) {
				modal.hide();
			} else {
				modal.show();
			}
		});
	});
</script>