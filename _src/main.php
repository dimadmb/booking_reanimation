<?php

ini_set('display_errors',1);

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/user.php";


$db = mysqli2::connect();






?>

<script>
	i18n = {
		months:['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		weekdays:['Пн','Вт','Ср','Чт','Пт','Сб','Вс']
	};
</script>

<link rel="stylesheet" href="/bower_components/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/uikit.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/datepicker.gradient.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.min.css">
<link rel="stylesheet" href="/bower_components/uikit/css/components/form-select.gradient.min.css">

<link rel="stylesheet" href="/css/findform.css">



<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/findform.php"; ?>


<div id="turs">

</div>


<?php




$url_key = "?pauth=".config::$keyVodohod;

$url_teplohod = "http://cruises.vodohod.com/agency/json-motorships.htm";

$url = $url_teplohod . $url_key;




//$data  = file_get_contents($url);
//$data = json_decode($data);
//foreach ($data as $key =>$item){
//
//    $sql = "insert into aa_teplohod set id= $key,
//type ='{$item->type}',
//name ='{$item->name}',
//code ='{$item->code}'
//";
//
//    $db->query($sql);
//
//}


$url_cruise=  "http://cruises.vodohod.com/agency/json-cruises.htm";

$url = $url_cruise . $url_key;

//$data  = file_get_contents($url);
//$data = json_decode($data);
//
//helper::var_dump_pre($data);
//
//foreach ($data as $key =>$tur){
//
//    $sql = "insert into aa_tur set id=$key,
//id_teplohod = {$tur->motorship_id},
//name='{$tur->name}',
//days = {$tur->days},
//
//date_start= STR_TO_DATE('{$tur->date_start}', '%Y-%m-%d'),
//date_stop = STR_TO_DATE('{$tur->date_stop}', '%Y-%m-%d'),
//
//availability_count = {$tur->availability_count}
//
//
//";
//    $db->query($sql);
//}
//


$turs = tur::getListTurs();

?>

<script id="tmplTurs" type="text/mustache">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . "/tmpl/turs.mustache";?>
</script>

<script src="/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/bower_components/lodash/dist/lodash.min.js"></script>

<script src="/bower_components/mustache.js/mustache.min.js"></script>
<script src="/bower_components/uikit/js/uikit.min.js"></script>
<script src="/bower_components/uikit/js/components/datepicker.min.js"></script>


<script src="/bower_components/uikit/js/components/form-select.min.js"></script>


<script src="/bower_components/StickyTableHeaders/js/jquery.stickytableheaders.js"></script>

<script src="/js/filter.js"> </script>

<script>

	jQuery(function($){

//		return;


		AA = {};
		AA.turs = '<?php echo json_encode($turs)?>';
		AA.turs = $.parseJSON(AA.turs);


		AA.turs = _.filter(AA.turs, {'is_delete' : 0});


		// исправить
		AA.lasting1 = $('#lasting1').val() *1 ;
		AA.lasting2 = $('#lasting2').val() *1 ;

		AA.min_out_day = '<?=$d1?>';
		AA.max_out_day = '<?=$d2?>';




		AA.tmpl = {
			turs : document.getElementById('tmplTurs').innerHTML
		};

		var full_turs_content = Mustache.render(AA.tmpl.turs, {turs : AA.turs } );
		$('#turs').html(full_turs_content);
		$('#amountTur').html( AA.turs.length);



		$('.table_turs').stickyTableHeaders({
			marginTop: -282,
		});

		filterTur();










	});

</script>

