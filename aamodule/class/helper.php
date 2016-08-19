<?php

/**
 * Class helper -просто вспомагательный класс с функциями помощниками
 *
 * пример использования
 *

require_once $_SERVER['DOCUMENT_ROOT'] . "/inc/service/_helper_.php";
helper :: var_dump_pre($hotels[0]['rooms']);
 */
class helper{




    static public function var_dump_pre($data, $do_var_dump = false){
        if (!self::isAdmin()) return;

        echo '<div style="background: #c9c1f4; padding: 5px; border: #999 2px solid; overflow-x:scroll;"><pre>';

        if (!isset($data)){echo '<p>переменной нет - выводить нечего</p>';}

        if (!$do_var_dump) {print_r($data);}
        else {var_dump($data);}

        echo '</pre></div>';
    }

    static public function isAdmin(){
        $mass=array(
            '::1',
            '46.102.244.61',
            '127.0.0.1',
			'62.117.111.84'
        );
        return in_array($_SERVER['REMOTE_ADDR'],$mass);
    }








}