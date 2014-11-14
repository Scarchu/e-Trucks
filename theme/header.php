<?php
ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<link href="<?php echo e_THEME; ?>style.css" rel="stylesheet" type="text/css">
<link rel="icon" type="image/png" href="<?php echo e_THEME; ?>favicon.ico" />
<meta http-equiv="Content-Type" content="text/css;charset=UTF-8">
<title><!--TITLE--></title>
</head>
<body>

<?php
if(isset($_SESSION['user_id']))		//podsigurqwane za wqrnata smqna na header-a (da ne se smenia s USERID!!!)
{
	$sql -> db_Select_gen('select count(*) as nb_new_pm from #pm where ((user1="'.USERID.'" and user1read="no") or (user2="'.USERID.'" and user2read="no")) and id2="1"');
	$nb_new_pm = $sql -> db_Fetch();
	$nb_new_pm = $nb_new_pm['nb_new_pm'];
	$new_message_new = ($nb_new_pm == 1) ? "ново" : "нови";
	$new_message_messages = ($nb_new_pm == 1) ? "съобщение" : "съобщения";
	$company = checkAdmin() ? "_АДМИН_" : mb_strtoupper(USERCO_name, "UTF-8");
	
	$text_h ='
	<table class="header" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td rowspan="2"><p class="logo_hr">e - К А М И О Н И</p></td>
		<td  width="16%"><p class="red"><a href="'.e_BASE.'profile.php?uid='.USERID.'">Здравей  '.USERFN.'</a></p></td>
		<td class="center" width="16%"><p>Имате <a href="'.e_PLUGINS.'pm/list_pm.php">
			'.$nb_new_pm.' '.$new_message_new.'</a> '.$new_message_messages.'.
		</td>
	</tr>
	<tr>
		<td>
			<center>
				<i>Фирма:</i>
				<br />
				<b>'.$company.'</b>
			</center>
		</td>
		<td class="center"><p><a href="'.e_PLUGINS.'pm/new_pm.php">Напиши ново ЛС.</p></a></td>
	</tr>
	</table>
	<hr>
	
	<table width="100%" border="0" cellpadding="1" cellspacing="1">
		<tr class="text">
			<td align="left">
				<a href="'.e_BASE.'index.php">Начало</a> | 
				  
				<a href="'.e_PLUGINS.'forum/">Форум</a> | ';
				if(USERLV >= 4) $text_h .='<a href="'.e_BASE.'advanced.php">Допълнителни</a> | ';
				if(USERLV >= 4) $text_h .='<a href="'.e_BASE.'designations.php">Назначения</a> | ';
				$text_h .='<a href="'.e_BASE.'logout.php">Изход</a>
			</td>
			<td align="right">';
				include e_THEME."otchet.php";
			$text_h .='</td>
		</tr>
	</table> 
	<hr>';
	
}
else
{
$text_h ='
<table class="header" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td rowspan="2"><p class="logo_hr">e - К А М И О Н И</p></td>
		<td  width="26%"><p class="red">Здравей - Страннико</p></td>
	</tr>
	<tr>
		<td width="16%"><blink><p class="red">Логни се!</p></blink></td>
	</tr>
</table>
<hr>	
';
}
echo $text_h;
?>