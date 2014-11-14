<?php

if (!defined('eTR_INIT')) { exit; }

function truck_select($truck)
{
	global $Truck, $broi_trucks, $pref, $pageTitle;
	
	for ($i=1; $i<=$pref['broi_trucks']; $i++)
	{
		switch ($truck)
		{
			case $i:
			$truck_x = new Truck;
			$truck_x->pr = $i;
			$k = ($i - 1);
			if(isset($pref['trucks_plate'][$k]))
			{
				$truck_x->truck_name = $pref['trucks_plate'][$k];
				$truck_x->main();
				$pageTitle = "е-КАМИОНИ::".$pref['trucks_plate'][$k];
			}
			else
			{
				error_display("ВНИМАНИЕ", "Не дефиниран камион!!!");
			}
			break;
		}
	}
}

function izminati($pr)
{
	global $sql;
	$pr = "truck".$pr."_cg";
	
	if($sql -> db_Select_gen("DESCRIBE ".MPREFIX.$pr.""))
	{
	$sql -> db_Select("$pr", "MIN(id) AS least, MAX(id) AS max");

	while($rowd = $sql -> db_Fetch())
	{
		$min = $rowd['least'];
		$max = $rowd['max'];
	}
	$sql -> db_Select("$pr", "*", "id='$min'");
	while($rowe = $sql -> db_Fetch())
	{
		$var1 = $rowe['trip'];
		$var2 = $rowe['date'];
	}
	$sql -> db_Select("$pr", "*", "id='$max'");
	while($rowf = $sql -> db_Fetch())
	{
		$var3 = $rowf['trip'];
		$var4 = $rowf['date'];
	}
	$trip = ($var3 - $var1);
	$output = "От ".$var2." до ".$var4." - ".$trip."км.";
	return $output;
}
}

function online()
{
	global $sql, $sql2;
	$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
	$time = time();
	$previous = "120"; #Time to check in seconds
	$timeout = $time-$previous;
	$sql -> db_Select("users", "*", "username='$username' AND timeout > '$timeout'"); #Have you been here in the past two minutes?
	$row_verify = $sql->db_Fetch();
	if (!isset($row_verify['username']))
	{
		$sql -> db_Update("users", "timeout='$time' WHERE username='$username'");
	}
	$sql -> db_Select("users", "*", "timeout > '$timeout'");
	$row_online = $sql -> db_Fetch();
	if (isset($row_online['username'])) #If there is atleast one user online
	{
		do
		{
			$curr_user = $row_online['username'];
			$sql2 -> db_Select("users", "first_name, last_name", "username='$curr_user'");
			$dn = $sql2 -> db_Fetch();
			$first_name = $dn['first_name'];
			$last_name = $dn['last_name'];
			echo ($first_name.' '.$last_name);
			echo ", ";
		}
		while($row_online = $sql -> db_Fetch());
	}
	else
	{
		echo "Няма активни потребители.";
	} 
}


function bg_date($dateFormat,$timestamp=null)
{
	if ($timestamp == null) $timestamp = time();
	if (strstr($dateFormat,"l"))
	{
		$dayFullName = array("1" =>"Понеделник","Вторник","Сряда","Четвъртък","Петък","Събота","Неделя");	   
		$dateFormat = str_replace("l",$dayFullName[date("N",$timestamp)],$dateFormat);
	}
	if (strstr($dateFormat,"D"))
	{
		$dayShortName = array("1" =>"Пон","Вт","Ср","Чет","Пет","Съб","Нед");
		$dateFormat = str_replace("D",$dayShortName[date("N",$timestamp)],$dateFormat);
	}
	if (strstr($dateFormat,"F"))
	{
		$monthFullName = array("1" =>"Януари","Февруари","Март","Април","Май","Юни","Юли","Август","Септември","Октомври","Ноември","Декември");
		$dateFormat = str_replace("F",$monthFullName[date("n",$timestamp)],$dateFormat);
	}
	if (strstr($dateFormat,"M"))
	{
		$monthShortName = array("1" =>"Ян","Фев","Март","Април","Май","Юни","Юли","Авг","Септ","Окт","Ноем","Дек");
		$dateFormat = str_replace("M",$monthShortName[date("n",$timestamp)],$dateFormat);
	}
	return date($dateFormat,$timestamp);
}

function truck_buttons($pr)
{
	global $pref;
	$mod ='';	
	$off1 = ($pref['modules']['chmr'][$pr] == 1) ? '' : 'disabled';	
	$off2 = ($pref['modules']['taho'][$pr] == 1) ? '' : 'disabled';
	if($pref['modules']['gorivo'][$pr] == 1)
	{
		if(($pref['modules']['frigo'][$pr] == 1) and ($pref['modules']['AdBlue'][$pr] == 0))
		{
			$mod = "frigo";
			include ("form_fuel.php");
		}
		elseif(($pref['modules']['AdBlue'][$pr] == 1) and ($pref['modules']['frigo'][$pr] == 0))
		{
			$mod = "adblue";
			include ("form_fuel.php");
		}
		elseif(($pref['modules']['AdBlue'][$pr] == 1) and ($pref['modules']['frigo'][$pr] == 1))
		{
			$mod = "frigo_adblue";
			include ("form_fuel.php");
		}
		else
		{
			$mod = "fuel";
			include ("form_fuel.php");
		}
		$off3 = '';
	}
	else { $off3 = 'disabled'; }
	if($pref['modules']['tovar'][$pr] == 1) { include ("form2.php"); $off4 = '';} else { $off4 = 'disabled'; }
	
	echo '
	<table width="100%" border="0" cellpadding="4" cellspacing="0">
		<tr class="text">
			<td width="25%" class="center"><button id="create-fuel" '.$off3.' style="box-shadow: 5px 5px 5px #888888;">Гориво</button></td>
			<td width="25%" class="center"><button id="create-cargo" '.$off4.' style="box-shadow: 5px 5px 5px #888888;">Товар</button></td>
			<td width="25%" align="center"><input type="button" onclick="location.href=\'chmr.php?pr='.$pr.'\'" value="ЧМР" '.$off1.' style="box-shadow: 5px 5px 5px #888888;"></td>
			<td width="25%" align="center"><input type="button" onclick="location.href=\'upload.php?pr='.$pr.'\'" value="ТАХО" '.$off2.' style="box-shadow: 5px 5px 5px #888888;"></td>
		</tr>
	</table>
	<hr>
	';
}

function go_back($text='Не сте попълнили някое от полетата!!!')
{
	echo '
		<br><center><h1>'.stripslashes($text).'</h1></center>
		<script type="text/javascript">setTimeout(function() {history.go(-1);},1850);</script>
	';
}

function who($id)
{
	global $sql;
	$sql -> db_Select("users", "*", "userid='$id'");
	$resultx = $sql -> db_Fetch();
	return ($resultx['first_name']." ".$resultx['last_name']);
}

function sreden_razhod($truck_numb, $id)
{
	global $sql, $sql3;
	$prev_trip = 0;
	$sql -> db_Select("$truck_numb", "*", "id='".$id."'");
	while($result = $sql -> db_Fetch())
	{
		$last_trip = $result['trip'];
		$last_litters = $result['liters'];
		$full = $result['full'];
	}
	$prev_id = ($id - 1);
	$sql -> db_Select("$truck_numb", "*", "id='".$prev_id."'");
	while($result1 = $sql -> db_Fetch())
	{
		$prev_trip = $result1['trip'];
		$temp_liters = $result1['liters'];
	}
	if(isset($temp_liters))
	{
		if($temp_liters == 0)
		{
			$prev_id = $prev_id - 1;
			$sql -> db_Select("$truck_numb", "*", "id='".$prev_id."'");
			$result1 = $sql -> db_Fetch();
			$prev_trip = $result1['trip'];
		}
	}
	if ($full !== "checked")
	return "Разхода не може да се изчисли. Резервоара не е бил напълнен!";
	
	$sreden_razhod = "Среден разход: ".round(($last_litters / ($last_trip - $prev_trip)) * 100, 2)."лт./100км.";
	return $sreden_razhod;
}

function error_display($title, $message, $width=240)
{
	echo ' <script>
			$(function() {
				$( "#dialog-message" ).dialog({
					modal: true,
					width: '.$width.',
					buttons: {
						Ок: function() {
							$( this ).dialog( "close" );
							history.back(1);
						}
					}
				});
			});
			</script>
	';
	echo '<div id="dialog-message" title="'.$title.'">
			<p>'.$message.'</p>
		</div>
	';
}

function convert_state($modul_count, $counter)
{
	$modul_count[$counter] = (!isset($modul_count[$counter])) ? 0 : 1;
	return $modul_count[$counter];
}

function convert_checked($module)
{
	$result = ($module == 1) ? 'checked' : '';
	return $result;
}

function backup_core()
{
	global $pref, $sql;
	$tmp = base64_encode((serialize($pref)));
	if (!$sql->db_Insert("core", "'pref_backup', '{$tmp}' ")) {
		$sql->db_Update("core", "value='{$tmp}' WHERE name='pref_backup'");
	}
}

function bbcode_to_html($text)
{
	$text = nl2br(htmlentities($text, ENT_QUOTES, 'UTF-8'));
	$in = array(
			'#\[b\](.*)\[/b\]#Usi',
			'#\[i\](.*)\[/i\]#Usi',
			'#\[u\](.*)\[/u\]#Usi',
			'#\[s\](.*)\[/s\]#Usi',
			'#\[img\](.*)\[/img\]#Usi',
			'#\[url\]((ht|f)tps?\:\/\/(.*))\[/url\]#Usi',
			'#\[url=((ht|f)tps?\:\/\/(.*))\](.*)\[/url\]#Usi',
			'#\[left\](.*)\[/left\]#Usi',
			'#\[center\](.*)\[/center\]#Usi',
			'#\[right\](.*)\[/right\]#Usi'
		);
	$out = array(
			'<strong>$1</strong>',
			'<em>$1</em>',
			'<span style="text-decoration:underline;">$1</span>',
			'<span style="text-decoration:line-through;">$1</span>',
			'<img src="$1" alt="Image" />',
			'<a href="$1">$1</a>',
			'<a href="$1">$4</a>',
			'<div style="text-align:left;">$1</div>',
			'<div style="text-align:center;">$1</div>',
			'<div style="text-align:right;">$1</div>'
		);
    $count = count($in)-1;
    for($i=0;$i<=$count;$i++)
    {
        $text = preg_replace($in[$i],$out[$i],$text);
    }
	return $text;
}

function html_to_bbcode($text)
{
	$text = str_replace('<br />','',$text);
	$in = array(
		'#<strong>(.*)</strong>#Usi',
		'#<em>(.*)</em>#Usi',
		'#<span style="text-decoration:underline;">(.*)</span>#Usi',
		'#<span style="text-decoration:line-through;">(.*)</span>#Usi',
		'#<img src="(.*)" alt="Image" />#Usi',
		'#<a href="(.*)">(.*)</a>#Usi',
		'#<div style="text-align:left;">(.*)</div>#Usi',
		'#<div style="text-align:center;">(.*)</div>#Usi',
		'#<div style="text-align:right;">(.*)</div>#Usi'
	);
	$out = array(
		'[b]$1[/b]',
		'[i]$1[/i]',
		'[u]$1[/u]',
		'[s]$1[/s]',
		'[img]$1[/img]',
		'[url=$1]$2[/url]',
		'[left]$1[/left]',
		'[center]$1[/center]',
		'[right]$1[/right]'
	);
    $count = count($in)-1;
    for($i=0;$i<=$count;$i++)
    {
        $text = preg_replace($in[$i],$out[$i],$text);
    }
	return $text;
}

function form_checkbox($form_name, $form_value, $form_checked = 0, $form_tooltip = "", $form_js = "")
{
		$name = ($form_name ? " id='".$form_name.$form_value."' name='".$form_name."'" : "");
		$checked = ($form_checked ? " checked='checked'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input type='checkbox' value='".$form_value."'".$name.$checked.$tooltip.$form_js." />";
}

function r_emote()
{
	global $sysprefs, $pref, $tp;

	//if (!is_object($tp->e_emote))
	//{
	//	require_once(e_HANDLER.'emote_filter.php');
	//	$tp->e_emote = new e_emoteFilter;
	//}
	$az = new e_emoteFilter;
	
	$str = '';
	foreach($az->emotes as $key => $value)		// filename => text code
	{
		$key = str_replace("!", ".", $key);					// Usually '.' was replaced by '!' when saving
		$key = preg_replace("#_(\w{3})$#", ".\\1", $key);	// '_' followed by exactly 3 chars is file extension
		$key = e_UPLOADS."emotes/Skype/" .$key;		// Add in the file path

		$value2 = substr($value, 0, strpos($value, " "));
		$value = ($value2 ? $value2 : $value);
		$value = ($value == '&|') ? ':((' : $value;

		$str .= "\n<a href=\"javascript:addtext(' $value ',true)\"><img src='$key' style='border:0; padding-top:2px;' alt='' /></a> ";
	}

	return "<div class='spacer'>".$str."</div>";
}

?>