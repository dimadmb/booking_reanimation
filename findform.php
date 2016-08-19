<?php

$db = mysqli2::connect();
// мин и макс дата

$sql = "select date_format(min(date_start),'%d.%m.%Y') as d1, date_format(max(date_start), '%d.%m.%Y') as d2 from aa_tur";
$res = $db->query($sql);

$r = $res->fetch_assoc();
$d1 = $r['d1'];
$d2 = $r['d2'];

$sql = "select date_format(max(dd),'%d.%m.%Y') as d1 \n
from ( select str_to_date('$d1','%d.%m.%Y') as dd union select now() as dd) as t2";

$res = $db->query($sql);
$r = $res->fetch_assoc();
$d1 = $r['d1'];



$listDays = catalog::getListDays();
$listTeplohod = catalog::getListTeplohod();
$listCityOut = catalog::getListCityOut();

?>
<div id="formFind">

    <form class="uk-form">
        Теплоход
        <select id="ship">
            <option value="0">Любой теплоход</option>
            <?php foreach ($listTeplohod as $item){  ?>
                <option value="<?=$item['id']?>"><?=$item['name']?></option>
            <?php } ?>
        </select>

        Город отправления

        <select id="city">
            <option value="0">Любой город</option>
            <?php foreach ($listCityOut as $item){  ?>
                <option value="<?=$item['id']?>"><?=$item['name']?></option>
            <?php } ?>
        </select>

        <div style="height: 15px;"></div>


        Дней, от
        <select id="lasting1">
            <?php foreach ($listDays as $item){ $days = $item['days'];  ?>
                <option value="<?=$days?>"><?=$days?></option>
            <?php } ?>
        </select>

        до

        <select id="lasting2">
            <?php foreach ($listDays as $item){ $days = $item['days'];

                $select = '';
                if ($listDays[count($listDays)-1]['days'] == $days )  $select = ' selected ';

                ?>
                <option <?=$select?> value="<?=$days?>"><?=$days?></option>
            <?php } ?>
        </select>


        Отправление,

        с <input id="d1" type="text" style="width: 90px;" data-uk-datepicker="{format:'DD.MM.YYYY', i18n: i18n }" value="<?=$d1?>" >
        по <input id="d2" type="text" style="width: 90px;" data-uk-datepicker="{format:'DD.MM.YYYY', i18n: i18n }" value="<?=$d2?>" >



        <div class="divCheck">
            <label>
                <input type="checkbox" id="checkHappy" class="filterCheck"> Счастливые круизы
            </label>

            <label >
                <input type="checkbox" id="checkSpecial" class="filterCheck"> Специальный тариф
            </label>
        </div>



        <div style="margin-top: 10px; color:#999;">
            Выбрано круизов: <span id="amountTur">...</span>

            <span id="resetFilter">
                 Сбросить фильтр
            </span>
        </div>
    </form>
</div>