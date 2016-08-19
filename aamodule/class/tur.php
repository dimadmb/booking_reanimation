<?php

//ini_set('display_errors',0);


class tur{

    /**
     * инфа из АПИ
     */

    static public function  getDataAPI($id_tur){

        $id_tur = (int)$id_tur;

        $url = "http://cruises.vodohod.com/agency/json-days.htm";
        $url_key = "?pauth=".config::$keyVodohod ."&cruise=$id_tur";

        $url = $url . $url_key;
        $data  = file_get_contents($url);
        $data = json_decode($data);

        return $data;
    }

    /**
     * инфа из БД
     */

    static public function  getData($id_tur){

        $db = mysqli2::connect();

        $id_tur = (int)$id_tur;


        $sql = "select aa_tur.* , aa_teplohod.name as teplohod_name,
date_format(date_start,'%d.%m.%y') as date_start2,
date_format(date_start,'%d.%m.%y') as d1,
date_format(date_stop,'%d.%m.%y') as date_stop2,
date_format(date_stop,'%d.%m.%y') as d2 \n
from aa_tur, aa_teplohod where \n

aa_tur.id_teplohod = aa_teplohod.id  and aa_tur.id = $id_tur limit 1";

        $tur = mysqli2::sql2array($sql);

        $tur = $tur[0];

        return $tur;



    }

    static public function getPrices($id_tur){

        $db = mysqli2::connect();

        $url_key = "?pauth=" . config::$keyVodohod;
        $url_teplohod = "http://cruises.vodohod.com/agency/json-prices.htm";

        $url = $url_teplohod . $url_key . "&cruise=$id_tur";


        $data = file_get_contents($url);
        $data = json_decode($data);





        if (!isset($data->room_all)){ return false;}

        $room_all = $data->room_all;
        $tariffs = $data->tariffs;

        $kautas = array();

        foreach ($room_all as $id_price => $rooms) {
            foreach ($rooms as $room) {
                $kautas[$room][] = $id_price;
            }
        }

        ksort($kautas);




        $nums15 = array();
        $sql15 = "select num from aa_discount, aa_tur where \n

aa_discount.type_discount = aa_tur.type_discount and \n
id_tur=$id_tur order by num DESC";

        $res15 = $db->query($sql15);

        while ($r = $res15->fetch_assoc()) {
            $nums15[] = $r['num'];
        }

        // сортируем чтобы выделенные каюты были наверху
        foreach ($nums15 as $item){
            $data = $kautas[$item];
            unset($kautas[$item]);
            $kautas = array($item=>$data) + $kautas;
        }

        $out['nums15'] = $nums15;

        $out['tariffs'] = $tariffs;
        $out['kautas'] = $kautas;


        return $out;

    }


    static public function getListTurs(){

        $db = mysqli2::connect();

        $sql = "select \n

type_discount,
is_delete,
aa_tur.id,
aa_teplohod.id as id_teplohod,
aa_teplohod.name as teplohod,
city1,
days,
date_format(date_start, '%d.%m.%y') as date_start2,
date_format(date_stop, '%d.%m.%y') as date_stop2,
aa_tur.name \n

 from aa_tur, aa_teplohod where aa_tur.id_teplohod = aa_teplohod.id order by date_start  ";

        $res = $db->query($sql);
//var_dump($sql);

//        $tur15 =array();
//        $sql15 ="SELECT count(*) as cc , id_tur FROM aa_discount15 group by id_tur";
//        $res15 = $db->query($sql15);
//        while ($r15 = $res15->fetch_assoc()){
//            $tur15[$r15['id_tur']]  = $r15['cc'];
//        }




        $turs = array();
        while ($r = $res->fetch_assoc()){

            $r['type_off']='';
            $r['type_happy']='';
            $r['type_special']='';

            $r['is_happy']=0;
            $r['is_special']=0;

            if ($r['type_discount']=='off') $r['type_off'] = ' checked ';
            else if ($r['type_discount']=='happy') {
                $r['type_happy'] = ' checked ';
                $r['is_happy'] = 1;
            }
            else if ($r['type_discount']=='special') {
                $r['type_special'] = ' checked ';
                $r['is_special'] = 1;
            }


            $r['is_delete'] = $r['is_delete'] * 1;

            $turs[]  = $r;
        }



        return $turs;

    }
}