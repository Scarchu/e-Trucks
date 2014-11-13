<?php
header('Content-Type: text/html; charset=utf-8');		//za mahane sled integrirane w osnowniat sait
$drivers_name = "Росен Ричард Бъчваров";
$company = "'ГАРАНТАУТО' ООД";
?>

<div id="output_container">
	<div id="output_header">
		<p style="text-align: center;"><b>РАБОТЕН ДНЕВНИК</b><br />на <?php echo $drivers_name; ?>, транпортен работник във фирма <?php echo $company; ?></p>
	</div>
	<div id="output_table">
		<table border="1" cellpadding="2" style="text-align: center; margin: 0 auto; width:98%;">
			<tr>
				<th width="6%">Дата</th>
				<th width="6%">Рег. N на МПС</th>
				<th width="5%">Начало на дневн. раб. време</th>
				<th width="5%">Край на дневн. раб. време</th>
				<th width="">Маршрут на движение</th>
				<th width="1%">Работно време извън времето за управление</th>
				<th width="1%">Време на управление</th>
				<th width="1%">Прекъсване времето на управление /почивки/</th>
				<th width="1%">Междудневна почивка</th>
				<th width="4%">Забележки</th>
			</tr>
			<?php
			for($i=1; $i<=20; $i++)
			{
			?>
			<tr>
				<td>10.12.14</td>
				<td>CA1592TK</td>
				<td>7.50</td>
				<td>17.53</td>
				<td>TAulov-brabrand</td>
				<td>0.06</td>
				<td>7.14</td>
				<td>3.01</td>
				<td>60.17</td>
				<td>rest</td>
			</tr>
			<?php } ?>
		</table>
	</div>
</div>
<A HREF="javascript:window.print()">Click to Print This Page</A>