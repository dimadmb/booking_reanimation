<?php

class invoice{
	static public function getData($id){

		$id = (int)$id;

		$sql ="select *,

 date_format(timecreate,'%d') as dd,
 date_format(timecreate,'%c') as mm,
 date_format(timecreate,'%Y') as yy \n
 from aa_schet where id= $id limit 1";
		$out =  mysqli2::sql2array($sql);
		return $out[0];
	}


	/**
	 *
	 * проверка физика на заполненость заказа
	*/

	static public function checkFiz($id){


		$out = true;

		$id = (int)$id;

		$db = mysqli2::connect();

		$sql = "select * from aa_buyer_fiz where id_schet = $id limit 1";
		$data =  mysqli2::sql2array($sql);
		$data = $data[0];


		$f1 = array('name', 'surname','patronymic','address','pass_seria','pass_num','pass_who','','');

		$f1_full = array('name', 'surname','patronymic','address','pass_seria','pass_num','pass_who','phone','email');

		$f2 = array('birthday', 'pass_date');

		foreach ($data as $key =>$item){
			if (in_array($key, $f1_full)){ if ($item=='') $out = false;}
			if (in_array($key, $f2)){ if ($item=='0000-00-00') $out = false;}
		}


		$sql = "select aa_place.* from aa_schet,aa_order,aa_place where \n

aa_schet.id = $id and aa_schet.id = aa_order.id_schet and aa_order.is_delete=0 and \n
aa_place.id_order = aa_order.id
";

		$mass =  mysqli2::sql2array($sql);

		foreach ($mass as $data){

			foreach ($data as $key =>$item){
				if (in_array($key, $f1)){ if ($item=='') $out = false;}
				if (in_array($key, $f2)){ if ($item=='0000-00-00') $out = false;}
			}
		}

		return $out;
	}

}