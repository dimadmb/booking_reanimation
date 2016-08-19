<?php

class Propis  
{ 
    private static $def = array ( 
        'forms' => array(2, 0, 1, 1, 1, 2, 2, 2, 2, 2), 
         
        'words' => array( 
            0 => array('секунда', 'секунды', 'секунд'), 
            1 => array('минута', 'минуты', 'минут'), 
            2 => array('час', 'часа', 'часов'), 
            3 => array('день', 'дня', 'дней'), 
            4 => array('месяц', 'месяца', 'месяцев'), 
            5 => array('год', 'года', 'лет'), 
            'r' => array('рубль', 'рубля', 'рублей'), 
            'k' => array('копейка', 'копейки', 'копеек'), 
        ), 
         
        'rank' => array( 
            0 => array('штука', 'штуки', 'штук'), 
            1 => array('тысяча', 'тысячи', 'тысяч'), 
            2 => array('миллион', 'миллиона', 'миллионов'), 
            3 => array('миллиард', 'миллиарда', 'миллиардов'), 
        ), 
     
        'nums' => array( 
            '0' => array( '', 'десять', '', ''), 
            '1' => array( 'один', 'одиннадцать', '', 'сто'), 
            '2' => array( 'два', 'двенадцать', 'двадцать', 'двести'), 
            '1f' => array( 'одна', '', '', ''), 
            '2f' => array( 'две', '', '', ''), 
            '3' => array( 'три', 'тринадцать', 'тридцать', 'триста'), 
            '4' => array( 'четыре', 'четырнадцать', 'сорок', 'четыреста'), 
            '5' => array( 'пять', 'пятнадцать', 'пятьдесят', 'пятьсот'), 
            '6' => array( 'шесть', 'шестнадцать', 'шестьдесят', 'шестьсот'), 
            '7' => array( 'семь', 'семнадцать', 'семьдесят', 'семьсот'), 
            '8' => array( 'восемь', 'восемнадцать', 'восемьдесят', 'восемьсот'), 
            '9' => array( 'девять', 'девятнадцать', 'девяносто', 'девятьсот') 
        ) 
    ); 
     
    public static function get($str)  
    { 
        $def =& self::$def; 
        $res = array(); 
        $group = preg_split("/\D+/", $str); 
         
        if (count($group) < 3) //деньги 
        { 
            $res[] = self::get_one($group[0], $def['words']['r']); 
            $def['rank'][0] = $def['words']['k']; 
            $res[] = self::num2word($group[1]); 
        } 
        else //время - дата 
        { 
            $group = array_reverse($group);     
            foreach ($group as $key => $value)  
            { 
                if (trim($value))  
                { 
                    $def['rank'][0] = $def['words'][$key]; 
                    $res[] = self::num2word($value, 0); 
                } 
            } 
            $res = array_reverse($res); 
        } 
        return implode(' ', $res); 
    } 
     
    public static function get_one($num, $words = array())  
    { 
        if (count($words)) self::$def['rank'][0] = $words; 
        $str = number_format($num, 0, '', ','); 
         
        $r = explode(',', $str); 
        $r = array_reverse($r); 
        $word = array(); 
         
        foreach($r as $key => $value)  
        { 
            $word[] = self::num2word($value, $key); 
        } 
         
        $word = array_reverse($word); 
        return implode(' ', $word); 
    } 
     
    private static function num2word($str, $key = null)  
    { 
        if (intval(trim($str)) === 0 && $key !== 0) return ''; 
        $def = self::$def; 
        $nums = $def['nums']; 
        $forms = $def['forms']; 
        $rank = $def['rank']; 

        $dig = str_split(sprintf('%02d', $str)); 
        $dig = array_reverse($dig); 
        //если нет ключа, вернуть цифры с правильной plural-формой (можно использовать для копеек) 
        if (!isset($key)) return $str . ' ' . ((1 == $dig[1]) ? $rank[0][2] : $rank[0][$forms[$dig[0]]]); 
        //если кончился запас "миллионов", вернуть цифры 
        if (!isset($rank[$key])) return $str; 
        $rank = $rank[$key]; 
         
        $f = (preg_match("/[ая]$/", $rank[0]) && ($dig[0] == 1 || $dig[0] == 2)) ? 'f' : ''; 
         
        if (1 == $dig[1])  
        { 
            $num_word = $nums[$dig[0]][1]; 
            $word = $rank[2]; 
        } 
        else  
        { 
            $num_word = $nums[$dig[1]][2] . ' ' . $nums[$dig[0] . $f][0]; 
            $word = $rank[$forms[$dig[0]]]; 
        } 
         
        $sotni = (isset($dig[2])) ? $nums[$dig[2]][3] . ' ' : ''; 
        if (!trim($num_word)) $num_word = '0'; //это для секунд (рубли сюда не доходят) 
        return $sotni . $num_word . ' ' . $word; 
    } 
     
} 

//Бонус: "нативный" time_diff() 

function n_time_diff ($d1, $d2 = null) 
{ 
    if (empty($d2)) $d2 = date('Y-m-d H:i:s'); 
    $time2 = strtotime($d2); 
    $time = (strtotime($d1) - $time2); 
     
    if ($time < 0) return '00:00:00'; 
    $Y = gmdate('Y', $time) - gmdate('Y', 0); 
    $m = gmdate('m', $time) - gmdate('m', 0); 
    $d = gmdate('d', $time) - gmdate('d', 0); 
    $h = gmdate('H:i:s', $time); 
     
    $curr_m = date('m', $time2) - 1; 
    $delta_m = array(1, -2, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1, 1, -2, 1, 0); 
    $days_0 = array_slice($delta_m, 0, $m); 
    $days_real = array_slice($delta_m, $curr_m, $m); 
    $days_corr = array_sum($days_real) - array_sum($days_0); 
     
    $d -= $days_corr; 
    if ($d < 0) { 
        $d = 30 - $delta_m[$curr_m] + $d; 
        $m -= 1; 
    } 
    if ($m < 0) { 
        $m = 11; 
        $Y -= 1; 
    } 
     
    return "$Y-$m-$d $h"; 
}

?>