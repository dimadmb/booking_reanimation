<?php

header("Content-Type: application/json;charset=utf-8");

ini_set('display_errors',1);

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/tur.php";

$action  = $_GET['action'];

$db = mysqli2::connect();

 function curl_get_file_contents($URL)
{
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $URL);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) return $contents;
    else return FALSE;
}


switch($action){


    case 'switchType':

        $id = (int)$_POST['id'];
        $type = $_POST['type'];


        if (true || !helper::isAdmin()) {

            if ($type == 'off') {
                $url = "http://rech-agent.ru/api_in/cruise/happy/$id/0";
                curl_get_file_contents($url);
                $url = "http://rech-agent.ru/api_in/cruise/spec/$id/0";
                curl_get_file_contents($url);
            } else if ($type == 'happy') {
                $url = "http://rech-agent.ru/api_in/cruise/happy/$id/1";
                curl_get_file_contents($url);
            } else if ($type == 'special') {
                $url = "http://rech-agent.ru/api_in/cruise/spec/$id/1";
                curl_get_file_contents($url);
            }

        }


        $sql = "update aa_tur set type_discount = '$type' where id = $id limit 1";

        $res = $db->query($sql);

        $result = $db->affected_rows;

        $out['success'] = $result;

        //переводим каюты в соотв скидку

        if ($result == 1){
            if ( $type == 'happy' || $type =='special'){
                $sql = "update aa_discount set type_discount = '$type' where id_tur = $id";
                $res = $db->query($sql);
            }
        }



       // $out['error'] = $db->error;
        echo json_encode($out);
        break;


    case 'doHot':
        $id = $_POST['id'];
        $is_hot = $_POST['is_hot'];

        $sql = "update aa_tur set is_hot = $is_hot where id = $id limit 1";
        $res = $db->query($sql);
        $out['success'] = $db->affected_rows;
        echo json_encode($out);
    break;

    case 'doDiscount':
        $out = array();
        $num = (int)$_POST['num'];
        $id_tur = (int)$_POST['id_tur'];

        $sql = "select type_discount from aa_tur where id=$id_tur limit 1";
        $res = $db->query($sql);
        $r = $res->fetch_assoc();

        $type = $r['type_discount'];
        $sql = "select * from aa_discount where num=$num and id_tur=$id_tur and type_discount='$type' limit 1";
        $res = $db->query($sql);

        if ($res->num_rows==1){

            $sql = "delete from aa_discount where num=$num and id_tur=$id_tur and type_discount='$type' limit 1";
            $res = $db->query($sql);
            if ( $db->affected_rows==1){ $out['action'] = 'delete';}
        } else if ($res->num_rows==0){

            $sql = "insert into aa_discount set num=$num , id_tur=$id_tur, type_discount='$type' ";
            $res = $db->query($sql);
            if ( $db->affected_rows==1){$out['action'] = 'insert';}
        }

        $out['success'] = $db->affected_rows;

        echo json_encode($out);



    break;


    case 'getListKauta':

        //$id_tur = (int)$_POST['id_tur'];
        $id_tur = (int)$_REQUEST['id_tur'];

        $sql = "select aa_kauta.* from aa_tur,aa_kauta where 

aa_tur.id = $id_tur and aa_kauta.id_teplohod = aa_tur.id_teplohod order by num

";


        $nums = array();
        $sql_discount = "select num from aa_discount, aa_tur where \n
        aa_discount.type_discount=aa_tur.type_discount and id_tur=$id_tur and aa_tur.id =$id_tur";

//        echo $sql_discount;



        $res15 = $db->query($sql_discount);

        while ($r = $res15->fetch_assoc()){
            $nums[] = $r['num'];
        }


        $out = mysqli2::sql2array($sql);
//var_dump($sql);
        foreach ($out as $key=>$item){

            if (in_array($item['num'], $nums)){

                $out[$key]['selectKauta'] = 'selectKauta';
            } else {
                $out[$key]['selectKauta'] = '';
            }
        }

		//print_r($out) ;

        echo json_encode($out);
    break;

    case 'getTur':

        //$id_tur = (int)$_POST['id_tur'];
        $id_tur = (int)$_REQUEST['id_tur'];

        $sql = "select \n
date_format(date_start,'%d.%m.%y') as d1,
date_format(date_stop,'%d.%m.%y') as d2,

aa_tur.* , aa_teplohod.name as teplohod  from \n

aa_tur, aa_teplohod where aa_tur.id_teplohod =  aa_teplohod.id  and aa_tur.id= $id_tur limit 1";

//echo $sql;

        $out = mysqli2::sql2array($sql);
        echo json_encode($out[0]);
    break;

}
