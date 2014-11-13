<?php
require_once "class.php";
//require_once "handlers/java_includes.php";
if(USERLV < 4) { die; }

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/css;charset=UTF-8">
	<link href="<?php echo e_THEME; ?>popup.css" rel="stylesheet" type="text/css">
	<title>Тахо файлове</title>
</head>

<div align="center"><b><h1 style="font: 18pt/20pt fantasy, cursive, Serif; font-weight: bold">Качени Тахо Файлове:</h1></b>
	<table width="50%"  border="1" cellpadding="3" cellspacing="0" class="border2">
		<tr class="text">
			<th><b>Дата</b></th>
			<th><b>Име на файл</b></th>
			<th><b>Размер</b></th>
		</tr>

<?php

$sql2 -> db_Select("tacho", "*", "ORDER BY id DESC", "no-where");
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
?>
</table>
<br />
<button onclick="JavaScript:window.close()">Затвори</button>
<br />