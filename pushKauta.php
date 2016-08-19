<?php
header("Content-Type: text/html;charset=utf-8");

ini_set('display_errors', 1);
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 3000);

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/catalog.php";

//require_once $_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php";

$db = mysqli2::connect();


$url_key = "?pauth=".config::$keyVodohod;

$url_teplohod = "http://cruises.vodohod.com/agency/json-prices.htm";


mysqli2::truncateTable('aa_kauta');

$sql = "select id,id_teplohod from aa_tur";
$res = $db->query($sql);

while ($r = $res->fetch_assoc()){

    $id_tur = $r['id'];
    $id_teplohod = $r['id_teplohod'];

    $sql ="select id from aa_kauta where id_teplohod= $id_teplohod";
    $res_col = $db->query($sql);
    if ($res_col->num_rows>0) continue;

    $url = $url_teplohod . $url_key . "&cruise=$id_tur";

    $data = file_get_contents($url);
    $data = json_decode($data);


    $room_all = $data->room_all;

//    helper::var_dump_pre($room_all);
//    helper::var_dump_pre($data);

    foreach ($room_all as $id_price => $rooms){

            foreach ($rooms as $room){

                $sql = "insert into aa_kauta set id_teplohod = $id_teplohod, num = '$room' ";
                $db->query($sql);
            }
    }




}

exit;


// устаревший алгоритм из экселя

$filename = $_SERVER['DOCUMENT_ROOT'] . "/from_xls/kauta/kauta_v2.xlsx";


$objPHPExcel = PHPExcel_IOFactory::load($filename);
$objPHPExcel->setActiveSheetIndex(0);

$sheets = $objPHPExcel->getAllSheets();


foreach ($sheets as $sheet) {
    $xls_code = $sheet->getTitle();
    echo $xls_code . '<br>';

    $sql = "select * from aa_teplohod where xls_code = '$xls_code' limit 1 ";

    $res = $db->query($sql);

    if ($res->num_rows == 0) continue;

    $r = $res->fetch_assoc();

    $filename = $r['name'];
    $id_teplohod = $r['id'];


    $mass = $sheet->toArray(); // выгружаем данные из объекта в массив

    for ($i = 3; $i < count($mass); $i = $i + 2) {
        $id_class = $mass[$i][1];
        $deck = $mass[$i][2];
        $number = $mass[$i + 1][0];
        $side = $mass[$i + 1][2];

        if ($side == 'Левый') { $side = 0;}
        else if ($side == 'Правый') { $side = 1;}

        $sql = "insert into aa_kauta set num = $number, id_teplohod=$id_teplohod, side=$side, deck ='$deck', id_class='$id_class' ";
        $db->query($sql);
        echo $db->error;
        echo '<br>';
    }
}