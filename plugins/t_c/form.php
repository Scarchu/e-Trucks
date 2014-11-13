<?php
if (!defined('eTR_INIT')) { exit; }
?>

<script type="text/javascript">
var rowNum = 0;
function addRow(frm) {
	rowNum ++;
	var row = '<p id="rowNum'+rowNum+'"><input type="text" name="qty[]" size="4" value="'+frm.add_qty.value+'"> &#8364; <input type="text" name="name[]" value="'+frm.add_name.value+'"> Opisanie <input type="button" value="Remove" onclick="removeRow('+rowNum+');"></p>';
	jQuery('#itemRows').append(row);
	frm.add_qty.value = '';
	frm.add_name.value = '';
}

function removeRow(rnum) {
	jQuery('#rowNum'+rnum).remove();
}
</script>

<script type="text/javascript">
$(function() {
	$( "#date" ).datepicker({ dateFormat: "dd.M.yy", firstDay: 1, monthNames: [ "Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември" ] });
});
</script>

<?php

$sql -> db_Select("saldo", "*", "ORDER BY id DESC LIMIT 1", "no-where");
$result = $sql -> db_Fetch();

//$sql2 -> db_Select("saldo", "*", "WHERE id=".($result['id']-1)."", "no-where");
//$result_prev = $sql2 -> db_Fetch();

//if(isset($result['id'])) { $current_saldo = $pref['initial_saldo']; }	//Prowerka za pyrwi zapis
$current_saldo = isset($result['id']) ? $result['saldo'] : $pref['initial_saldo'];

if (isset($_POST['submit_val']))
{
	$timestamp = strtotime($_POST['date']);
	$from_trip = mysql_real_escape_string($_POST['kilometers']);
	$from_fuel = mysql_real_escape_string($_POST['fuel']);
	$from_commis = isset($_POST['commision']) ? mysql_real_escape_string($_POST['commision']) : 0;
	//$res_stage1 = ($from_trip - $from_fuel - $from_commis);
	$res_stage1 = array(0 => $from_trip, 1 => $from_fuel, 2 => $from_commis);
	// test purposes only!!! echo $res_stage1;
	if(isset($_POST['add_value_1']))
	{
		$additional_income_value_1 = mysql_real_escape_string($_POST['add_value_1']);
		$additional_income_desc_1 = mysql_real_escape_string($_POST['add_desc_1']);
		$additional_income_value_2 = isset($_POST['add_value_2']) ? mysql_real_escape_string($_POST['add_value_2']) : 0;
		$additional_income_desc_2 = isset($_POST['add_desc_2']) ? mysql_real_escape_string($_POST['add_desc_2']) : '';
	}
	else
	{
		$additional_income_value_1 = 0;
		$additional_income_desc_1 = '';
		$additional_income_value_2 = 0;
		$additional_income_desc_2 = '';
	}
	$additionals = array($additional_income_desc_1 => $additional_income_value_1, $additional_income_desc_2 => $additional_income_value_2);
	$additionals = serialize($additionals);
	$outcome = '';
	$qty = isset($_POST['qty']) ? $_POST['qty'] : 0;
	$name = isset($_POST['name']) ? $_POST['name'] : 0;
	foreach ( $qty as $key=>$value )
	{
		$values_money[] = mysql_real_escape_string($value);
		$outcome += mysql_real_escape_string($value);
	}
	foreach ( $name as $key=>$value )
	{
		$values_descr[] = mysql_real_escape_string($value);
	}
	
	$new_saldo = ($current_saldo + ($from_trip - $from_fuel - $from_commis + $additional_income_value_1 + $additional_income_value_2 - $outcome));
	
	if($sql -> db_Insert("saldo", "'', '".$timestamp."', '".serialize($res_stage1)."', '".$additionals."', '".serialize($values_money)."', '".serialize($values_descr)."', '1', '".$new_saldo."'"))
	{
		echo "bravo";
		//header("Location: trucks_control.php");
		die;
	}
	
}
$text ='
<form method="post" target="">
<table border="0" width="50%">
<tr>
	<td>
		<label class="label_j text_input" for="date">Дата</label></td><td><input class="input_j" type="text" name="date" id="date" value="" /></td>
	</td>
</tr>
<tr>
<td>
	<table class="border2">
		<tr>
			<th colspan="2">ПРИХОДИ</th>
		</tr>
		<tr>
			<td>От километри</td>
			<td><input type="text" name="kilometers"> &#8364;</td>
		</tr>
		<tr>
			<td>От гориво</td>
			<td><input type="text" name="fuel"> &#8364;</td>
		</tr>
		<tr>
			<td>Комисионна</td>
			<td><input type="text" name="commision"> &#8364;</td>
		</tr>
	</table>
	</td>
	<td>
		<table class="border2">
		<tr>
			<th colspan="2">ДРУГИ ПРИХОДИ</th>
		</tr>
		<tr>
			<th>сума</th>
			<th>описание</th>
		</tr>
		<tr>
			<td><input type="text" name="add_value_1" /> &#8364;</td>
			<td><input type="text" name="add_desc_1"></td>
		</tr>
		<tr>
			<td><input type="text" name="add_value_2" /> &#8364;</td>
			<td><input type="text" name="add_desc_2"></td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
	<br />
	<table class="border2">
		<tr>
			<th>РАЗХОДИ</th>
		</tr>
		<tr>
			<td>
				<div id="itemRows">
					<input type="text" name="add_qty" size="4"> &#8364; </input><input type="text" name="add_name"> Описание |</input><input onclick="addRow(this.form);" type="button" value="Добави" />|
				</div>
			</td>
		</tr>
	</table>
	<br />
	<input type="submit" name="submit_val" value="Запиши" />
</form>
';

$main_table = $text;