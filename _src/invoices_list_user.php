<?php
/**
 * Created by PhpStorm.
 * User: Dronello
 * Date: 11.04.16
 * Time: 1:00
 */


$current_user =JFactory::getUser();
$user_id =  $current_user->id;


$sql = "select aa_schet.id as id_schet ,

aa_schet.status,
aa_tur.id as id_tur,
aa_tur.name as way,
date_format(aa_schet.timecreate, '%H:%i <span class=gray_date>%d.%m</span>') as timecreate2,

date_format(date_start, '%d.%m') as d1,
date_format(date_stop, '%d.%m') as d2,
aa_teplohod.name as teplohod \n

 from aa_schet,aa_tur, aa_teplohod \n
 where aa_schet.id_tur = aa_tur.id and aa_tur.id_teplohod = aa_teplohod.id  and user_id  =$user_id \n

 order by aa_schet.id DESC";
$res = $db->query($sql);
?>

<style type="text/css">

	.gray_date{
		color : #999;
		font-size: 10px;
		font-weight: bold;
	}

	.trRemove{
		background: rgba(255,0,0,0.4) !important;
	}
</style>

<h1>Заявки</h1>

<table class="uk-table uk-table-striped">

	<thead>
	<tr>
		<th>Заявка</th>
		<th>Создан</th>

		<th>Даты</th>
		<th>Теплоход</th>
		<th></th>
	</tr>
	</thead>

<?php while ($r = $res->fetch_assoc()){

	$id = $hashids_schet->encode($r['id_schet']);

	$is_delete='';
	$hint = '';

	if ($r['status']==0){
		$is_delete = 'trRemove';
		$hint = " title='Удаленная заявка' ";

	}

	?>


	<tr <?=$hint?> class="<?=$is_delete?>">
		<td>#<?=$r['id_schet']?></td>
		<td><?=$r['timecreate2']?></td>
		<td><?=$r['d1']?> - <?=$r['d2']?>

			<a title="<?=$r['way']?>"
					target="_blank" href="/cruise/<?=$r['id_tur']?>">
				<i class="fa fa-external-link" aria-hidden="true"></i>

			</a>

		</td>
		<td><?=$r['teplohod']?></td>



		<td>
			<?php if ($r['status'] !=0 ){ ?>
			<a target="_blank" href="/invoice/<?=$id?>">Перейти</a>
			<?php } ?>
		</td>


	</tr>



<?php } ?>

	</table>




