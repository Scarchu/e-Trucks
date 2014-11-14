<?php

if (!defined('eTR_INIT')) { exit; }

if(ADMIN or ((USERLV == BOSS_LEVEL) and (count($user_trucks_array) > 1)))
{
	$text_h .='<form action="'.e_BASE.'index.php" method="GET">
				<select onchange="this.form.submit()" name="truck" style="box-shadow: 5px 5px 5px #888888;">
					<option value="0">Избери камион</option>
					';
					for ($i=1; $i<=$pref['broi_trucks']; $i++)
					{
						$k = ($i - 1);
						if((USERLV == ADMIN_LEVEL) or ($sql2 -> db_Select_gen("DESCRIBE ".MPREFIX."truck".$i."_cg") and ($pref['trucks_plate'][$k] != "") and (is_truck_off($k)) and array_key_exists($i, $user_trucks_array)))
						{
							$text_h .='<option value="'.$i.'">'.(isset($pref['trucks_plate'][$k]) ? $pref['trucks_plate'][$k] : "").'</option>';
						}
					}
				$text_h .='
				</select>
			</form>';
}
else
{
	
	$text_h .= '<a href="'.e_BASE.'?truck='.DRIVERTR.'">Отчет</a>';
}

function is_truck_off($count)
{
	global $pref;
	if(!empty($pref['trucks_off']))
	{
		if(!in_array($count, $pref['trucks_off']))
		{
			return false;
		}
	}
	else
	{
		return true;
	}
}
?>
