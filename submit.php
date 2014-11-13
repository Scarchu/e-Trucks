<?php 
include_once "class.php";
require (HEADERF);

$pr   = $_GET["pr"];
$mode = $_GET["mode"];
$prl  = "truck".$pr."_lt";
$prc  = "truck".$pr."_cg";

if ($mode == "fuel") //Zapis na Goriwoto
{
	$ui = $_GET["ui"];
	$trip=$_POST["trip"];
	$liters=$_POST["liters"];
	$literst = varset($_POST["literst"], 0);
	$adblue  = varset($_POST["adblue"], 0);
	$date=$_POST["date1"];
	$full=$_POST["full"];
	if(isset($_POST["cash"]))
	$cash=$_POST["cash"];
	$sql -> db_Insert("$prl", "'', '$date', '$liters', '$literst', '$adblue', '$trip', '$cash', '$full', '$ui'");
}

elseif ($mode == "cargo") //Zapis na Cargoto
{
	$ui = $_GET["ui"];
	$trip=$_POST["tripc"];
	$road=$_POST["road"];
	$date=$_POST["date2"];
	if(isset($_POST["empty"]))
	$empty=$_POST["empty"];
	$sql -> db_Insert("$prc", "'', '$date', '$road', '$trip', '$empty', '$ui'");
}

if (isset($_POST['fuel_update'])) //Update na Goriwoto
{
	$id=$_POST["id"];
	$pr=$_POST["pr"];
	$prl=$_POST["prl"];
	$trip=$_POST["trip"];
	$liters=$_POST["liters"];
	$literst=$_POST["literst"];
	$adblue=$_POST["adblue"];
	$date=$_POST["date"];
	$cash=$_POST["cash"];
	$full=$_POST["full"];
	$ui=$_POST["ui"];
	$sql -> db_Update("$prl", "trip='$trip',liters='$liters',literst='$literst',adblue='$adblue',date='$date',cash='$cash',full='$full',userid='$ui' WHERE id='$id'");
	header("location: index.php?truck=$pr");
}

if (isset($_POST['cargo_update'])) //Update na Cargoto
{
	$id=$_POST["id"];
	$pr=$_POST["pr"];
	$prc=$_POST["prc"];
	$trip=$_POST["trip"];
	$road=$_POST["road"];
	$date=$_POST["date"];
	$empty=$_POST["empty"];
	$ui=$_POST["ui"];
	$sql -> db_Update("$prc", "trip='$trip',road='$road',date='$date',empty='$empty',userid='$ui' WHERE id='$id'");
	header("location: index.php?truck=$pr");
}

if($mode=="updatef") //Forma za update-ta na Goriwoto
{
	$id=$_GET["id"];
	$sql -> db_Select("$prl", "*", "id='$id'");
	while($row = $sql -> db_Fetch())
	{
		$id=$row['id'];
		$trip=$row["trip"];
		$liters=$row["liters"];
		$literst=$row["literst"];
		$adblue=$row["adblue"];
		$date=$row["date"];
		$cash=$row["cash"];
		$full=$row["full"];
		$ui=$row["userid"];
	}
?>
	
<script>
	$(function() {
		$( "#date" ).datepicker({ dateFormat: "dd.M.yy", firstDay: 1, monthNames: [ "Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември" ] });
	});
</script>

	<div class="center">
		<h1>Промяна на запис</h1>
		<u>направен от: <b><?php echo who($ui); ?></b></u>
		<br />
		<form name="form_fuel" method="POST" action="<?php echo e_SELF; ?>">
			<table align = "center" width="70%"  border="0" cellpadding="5" cellspacing="0" class="border">
				<tr>
					<td class="border2"><b>Километраж</b></td>
					<td class="border2"><b>Гориво Камион</b></td>
					<td class="border2"><b>Гориво Фриго</b></td>
					<td class="border2"><b>AdBlue</b></td>
					<td class="border2"><b>Дата</b></td>
					<td class="border2"><b>Кеш?</b></td>
					<td class="border2"><b>Пълни резервоари?</b></td>
				</tr>
				<tr>
					<td class="border2"><input type="number" value="<?php echo $trip; ?>" name="trip" /></td>
					<td class="border2"><input type="number" value="<?php echo $liters; ?>" name="liters" /></td>
					<td class="border2"><input type="number" value="<?php echo $literst; ?>" name="literst" /></td>
					<td class="border2"><input type="number" value="<?php echo $adblue; ?>" name="adblue" /></td>
					<td class="border2"><input type="text" value="<?php echo $date; ?>" name="date" id="date" /></td>
					<td class="border2"><input type="checkbox" value="checked" <?php if($cash == "checked") echo "checked"; ?> name="cash" id="check1" />Да</td>
					<td class="border2"><input type="checkbox" value="checked" <?php if($full == "checked") echo "checked"; ?> name="full" id="check2" />Да</td>
				</tr>
			</table>
			<input type="button" value="Назад" onClick="history.go(-1);return true;"></td>
			<input type="submit" name="fuel_update" value="Промени"></td>
			<input type="hidden" value="<?php echo $id; ?>" name="id" />
			<input type="hidden" value="<?php echo $pr ?>" name="pr" />
			<input type="hidden" value="<?php echo $prl ?>" name="prl" />
			<input type="hidden" value="<?php echo $ui ?>" name="ui" />
		</form>
	</div>
	<br />
<?php
}

elseif($mode=="updatec") //Forma za update-ta na Cargoto
{
	$id=$_GET["id"];
	$sql -> db_Select("$prc", "*", "id='$id'");
	while($row = $sql -> db_Fetch())
	{
		$id=$row['id'];
		$trip=$row["trip"];
		$road=$row["road"];
		$date=$row["date"];
		$empty=$row["empty"];
		$ui=$row["userid"];
	}
	?>
	
	<script>
		$(function() {
			$( "#date" ).datepicker({ dateFormat: "dd.M.yy", firstDay: 1, monthNames: [ "Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември" ] });
		});
	</script>
	<div class="center">
		<h1>Промяна на запис</h1>
		<u>направен от: <b><?php echo who($ui); ?></b></u>
		<br />
		<form name="form" method="POST" action="<?php echo e_SELF; ?>">
			<table align="center" width="60%"  border="0" cellpadding="4" cellspacing="0" class="border">
				<tr class="text">
					<td class="border2"><b>Километраж</b></td>
					<td class="border2"><b>Маршрут</b></td>
					<td class="border2"><b>Дата</b></td>
					<td class="border2"><b>Празен?</b></td>
				</tr>
				<tr>
				    <td class="border2"><input type="number" value="<?php echo $trip; ?>" name="trip" /></td>
					<td class="border2"><input type="text" value="<?php echo $road; ?>" name="road" /></td>
					<td class="border2"><input type="text" value="<?php echo $date; ?>" name="date" id="date" /></td>
					<td class="border2"><input type="checkbox" value="checked" <?php if($empty=="checked") echo "checked"; ?> name="empty" id="check1" /></td>
					<input type="hidden" value="<?php echo $id; ?>" name="id" />
					<input type="hidden" value="<?php echo $pr ?>" name="pr" />
					<input type="hidden" value="<?php echo $prc ?>" name="prc" />
					<input type="hidden" value="<?php echo $ui ?>" name="ui" />
				</tr>
			</table>
			<input type="button" value="Назад" onClick="history.go(-1);return true;">
			<input type="submit" name="cargo_update" value="Промени">
		</form>
	</div>
	<br />
<?php
}

require (FOOTERF);
?>