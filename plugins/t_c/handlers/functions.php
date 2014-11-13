<?php

function current_hedyr()
{
	$text ='
		<div id="tc_header">
			<div id="truck_sel">
				<select class="">
					<option value="0">CA1592TK</option>
					<option value="1"></option>
				</select> 
			</div>
			<div id="truck_menu">
				<button class="button" type="submit" method="get" formaction="trucks_control.php?mode=otchet">Отчет</button><br />
				<button class="button">Справки</button><br />
				<button class="button">Редакция</button>
			</div>
			<div id="truck_number"><h1>СА1592ТК</h1></div>
		</div>';

	return $text;
}

function incomings_table($trip, $fuel, $taxes)
{
	$text ='
		<div id="above_tables">ПРИХОДИ</div>
		<table border="1" style="text-align:center; margin: 0 auto; width:80%;">
			<tr>
				<td width="20%">'.$trip.'</td>
				<td>от километри</td>
			</tr>
			<tr>
				<td>- '.$fuel.'</td>
				<td>от гориво</td>
			</tr>
			<tr>
				<td>- '.$taxes.'</td>
				<td>комисионни</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align: center;">'.($trip - $fuel - $taxes).' печалба</td>
			</tr>
		</table>
		<br />
	';
	return $text;
}

function outcomings_table($masiv_value, $masiv_desc)
{
	$rezultata = '';
	$text ='
		<div id="above_tables">РАЗХОДИ</div>
		<table border="1" style="text-align:center; margin: 0 auto; width:80%;">
		';
		for($i=0; $i<count($masiv_value); $i++)
		{
			$text .='
			<tr>
				<td width="20%">'.$masiv_value[$i].'</td>
				<td>'.$masiv_desc[$i].'</td>
			</tr> ';
			$rezultata += $masiv_value[$i];
		}
		
			$text .='<tr>
		
				<td colspan="2" style="text-align: center;">'.$rezultata.' разходи</td>
			</tr>
		</table>
		<br />
	';
	return $text;
}

function other_incomings_table($masiv)
{
	$rezultata = '';
	$text ='
		<div id="above_tables">ДРУГИ ПРИХОДИ</div>
		<table border="1" style="text-align:center; margin: 0 auto; width:80%;">
			';
		foreach($masiv as $key => $value)
		{
			$text .='
			<tr>
				<td width="20%">'.$value.'</td>
				<td>'.$key.'</td>
			</tr> ';
			$rezultata += $value;
		}
		
			$text .='<tr>
		
				<td colspan="2" style="text-align: center;">'.$rezultata.' Приходи</td>
			</tr>
		</table>
		<br />
	';
	return $text;
}

function pliachka_table($pliachkata)
{
	$text ='
		<div id="above_tables">ПЕЧАЛБА</div>
		<table border="1" style="text-align:center; margin: 0 auto; width:80%;">
			<tr>
				<td width="20%">'.$pliachkata.'</td>
				<td>чиста печалба</td>
			</tr>
			<tr>
				<td colspan="2">/2</td>
			</tr>
			<tr>
				<td colspan="2">По: '.(round($pliachkata/2)).' печалба</td>
			</tr>
		</table>
	';
	return $text;
}

function predishen_mesec_table($id)
{
	$half_saldo = (prev_month_saldo($id) / 2);
	$text ='
		<div id="above_tables">ПРЕДИШЕН МЕСЕЦ</div>
		<table border="1" style="text-align:center; margin: 0 auto; width:80%;">
			<tr>
				<td>Гарантауто</td>
				<td>'.$half_saldo.'</td>
			</tr>
			<tr>
				<td>Боби</td>
				<td>'.$half_saldo.'</td>
			</tr>
			<tr>
				<td>Салдо:</td>
				<td>'.prev_month_saldo($id).'</td>
			</tr>			
		</table>
	';
	return $text;
}

function tekusht_mesec_table($dobavyk, $id)
{
	$dobavyk = round($dobavyk / 2);
	$text ='
		<div id="above_tables">ТЕКУЩ МЕСЕЦ</div>
		<table border="1" style="text-align:center; margin: 0 auto; width:80%;">
			<tr>
				<td>Гарантауто</td>
				<td>'.(prev_month_saldo($id)/2+$dobavyk).'</td>
				<td>'.$dobavyk.'</td>
			</tr>
			<tr>
				<td>Боби</td>
				<td>'.(prev_month_saldo($id)/2+$dobavyk).'</td>
				<td>'.$dobavyk.'</td>
			</tr>
			<tr>
				<td>Салдо:</td>
				<td colspan="2">'.((prev_month_saldo($id)/2+$dobavyk)*2).'</td>
			</tr>			
		</table>
	';
	return $text;
}

function prev_month_saldo($id)
{
	global $sql, $pref;
	if(($id - 1) == 0)
	{
		$result['saldo'] = $pref['initial_saldo'];
	}
	else
	{
		$sql -> db_Select("saldo", "*", "WHERE id=".($id-1)."", "no-where");
		$result = $sql -> db_Fetch();
	}
	return $result['saldo'];
}