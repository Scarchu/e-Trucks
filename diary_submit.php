<?php
header('Content-Type: text/html; charset=utf-8');		//za mahane sled integrirane w osnowniat sait
//$drivers_name = "Росен Ричард Бъчваров";
//$company = "'ГАРАНТАУТО' ООД";
$truck_numb = "CA1592TK";
$how_many_days = 30;

$drivers_name = isset($_POST['drivers_name']) ? $_POST['drivers_name'] : '';
$company = isset($_POST['company']) ? $_POST['company'] : '';


if(!isset($_POST['stage']))
{
	echo '<form method="post" action="#">
			<table border="1">
				<tr>
					<th>Име на фирмата</th>
					<th>Име на водача</th>
				</tr>
				<tr>
					<td><input name="company" /></td>
					<td><input name="drivers_name" /></td>
				</tr>
			</table>
			<input type="hidden" name="stage" value="1" />
			<input type="submit" />
		</form>
	';
}
elseif($_POST['stage'] == 1)
{
	
/*********************************************************/

if(isset($_POST['submit']))
{
	$date		 = $_POST['date'];
	$begin_day	 = $_POST['begin_day'];
	$end_day	 = $_POST['end_day'];
	$trip		= $_POST['trip'];
	$drive_off	 = $_POST['drive_off'];
	$drive		= $_POST['drive'];
}
else
{
	$date		 = array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'', 21=>'', 22=>'', 23=>'', 24=>'', 25=>'', 26=>'', 27=>'', 28=>'', 29=>'', 30=>'', 31=>'');
	$begin_day	 = array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'', 21=>'', 22=>'', 23=>'', 24=>'', 25=>'', 26=>'', 27=>'', 28=>'', 29=>'', 30=>'', 31=>'');
	$end_day	 = array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'', 21=>'', 22=>'', 23=>'', 24=>'', 25=>'', 26=>'', 27=>'', 28=>'', 29=>'', 30=>'', 31=>'');
	$trip		 = array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'', 21=>'', 22=>'', 23=>'', 24=>'', 25=>'', 26=>'', 27=>'', 28=>'', 29=>'', 30=>'', 31=>'');
	$drive_off	 = array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'', 21=>'', 22=>'', 23=>'', 24=>'', 25=>'', 26=>'', 27=>'', 28=>'', 29=>'', 30=>'', 31=>'');
	$drive		 = array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'', 21=>'', 22=>'', 23=>'', 24=>'', 25=>'', 26=>'', 27=>'', 28=>'', 29=>'', 30=>'', 31=>'');
}

for($i=0; $i<=$how_many_days; $i++)		//izchisliawane i zarejdane na promenliwite
{
	$test_time1 = (strtotime($end_day[$i]) - strtotime($begin_day[$i]));
	$rezultat_pvu[$i] = $test_time1 - chasovi_interval_kym_sekundi($drive[$i]) - chasovi_interval_kym_sekundi($drive_off[$i]);
	
	if(isset($begin_day[$i+1]) && $begin_day[$i+1] == 0)
	{
		$resultat_mdp[$i] = '';
	}
	else
	{
		$resultat_mdp[$i] = mejdudnevna_pochiwka($end_day[$i], @$begin_day[$i+1], $i);
	}
	
	if(empty($begin_day[$i]) && empty($end_day[$i]))
	{
		$rest[$i] = "Почивка";
	}
	else
	{
		$rest[$i] = '';
	}
}


?>

<div id="output_container">
	<div id="output_header">
		<p style="text-align: center;"><b>РАБОТЕН ДНЕВНИК</b><br />на <?php echo $drivers_name; ?>, транспортен работник във фирма <?php echo $company; ?></p>
	</div>
	<div id="output_table">
		<form method="post" action="#">
		<table border="1" cellpadding="2" style="text-align: center; margin: 0 auto; width:98%;">
			<tr>
				<th width="6%">Дата</th>
				<th width="6%">Рег. N на МПС</th>
				<th width="5%">Начало на дневн. раб. време</th>
				<th width="5%">Край на дневн. раб. време</th>
				<th width="50%">Маршрут на движение</th>
				<th width="1%">Работно време извън времето за управление</th>
				<th width="1%">Време на управление</th>
				<th width="1%">Прекъсване времето на управление /почивки/</th>
				<th width="1%">Междудневна почивка</th>
				<th width="4%">Забележки</th>
			</tr>
			<?php
			for($i=0; $i<=$how_many_days; $i++)		//vizualizirane na promenliwite
			{
			?>
			<tr>
				<td><input type="text" name="date[]" placeholder="дата" size="8" value="<?php echo $date[$i]; ?>" /></td>
				<td><p><?php echo $truck_numb; ?></p></td>
				<td><input type="text" name="begin_day[]" placeholder="час" size="5" value="<?php echo $begin_day[$i]; ?>" /></td>
				<td><input type="text" name="end_day[]" placeholder="час" size="5" value="<?php echo $end_day[$i]; ?>" /></td>
				<td><input type="text" name="trip[]" placeholder="маршрут" size="55" value="<?php echo $trip[$i]; ?>" /></td>
				<td><input type="text" name="drive_off[]" placeholder="час" size="5" value="<?php echo $drive_off[$i]; ?>" /></td>
				<td><input type="text" name="drive[]" placeholder="час" size="5" value="<?php echo $drive[$i]; ?>" /></td>
				<td><p><?php echo date("H:i", ($rezultat_pvu[$i])); ?></p></td>
				<td><p><?php echo $resultat_mdp[$i]; ?></p></td>
				<td><p><?php echo $rest[$i]; ?></p></td>
			</tr>
			<?php } ?>
		</table>
		<input type="hidden" name="stage" value="1" />
		<input type="hidden" name="company" value="<?php echo $company; ?>" />
		<input type="hidden" name="drivers_name" value="<?php echo $drivers_name; ?>" />
		<input type="submit" name="submit" value="Изчисли" />
		<form>
	</div>
</div>
<a href="javascript:window.print()">Принт</a>

<?php
}
/***************************************************************************/
function chasovi_interval_kym_sekundi($chas)
{
	sscanf($chas, "%d.%d", $hours, $minutes);
	$time_seconds = (($hours * 60 + $minutes) * 60);
	return $time_seconds;
}

function mejdudnevna_pochiwka($end, $start, $i)
{
	$temp = !empty($end) ? date("H:i", (86400 - strtotime($end) + strtotime($start))) : '';
	//echo "i = ".$i." | temp = ".$temp."<br />";
	return $temp;
}

?>