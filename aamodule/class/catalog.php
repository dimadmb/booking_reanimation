<?php

class catalog{

    /** Список продолжительности */
    static public function getListDays(){
        $sql = "Select distinct days from aa_tur order by days";
        return mysqli2::sql2array($sql);
    }

    /** Список теплоходов */
    static public function getListTeplohod(){
        $sql = "Select id, name from aa_teplohod order by name";
        return mysqli2::sql2array($sql);
    }

    /** список городов отправления */

    static public function getListCityOut(){
        $sql = "Select id, name from aa_city order by name";
        return mysqli2::sql2array($sql);
    }
}