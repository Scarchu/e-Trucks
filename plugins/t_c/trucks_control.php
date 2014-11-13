<?php
require_once("../../class.php");
require_once(HEADERF);

/***************************************************

kliuchowe za prihodite:
	0 -> pechalba ot kilometri;
	1 -> razhod za goriwo;
	2 -> razhod ot komisionni.
	
***************************************************/

echo '<link href="style.css" rel="stylesheet" type="text/css">';
include("languages/Bulgarian.php");
require_once "handlers/functions.php";

$sql -> db_Select("saldo", "*", "ORDER BY id DESC LIMIT 1", "no-where");
$result = $sql -> db_Fetch();

$period = bg_date("F, Y", $result['datestamp']);

$prihodi_arr = unserialize($result['income']);
$razhodi_value = unserialize($result['outcome_value']);
$razhodi_desc = unserialize($result['outcome_desc']);
$additional_incomes = unserialize($result['income_other']);

if(count($razhodi_value) !== count($razhodi_desc))
{
	echo "Грешка в базата данни!!!";
	die;
}

$razhodi_value = isset($razhodi_value) ? $razhodi_value : array(0=>0);

$prihodi_arr[0] = isset($prihodi_arr[0]) ? $prihodi_arr[0] : 0;
$prihodi_arr[1] = isset($prihodi_arr[1]) ? $prihodi_arr[1] : 0;
$prihodi_arr[2] = isset($prihodi_arr[2]) ? $prihodi_arr[2] : 0;

if($prihodi_arr[0] == 0 and $prihodi_arr[1] == 0 and $prihodi_arr[1] ==0)
{
	$pliachkata = getting_final_result($additional_incomes) - getting_final_result($razhodi_value);
}
else
{
	$pliachkata = ($prihodi_arr[0] - $prihodi_arr[1] - $prihodi_arr[2] - getting_final_result($razhodi_value) + getting_final_result($additional_incomes));
}
	
function getting_final_result($masiv)
{
	$result = '';
	foreach($masiv as $key => $value)
	{
		$result += $value;
	}
	return $result;
}

$header = current_hedyr();
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
if($mode == '')
{
$main_table ='
<div id="tc_container">
	<div style="width:90%; text-align:center;">'.$period.'</div><br />
	<table border="0" cellpadding="2" style="width:90%; text-align:center; margin: auto;">
		<tr>
			<td style="width:60%;">'.incomings_table($prihodi_arr[0],$prihodi_arr[1],$prihodi_arr[2]).'</td>
			<td rowspan="4" style="vertical-align:top;">
				'.predishen_mesec_table($result["id"]).'<br />'.tekusht_mesec_table($pliachkata, $result["id"]).'<br />
				<button onclick="window.location.href=\'trucks_control.php?mode=otchet\'">Въвеждане</button>
				<input type="button" value="Справки" disabled/>
				
				<br /><br />
				<p>Първоначално салдо: <b>'.$pref["initial_saldo"].'</b> <i>евро</i></p>
				<p>Текуща разлика в салдото: <b>'.($pref["initial_saldo"] - $result["saldo"]).'</b> <i>евро</i></p>
			</td>
		</tr>
		<tr>
			<td>'.outcomings_table($razhodi_value, $razhodi_desc).'</td>
		</tr>
		<tr>
			<td>'.other_incomings_table($additional_incomes).'</td>
		</tr>
		<tr>
			<td>'.pliachka_table($pliachkata).'</td>
		</tr>
		
	</table>
	<br />
</div>
';
}
elseif($mode == "otchet")
{
	require e_PLUGINS."t_c/form.php";
}
echo $main_table;
//$ns->tablerender("hedyr", $header);
//$ns->tablerender("laino", $main_table);

require_once(FOOTERF);
?>