<?


class Discount 
{
	public static $discounts = [9=>11,10=>10,11=>9,12=>8,1=>5,2=>5,3=>5];

	public static function getSesonPercent($year_now = null, $year_tur = null)
	{
		return self::$discounts[date('n')];
	}
	public static function getSesonKoef($year_now = null, $year_tur = null)
	{
		return (100 - self::$discounts[date('n')])/100;
	}
	
	
	
}