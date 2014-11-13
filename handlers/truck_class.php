<?php

if (!defined('eTR_INIT')) { exit; }

class Truck
{
	var $pr;
	var $truck_name;
	var $buttons_enable = TRUE;
	
	function db_select()
	{
		$this->fdb = "truck".$this->pr."_lt";
		$this->cdb = "truck".$this->pr."_cg";
	}
	
	function main()
	{
		$this->db_select();
		echo "<table class='truck_id' border='0' cellpadding='0'><tr><td><p>".$this->truck_name."</p></td></tr></table>";	
		if ($this->buttons_enable == "TRUE")
		truck_buttons($this->pr);
		echo '<table width="100%" border="0" cellpadding="2" cellspacing="0">';
			$this->left_index_colomn();
			$this->right_index_colomn();
		echo '</table>';
	}

	function left_index_colomn()
	{
		global $sql, $sql2, $pref;
		global $pr, $modules_array;
		$fdb = $this->fdb;
		if(($pref['modules']['frigo'][$this->pr] == 1) and ($pref['modules']['AdBlue'][$this->pr] == 0))
		{
			$frigo_onoff_header = "<table width='95%' border='1' cellpadding='3' cellspacing='0'><th width='10%'><b>Дата</b></th><th width='25%'><b>Километраж</b></th><th width='33%'><b>Литра Камион</b></th><th width='33%'><b>Литра Фриго</b></th>";
		}
		elseif(($pref['modules']['frigo'][$this->pr] == 0) and ($pref['modules']['AdBlue'][$this->pr] == 1))
		{
			$frigo_onoff_header = "<table width='95%' border='1' cellpadding='3' cellspacing='0'><th width='10%'><b>Дата</b></th><th width='25%'><b>Километраж</b></th><th width='33%'><b>Литра Камион</b></th><th width='33%'><b>Литра AdBlue</b></th>";
		}
		elseif(($pref['modules']['frigo'][$this->pr] == 1) and ($pref['modules']['AdBlue'][$this->pr] == 1))
		{
			$frigo_onoff_header = "<table width='95%' border='1' cellpadding='2' cellspacing='0'><th width='10%'><b>Дата</b></th><th width='25%'><b>Километраж</b></th><th width='21%'><b>Литра Камион</b></th><th width='21%'><b>Литра Фриго</b></th><th width='21%'><b>Литра AdBlue</b></th>";
		}
		else
		{
			$frigo_onoff_header = "<table width='95%' border='1' cellpadding='2' cellspacing='0'><th width='10%'><b>Дата</b></th><th width='40%'><b>Километраж</b></th><th width='40%'><b>Литра Камион</b></th>";
		}
		$num_rows = $sql -> db_Count("$fdb", "(*)", "");
		$pages = new Paginator;
		$pages->truck_number = $this->pr;
		$pages->items_total = $num_rows;  
		$pages->mid_range = 5;  
		$pages->paginate();
		echo '<td width="38%" align="center" valign="top"><h1>Гориво</h1>
				<div class="center">'.$pages->display_pages().'</div>';
		$sql2 -> db_Select("$fdb", "*", "ORDER BY id DESC $pages->limit", "no-where");
		echo $frigo_onoff_header;
		while($row = $sql2 -> db_Fetch())
		{
			$bckgr = ($row['cash'] == 'checked') ? $pref['color_cash'] : 'FFFFFF'; 	//ocwetiawane na cash
			$full = ($row['full'] == 'checked') ? 'FF0000' : '000000';   			//ocwetiawane na pylni rezerwoari
					
			if(($pref['modules']['frigo'][$this->pr] == 1) and ($pref['modules']['AdBlue'][$this->pr] == 0)) { $frigo_onoff_row = '<td class="border2">'.$row["literst"].'</td>'; }
			elseif(($pref['modules']['frigo'][$this->pr] == 0) and ($pref['modules']['AdBlue'][$this->pr] == 1)) { $frigo_onoff_row = '<td class="border2">'.$row["adblue"].'</td>'; }
			elseif(($pref['modules']['frigo'][$this->pr] == 1) and ($pref['modules']['AdBlue'][$this->pr] == 1)) { $frigo_onoff_row = '<td class="border2">'.$row["literst"].'</td><td class="border2">'.$row["adblue"].'</td>'; }
			else { $frigo_onoff_row = ''; }
			$link_fuel = ($_SESSION['user_level'] >= 4) ? '<a href = "submit.php?mode=updatef&pr='.$this->pr.'&id='.$row["id"].'">' : '';
		echo '<tr class="text tdh" bgcolor="'.$bckgr.'">
					<td class="border2" title="'.who($row["userid"]).'">'.$link_fuel.' '.$row["date"].'</a></td>
					<td class="border2" title="'.sreden_razhod($fdb, $row['id']).'">'.$row["trip"].'</td>
					<td class="border2" style="color:#'.$full.'">'.$row["liters"].'</td>
					'.$frigo_onoff_row.'
					</tr>
			';
		}
		echo '</table></td>';
	}



	function right_index_colomn()
	{
		global $sql, $sql2, $pref;
		$cdb = $this->cdb;
		echo '<td width="62%" valign="top"><h1 class="center">Товари</h1>';
		$num_rows = $sql -> db_Count("$cdb", "(*)", "");
		$pages = new Paginator;
		$pages->truck_number = $this->pr;
		$pages->items_total = $num_rows;  
		$pages->mid_range = 5;
		$pages->paginate();
		echo "<div class='center'>";
		echo $pages->display_pages();
		echo "</div>";
		$sql2 -> db_Select("$cdb", "*", "ORDER BY id DESC $pages->limit", "no-where");
		echo '<table width="95%" border="1" cellpadding="2" cellspacing="0">
				<th width="12%"><b>Дата</b></th>
				<th><b>Маршрут</b></th>
				<th width="10%"><b>Километраж</b></th>
				<th width="10%"><b>Пробег</b></th>
			';
		while($rowc = $sql2 -> db_Fetch())
		{
			$bckgr = ($rowc['empty'] == 'checked') ? $pref['color_empty'] : '#FFFFFF';	//Ocwetiawane prazen kurs
			$link_cargo = (USERLV >= 4) ? '<a href = "submit.php?mode=updatec&pr='.$this->pr.'&id='.$rowc["id"].'">' : '';
			$uid = $rowc['userid'];
			echo '
				<script>
				$(function() {
				$( document ).tooltip();
				});
				</script>				
			
			<tr class="text tdh" bgcolor="'.$bckgr.'">
			<td class="border2" title="'.who($rowc["userid"]).'">'.$link_cargo.' '.$rowc["date"].'</a></td>
			<td class="border2">'.$rowc["road"].'</td>
			<td class="border2">'.$rowc["trip"].'</td>
			<td class="border2">
			';
			$idm=($rowc['id']-1);
			$sql -> db_Select("$cdb", "trip", "id='$idm'");
			$razlikaf = $sql -> db_Fetch();
			$pb=($rowc['trip']-$razlikaf['trip']); 
			echo $pb; 
			echo '</td>			
					</tr>
			';
		}
		echo '</table>
				</td>
		';
	}
	
	function izminati_po_mesec()
	{
		global $year, $pref, $months, $short_months, $sql, $sql2;
		$sql3 = new db;
		$this->db_select();
		$cdb = $this->cdb;
		
		if($sql -> db_Count("$cdb", "(*)"))
		{
			for ($j=0; $j<=11; ++$j)
			{
				$k = $j - 1;
				$last_year = ($year - 1);	
				$searchc1 = $short_months[$j].'.'.$year;
				@$searchc2 = $short_months[$k].'.'.$year;
				$searchc3 = "Dec.".$last_year;
				$sql -> db_Select("$cdb", "MIN(trip) AS min, MAX(trip) AS max", "date LIKE '%$searchc1'");
				$sql2 -> db_Select("$cdb", "MAX(trip) AS max", "date LIKE '%$searchc2'");
				$sql3 -> db_Select("$cdb", "MAX(trip) AS max", "date LIKE '%$searchc3'");
				$text = '';
				while ($dq = $sql -> db_Fetch()) // min\max kilometers - current month
				{
					$dd = $dq['min'];
					$dc = $dq['max'];
				}
				while ($dz = $sql2 -> db_Fetch()) // max kilometers previous month
				{
					$dx = $dz['max'];
				}
				while ($dv = $sql3 -> db_Fetch()) // max kilometers december, previous year
				{
					$dn = $dv['max'];
				}
				if ($k !== -1)
				{
					$resultat = ((($dc - $dd) + ($dd - $dx)) < 0) ? 0 : (($dc - $dd) + ($dd - $dx));
				}
				else
				{
					$resultat = (($dc - $dd) + ($dd - $dn));
				}
				$result_array[] = $resultat;
		}
//========================================================================================================//
		if ($this->pr %5 == 0)
		{
			echo "<tr>";
		}
		$standby_adv_row[1] = ($pref['standby_onoff'] == 1) ? '<th>Стендбай</th>' : '';
		
		$text .='
			<td>
				<table border="0" width="99%" cellpadding="2" cellspacing="0" class="border2">
					<tr>
						<h1 style="font: 8pt/10pt fantasy, cursive, Serif; font-weight: bold">'.$pref['trucks_plate'][($this->pr -1)].'</h1>
					</tr>
					<tr>
						<th>Месец</th>
						<th>Изминати</th>
						'.$standby_adv_row[1].'
					</tr>
			';
					for ($i=0; $i<=11; ++$i)
					{
					$standby_adv_row[2] = ($pref['standby_onoff'] == 1) ? '<td class="border2">'.($pref['garanted_km'] - $result_array[$i]).' км.</td>' : '';
					$text .='
						<tr>
							<td class="border2">'.$months[$i].'</td>
							<td class="border2">'.$result_array[$i].' км.</td>
							'.$standby_adv_row[2].'
						</tr>
					';
					}
					
					$text .='
				</table>
			</td>		
		';
		if ($this->pr %5 == 0)
		echo "</tr>";
		echo $text;
		}
	}	
}
?>