<?php

echo decimal_to_time(12.10);



function decimal_to_time($decimal)
{
    $hours = ceil($decimal / 60);
    $minutes = ceil($decimal * 60);
    $seconds = $decimal - (int)$decimal;
    $seconds = round($seconds * 60);
 
    return str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":" . str_pad($seconds, 2, "0", STR_PAD_LEFT);
	//return $hours+$minutes;
}


?>