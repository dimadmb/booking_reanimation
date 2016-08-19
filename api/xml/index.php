<?php
header("Content-Type: text/html;charset=utf-8");

require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/helper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/aamodule/class/mysqli2.php";


$db = mysqli2::connect();



?>
<h1>Экспорт данных в xml</h1>

<h2>Круизы</h2>

<a href="/xml/cruises" target="_blank">Список круизов в формате xml</a>

<h2>Каюты</h2>

<p>Для получения списка кают в круизе, необходимо передать GET параметр <b>cruise</b>, равный id круиза</p>


<p>В каютах возможно 1-,2-,3-,4- местное размещение в зависимости от класса каюты. Цена зависит от размещения в каюте</p>


<p>Пример http://<?=$_SERVER['HTTP_HOST']?>/xml/kauta?cruise=3339</p>

<a href="/xml/kauta?cruise=3339" target="_blank">Список кают в круизе в формате xml</a>