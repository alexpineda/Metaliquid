<?php
class MetaLiquid_Filters
{
	//returns int value representing minutes since 2002, TeamLiquid's founding
	//up until the post date extracted from the string
	function lastreplytime($str)
	{
		$months = array("Jan" => 0, "Feb" => 1, "Mar" => 2, "Apr" => 3, "May" => 4,
			"Jun" => 5, "Jul" => 6, "Aug" => 7, "Sep" => 8, "Oct" => 9, "Nov" => 10,
			"Dec" => 11);
		
		if (preg_match('/([0-9]{2}):([0-9]{2})\s([a-zA-Z]{3})\s([0-9]{2})\s([0-9]{4})/', $str, $matches)){
			
			list($null, $hour, $minute, $month, $day, $year) = $matches;
			$time = 0;
			
			//previous years
			for ($y = 2002; $y < $year; $y ++ ){
				for ($i = 0; $i < 12; $i++){
					$time += 1440 * cal_days_in_month(CAL_GREGORIAN, $i+1, $y);
				}
			}
			
			//entire year upto the month of
			$i = 0;
			do {
				$time += 1440 * cal_days_in_month(CAL_GREGORIAN, $i+1, $year);
				$i ++;
			} while ( $i <  $months[$month] );
			
			//all days upto the day of
			for ($i = 1; $i < $day; $i++){
				$time += 1440;
			}
			
			//all minutes of the day
			$time += $minute;
			$time += $hour * 60;
			
			return $time;
		}
	}
	
	function integer($str)
	{
		return (int)$str;
	}
}