<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/configuration.php";



class mysqli2{

    static public function connect(){

        $JConfig = new JConfig();



        $db_host= $JConfig->host;
        $db_user = $JConfig->user;
        $db_pw = $JConfig->password;
        $db_name = $JConfig->db;

        $mysqli = mysqli_connect($db_host,$db_user,$db_pw,$db_name) or die(mysqli_error());



        $mysqli->query('SET NAMES utf8');
        $mysqli->query('SET CHARACTER SET utf8');
        $mysqli->query('SET COLLATION_CONNECTION="utf8_general_ci"');

        return $mysqli;
    }


    /** преобразование sql запроса в массив */
    static public function sql2array($sql){

        $dbi = self::connect();

        $out = array();
        $res = $dbi->query($sql);

        if ($res->num_rows == 0){

            $out = false;

//        } else if ($res->num_rows == 1){
//            $r = $res->fetch_assoc();
//            $out = $r;
        } else {

            while ($r = $res->fetch_assoc()) {
                $out[] = $r;
            }
        }

        return $out;

    }

    /** очистка таблицы */

    static public function truncateTable($tableName){

        $db = self::connect();
        $sql = "truncate table $tableName";
        $db->query($sql);


    }
}