<?php
include_once "class.php";
include_once (HEADERF);

$year = varset($_POST['year'], date("Y"));
$current_year = date("Y");

if(USERLV < 4) { echo"<p style='text-align:center;'>Нямате право на достъп до тази страница</p>"; require_once(FOOTERF); exit; }

echo '
		<script language="javascript">
			var newwindow;
			function popit(url){
				newwindow = window.open(url, \'\', "status=no, scrollbars=yes, height=550px, width=650px, resizeable=1");
			}
		</script>
		
	<div id="adv_container" style="width:99%; text-align:center;">
	<div id="row1">
		
		<div style="float:left; width:49%;">
			<b><h1 style="font: 18pt/20pt fantasy, cursive, Serif; font-weight: bold">Качени Тахо Файлове:</h1></b>
			<table width="100%"  border="0" cellpadding="3" cellspacing="0" class="border2">
				<tr class="text">
					<th><b>Дата</b></th>
					<th><b>Име на файл</b></th>
					<th><b>Размер</b></th>
				</tr>
';
			$broika_tacho = $sql -> db_Count("tacho", "(*)");
			if($broika_tacho !== 0)
			{
				$sql2 -> db_Select("tacho", "*", "ORDER BY id DESC LIMIT 5", "no-where");
				while($rowa = $sql2 -> db_Fetch())
				{
					$fe = !file_exists(e_UPLOADS.'tacho_files/'.$rowa["fname"]) ? '<img title="файла липсва!" src="'.e_THEME.'Images/warning_small.png" />'.$rowa["fname"] : '<a href="'.ta_PATH.$rowa["fname"].'">'.$rowa["fname"].'</a>';
					
						
					
					echo'
					<tr class="text">
						<td class="border2">'.$rowa["date"].'</a></td>
						<td class="border2">'.$fe.'</td>
						<td class="border2">'.$rowa["fsize"].'<b> KB</b></td>
					</tr>
					';
				}
				echo "</table>";
				echo "<br />Има ".$broika_tacho." качени файла. Ако искате да видите останалите ".($broika_tacho-5).", натиснете "; ?> <a href="#" onclick="popit('additional_popups.php')">ТУК</a> <?php
			}
			else
			{
				echo '<tr class="text"><td colspan="3">Няма качени Тахо файлове все още.</td></tr></table>';
			}
			echo'
		</div>
		
		<div style="float:right;width:49%;">
			<b><h1 style="font: 18pt/20pt fantasy, cursive, Serif; font-weight: bold">Изпратени ЧМР застраховки:</h1></b>
			<table width="100%"  border="0" cellpadding="3" cellspacing="0" class="border2">
				<tr class="text">
					<th><b>Дата</b></th>
					<th><b>Номер</b></th>
					<th><b>Шаси</b></th>
				</tr>
			';
				$sql -> db_Select("cmr", "*", "ORDER BY id DESC LIMIT 0,7", "no-where");
				while($rowb = $sql -> db_Fetch())
				{
					echo'
					<tr class="text">
						<td class="border2">'.$rowb["date"].'</a></td>
						<td class="border2">'.$rowb["numb"].'</td>
						<td class="border2">'.$rowb["chass"].'</td>
					</tr>
				';
				}
			echo'
			</table>
			<br />
		</div>
		
	</div>
	
	<div id="row2" style="clear: both;">
		<div>
			<hr><br />
			<div align="center"><b><h1 style="font: 18pt/20pt fantasy, cursive, Serif; font-weight: bold">Изминати километри:</h1></b>';
				for ($i=1; $i<=$pref['broi_trucks']; $i++)
				{
					if($sql -> db_Select_gen("DESCRIBE ".MPREFIX."truck".$i."_cg"))
					{
						$k = ($i - 1);
						if($sql -> db_Count("truck".$i."_cg", "(*)") and ($pref['trucks_plate'][$k] != "") and (@!in_array($k, $pref['trucks_off'])))
						{
							echo '<table border="0" width="50%" cellpadding="2" cellspacing="0" class="border2">';
							echo '<tr class="tdh"><th width="20%">'.$pref['trucks_plate'][$k].'</th><td>'.izminati($i).'</td></tr>';
							echo '</table>';
						}
					}
				}
				echo '
				<br />
				<b><h1 style="font: 12pt/14pt fantasy, cursive, Serif; font-weight: bold">По месеци за '; 
					echo '<form method="post" action="'.e_SELF.'"><select onchange="this.form.submit()" name="year">';
					for($i=$current_year-4; $i <= $current_year; $i++)
					{
						$sel = '';
						$k = ($current_year - $i);
						if($year == $i) $sel = "selected";
						echo '<option value="'.($current_year - $k).'" '.$sel.'>'.($current_year - $k).'</option>';
					}
					echo '</select></form>';
				echo ' година:</h1></b>
				<table border="0" width="68%" cellpadding="2" cellspacing="0" align="center">';
					$truck_x = new Truck;
					for ($i=1; $i<=$pref['broi_trucks']; $i++)
					{
						$k = $i - 1;
						if((isset($pref['trucks_plate'][$k])) and (@!in_array($k, $pref['trucks_off'])))
						{
							$truck_x->pr = $i;
							$truck_x->izminati_po_mesec();
						}
					}
					echo '
				</table>
			</div>
			<br />
			
		</div>
	</div>
	
	</div>
';

require_once(FOOTERF);