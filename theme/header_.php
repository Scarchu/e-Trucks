<?php
ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<link href="<?php echo e_THEME; ?>style.css" rel="stylesheet" type="text/css">
<link rel="icon" type="image/png" href="<?php echo e_THEME; ?>favicon.ico" />
<meta http-equiv="Content-Type" content="text/css;charset=UTF-8">
<title><!--TITLE--></title>
</head>
<body>

<?php
if(isset($_SESSION['user_id']))		//podsigurqwane za wqrnata smqna na header-a (da ne se smenia s USERID!!!)
{
	$sql -> db_Select_gen('select count(*) as nb_new_pm from #pm where ((user1="'.USERID.'" and user1read="no") or (user2="'.USERID.'" and user2read="no")) and id2="1"');
	$nb_new_pm = $sql -> db_Fetch();
	$nb_new_pm = $nb_new_pm['nb_new_pm'];
		
	
	$text ='
	<table class="header" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td rowspan="2"><p class="logo_hr"> Г А Р А Н Т А У Т О </p></td>
		<td  width="16%"><p class="red"><a href="'.e_BASE.'profile.php?uid='.USERID.'">Здравей  '.USERFN.'</a></p></td>
		<td class="center" width="16%"><p>Имате <a href="'.e_PLUGINS.'pm/list_pm.php">
		';
		if ($nb_new_pm == 1)
		{
			$new = " ново";
			$new_s = " съобщение";
		}
		else
		{
			$new = " нови";
			$new_s = " съобщения";
		}
		if ($nb_new_pm !=0)
		{
			$count_messages = ("<blink>".$nb_new_pm.$new."</blink>");
		}
		else
		{
			$count_messages = ($nb_new_pm.$new);
		}
		$text .='	
		'.$count_messages.'</a>'.$new_s.'.
		</td>
	</tr>
	<tr>
		<td>';
		if (USERLV == 0) { $text .='<a href="'.e_BASE.'logout.php"><img src="'.e_THEME.'Images/leave_32.png" title="изход" /></a>'; }
		$text .=' <a href="'.e_PLUGINS.'forum/"><img src="'.e_THEME.'Images/forum.png" title="форум" /></a>
				  <a href="'.e_BASE.'diary.php"><img src="'.e_THEME.'Images/diary.png" title="Работен Дневник" /></a>
		</td>
		<td class="center"><p><a href="'.e_PLUGINS.'pm/new_pm.php">Напиши ново ЛС.</p></a></td>
	</tr>
	</table>
	<hr>
	';
	if (USERLV > 0)
	{
	$text .='
	<table width="100%" border="0" cellpadding="4" cellspacing="0">
		<tr class="text">
			<td width="9.5%" align="left"><a href="'.e_BASE.'logout.php"><img src="'.e_THEME.'Images/leave_32.png" title="Изход" alt="Изход" style="box-shadow: 5px 5px 5px #888888;"></a></td>
			<td width="27%" align="center"><a href="'.e_BASE.'index.php"><img src="'.e_THEME.'Images/main_32.png" title="Начало" alt="Начало" style="box-shadow: 5px 5px 5px #888888;"></a></td>
			<td width="27%" align="center">
			<form action="'.e_BASE.'index.php" method="GET">
				<select onchange="this.form.submit()" name="truck" style="box-shadow: 5px 5px 5px #888888;">
					<option value="0">Избери камион</option>
					';
					for ($i=1; $i<=$pref['broi_trucks']; $i++)
					{
						$k = ($i - 1);
						if((USERLV == ADMIN_LEVEL) or ($sql2 -> db_Select_gen("DESCRIBE ".MPREFIX."truck".$i."_cg") and ($pref['trucks_plate'][$k] != "") and (is_truck_off($k)) and array_key_exists($i, $user_trucks_array)))
						{
							$text .='<option value="'.$i.'">'.(isset($pref['trucks_plate'][$k]) ? $pref['trucks_plate'][$k] : "").'</option>';
						}
					}
				$text .='
				</select>
			</form>
			</td>
			<td width="9.5%" align="right"><a href="'.e_BASE.'advanced.php"> <img src="'.e_THEME.'Images/stats_32.png" title="Допълнителни" alt="Допълнителни" style="box-shadow: 5px 5px 5px #888888;"></a></td>
		</tr>
	</table> 
	<hr>';
	}
}
else
{
$text ='
<table class="header" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td rowspan="2"><p class="logo_hr"> Г А Р А Н Т А У Т О </p></td>
		<td  width="26%"><p class="red">Здравей - Страннико</p></td>
	</tr>
	<tr>
		<td width="16%"><blink><p class="red">Логни се!</p></blink></td>
	</tr>
</table>
<hr>	
';
}
echo $text;

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