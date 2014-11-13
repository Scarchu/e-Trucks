<?php
header('Content-Type: text/html; charset=utf-8');		//za mahane sled integrirane w osnowniat sait
$drivers_name = "Росен Ричард Бъчваров";
$company = "'ГАРАНТАУТО' ООД";
$truck_numb = "CA1592TK";

if(isset($_POST['submit']))
{
	$date 		= $_POST['date'];
	$begin_day 	= $_POST['begin_day'];
	$end_day 	= $_POST['end_day'];
	$trip		= $_POST['trip'];
	$drive_off 	= $_POST['drive_off'];
	$drive		= $_POST['drive'];
}
else
{
	$date 		= array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'');
	$begin_day 	= array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'');
	$end_day 	= array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'');
	$trip 		= array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'');
	$drive_off 	= array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'');
	$drive 		= array(0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 18=>'', 19=>'', 20=>'');
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
			for($i=0; $i<=19; $i++)
			{
			?>
			<tr>
				<td><input type="text" name="date[]" placeholder="дата" size="8" value="<?php echo $date[$i]; ?>" /></td>
				<td><input type="text" name="truck_numb[]" placeholder="номер" size="8" value="<?php echo $truck_numb; ?>" /></td>
				<td><input type="text" name="begin_day[]" placeholder="час" size="5" value="<?php echo $begin_day[$i]; ?>" /></td>
				<td><input type="text" name="end_day[]" placeholder="час" size="5" value="<?php echo $end_day[$i]; ?>" /></td>
				<td><input type="text" name="trip[]" placeholder="маршрут" size="55" value="<?php echo $trip[$i]; ?>" /></td>
				<td><input type="text" name="drive_off[]" placeholder="час" size="5" value="<?php echo $drive_off[$i]; ?>" /></td>
				<td><input type="text" name="drive[]" placeholder="час" size="5" value="<?php echo $drive[$i]; ?>" /></td>
				<td><p></p></td>
				<td><p></p></td>
				<td>rest</td>
			</tr>
			<?php } ?>
		</table>
		<input type="submit" name="submit" value="Изчисли" />
		<form>
	</div>
</div>
<a href="javascript:window.print()">Принт</a>