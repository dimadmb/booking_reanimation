<?php

class tools
{

	/**
	 * Сокращення запись number_format
	 */
	static public function nf($num)
	{
		return number_format($num, 0, '.', ' ');
	}


	static function createSelect($str, $nameselect, $selectitem = 0)
	{


		$db = mysqli2::connect();

		echo '<select id="' . $nameselect . '" name="' . $nameselect . '" >';


		$res = $db->query($str);
		//$col = mysqli_num_rows($res);

		while ($r = $res->fetch_assoc()) {


			$id = $r["id"];

			if ($id == $selectitem) {
				$s = 'selected="selected"';
			} else {
				$s = '';
			}
			$name = $r["name"];

			echo "<option " . $s . " value='" . $id . "' >$name</option>";
		}


		echo '</select>';
	}

}
