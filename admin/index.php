<?php
include_once "../class.php";
include_once (HEADERF);

if(ADMIN)
{

$mode = varset($_GET['mode']);
//$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';
/*if(e_QUERY)
{
	$tmp = explode('=', e_QUERY);
	$action = $tmp[0];
	$mode = $tmp[1];
	unset ($tmp);
}*/

if(isset($_POST['new_company'])) //za firmite
{
	$company = $_POST['comp_name'];
	$company_VAT = $_POST['comp_VAT'];
	$company_location = htmlspecialchars($_POST['comp_location']);
	if(!empty($company) and !empty($company_VAT) and !empty($company_location))
	{	
		if(is_numeric($company_VAT))
		{
			$sql -> db_Insert("companies", "'', '".$company."', '', '', '".$company_VAT."', '".$company_location."', ''");
		}
		else
		{
			go_back("Данъчният номер е неверен!");
		}
	}
	else
	{
		go_back();
	}
}

if(isset($_POST['settings'])) //za modulite
{
	$modules = array("frigo" => array(), "chmr" => array(), "taho" => array(), "gorivo" => array(), "tovar" => array(), "AdBlue" => array()); 
	
	$checkbox1 = varset($_POST['frigo']);
	$checkbox2 = varset($_POST['chmr']);
	$checkbox3 = varset($_POST['taho']);
	$checkbox4 = varset($_POST['gorivo']);
	$checkbox5 = varset($_POST['tovar']);
	$checkbox6 = varset($_POST['AdBlue']);
	
	for($i=1; $i<($pref['broi_trucks'] + 1); $i++)
	{		
		$pref['modules']['frigo'][$i]	= convert_state($checkbox1, $i);
		$pref['modules']["chmr"][$i] 	= convert_state($checkbox2, $i);
		$pref['modules']["taho"][$i] 	= convert_state($checkbox3, $i);
		$pref['modules']["gorivo"][$i] 	= convert_state($checkbox4, $i);
		$pref['modules']["tovar"][$i] 	= convert_state($checkbox5, $i);
		$pref['modules']["AdBlue"][$i] 	= convert_state($checkbox6, $i);
	}
	save_prefs();
	header("Location: ".e_ADMIN."index.php?mode=modules");
	exit();
}

if(isset($_POST['mainten'])) // za zatwarianeto
{
	$maint = (isset($_POST['maint'])) ? $_POST['maint'] : "0";
	$pref['maintenance'] = $maint;
	$pref['maintenance_text'] = $tp -> toForm($_POST['maint_text']);
	save_prefs();
	echo '<div class="message">Данните са записани успешно!</div><br />';
	header( "refresh:2;url=".e_ADMIN."index.php?mode=maint" );
}

if(isset($_POST['prefs'])) //za obshtite nastroiki
{
	$pref['color_cash'] 	= $_POST['color1'];
	$pref['color_empty'] 	= $_POST['color2'];
	$pref['broi_trucks'] 	= varset($_POST['broi_trucks'], 1);
	$pref['standby_onoff'] 	= varset($_POST['standby_onoff'], 0);
	$pref['garanted_km'] 	= varset($_POST['garant_trip'], 0);
	$pref['latest_usersc']	= varset($_POST['latest_usersc'], 2);
	$pref['users_to_show']	= varset($_POST['users_to_show'], 1);
	$pref['smiley_activate']= varset($_POST['smiley_activate'], 0);
	$pref['make_clickable']	= varset($_POST['make_clickable'], 0);
	$pref['link_replace']	= varset($_POST['link_replace'], 0);
	$pref['link_text']		= varset($_POST['link_text']);
	$pref['upload_size'] 	= varset($_POST['upload_maxsize'], 0);
	$pref['chat_lines']		= (is_numeric($_POST['chat_lines'])) ? intval($_POST['chat_lines']) : $pref['chat_lines'];
	$pref['forum_cposts']	= $_POST['forum_cposts'];
	$pref['forum_cnew'] 	= $_POST['forum_cnew'];
	$pref['chmr_username'] 	= varset($_POST['chmr_username']);
	$pref['chmr_password'] 	= varset($_POST['chmr_password']);
	$pref['chmr_from'] 		= varset($_POST['chmr_from']);
	$pref['chmr_to1'] 		= varset($_POST['chmr_to1']);
	$pref['chmr_to2'] 		= varset($_POST['chmr_to2']);
	$pref['chmr_subject'] 	= varset($_POST['chmr_subject']);
	save_prefs();
	echo '<div class="message">Данните са записани успешно!</div><br />';
	header( "refresh:2;url=".e_ADMIN."index.php?mode=pref" );
}
//-----------------------------------------------------------------------------------------------------------------------------//

if(isset($_POST['trucks'])) // za nastroikata na kamionite
{
	$pref['trucks_plate'] = $_POST['truck_plate'];
	$pref['trucks_off']   = varset($_POST['off']);
	save_prefs();		
	echo '<div class="message">Данните са записани успешно!</div><br />';
	header( "refresh:2;url=".e_ADMIN."index.php?mode=truck" );
}
?>

<div class="center"><h2 style="{font: 14pt/16pt fantasy, cursive, Serif} ">АДМИНИСТРАЦИЯ</h1></div>
<br />
<table align="center" width="90%" border="0" cellpadding="3">
	<tr class="center">
		<td width=25%"><a href="<?php echo e_SELF; ?>?mode=pref"><img src="../theme/Images/prefs_32.png"><br />Настройки</a></td>
		<td width=25%"><a href="<?php echo e_SELF; ?>?mode=truck"><img src="../theme/Images/truck.png"><br />Настройка камиони</a></td>
		<td width=25%"><a href="<?php echo e_SELF; ?>?mode=users"><img src="../theme/Images/users_32.png"><br />Потребители</a></td>
		<td width=25%"><a href="<?php echo e_SELF; ?>?mode=dbase"><img src="../theme/Images/database_32.png"><br />База Данни</a></td>
	</tr>
	<tr class="center">
		<td><a href="<?php echo e_SELF; ?>?mode=maint"><img src="../theme/Images/maintain_32.png"><br />Затваряне</a></td>
		<td><a href="<?php echo e_SELF; ?>?mode=modules"><img src="../theme/Images/modules_32.png"><br />Модули</a></td>
		<td><a href="<?php echo e_SELF; ?>?mode=companies"><img src="../theme/Images/modules_32.png"><br />Фирми</a></td>
		<td><a href="<?php echo e_SELF; ?>?mode=gallery"><img src="../theme/Images/.png"><br />Галерия</a></td>
	</tr>
	<tr class="center">
		<td><a href="<?php echo e_SELF; ?>?mode=users_classes"><img src="../theme/Images/users_32.png"><br />Потребителски класове</a></td>
	</tr>
</table>
<br />
<hr color="green" width="70%">

<?php
$pageTitle = 'e-КАМИОНИ::Админ';

//==============================================User classes===============================================================//

if($mode == "users_classes")
{
	header("Location: userclass2.php");
	exit;
}

//=================================================Gallery=================================================================//

if($mode == "gallery")
{
	$action = varset($_GET['action']);
	echo "
		<h1>Галерия</h1>
		<br />
		<div class='center'>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=1';\">Изглед</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=2';\">База данни</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=3';\">файл права</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=4';\">Файл обновяване</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=5';\">Езици</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=6';\">Основни</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=7';\">Мета инфо</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=8';\">Преглеждане файлове</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=9';\">Сигурност</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=10';\">Слайдшоу</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=11';\">Скорост</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=12';\">Миниатюри</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=13';\">Потребителски достъп</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=14';\">Потребителски галерии</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=15';\">Админ лог</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=16';\">Бъг лог</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=gallery&action=17';\">Воден знак</button>
		</div>
		<br />
		<hr>
		<br />
		";
	switch($action)
	{
		case 1:	require e_PLUGINS.'autogallery/admin_appearance.php';	break;
		case 2:	require e_PLUGINS.'autogallery/admin_dbupdate.php';		break;
		case 3:	require e_PLUGINS.'autogallery/admin_dochmod.php';		break;
		case 4:	require e_PLUGINS.'autogallery/admin_fileupdate.php';	break;
		case 5:	require e_PLUGINS.'autogallery/admin_languages.php';	break;
		case 6:	require e_PLUGINS.'autogallery/admin_main.php';			break;
		case 7:	require e_PLUGINS.'autogallery/admin_metadata.php';		break;
		case 8:	require e_PLUGINS.'autogallery/admin_reviewuploads.php';break;
		case 9:	require e_PLUGINS.'autogallery/admin_security.php';		break;
		case 10:require e_PLUGINS.'autogallery/admin_slideshow.php';	break;
		case 11:require e_PLUGINS.'autogallery/admin_speed.php';		break;
		case 12:require e_PLUGINS.'autogallery/admin_thumbnails.php';	break;
		case 13:require e_PLUGINS.'autogallery/admin_useraccess.php';	break;
		case 14:require e_PLUGINS.'autogallery/admin_usergalleries.php';break;
		case 15:require e_PLUGINS.'autogallery/admin_viewadminlog.php';	break;
		case 16:require e_PLUGINS.'autogallery/admin_viewdebuglog.php';	break;
		case 17:require e_PLUGINS.'autogallery/admin_watermark.php';	break;
	}
}

//==================================================Firmi==================================================================//
if($mode == "companies")
//if(isset($action) && $mode == 'companies')
{
	$pageTitle .= "::Компании";
	$sql -> db_Select("companies", "*", "ORDER BY id ASC", "no-where");
	echo'
		<div align="center">
			<h2>Фирми</h2>
			<form action="'.e_ADMIN.'company.php" method="post">
			<select onchange="this.form.submit()" name="company_selection">
				<option value="0">Избери фирма</option>
	';
		while($result = $sql -> db_Fetch())
		{
			echo '<option value="'.$result['id'].'">'.$result['name'].'</option>';
		}
	echo'
			</select>
			</form>
		</div>
	
		<br /><hr class="red" width="60%">
		<h2 align="center">Добавяне на Фирма</h2>
		<div id="comp_edit">
			<form id="company" method="post" action="'.e_SELF.'">
				<table border="1" width="100%">
				<tr>
					<th class="small_text">Име на фирмата</th>
					<td style="text-align: right;"><input id="name" type="text" name="comp_name" /></td>
				</tr>
				<tr>
					<th class="small_text">ДДС номер</th>
					<td style="text-align: right;"><input type="text" name="comp_VAT" /></td>
				</tr>
				<tr>
					<th class="small_text">Местонахождение</th>
					<td style="text-align: right;"><textarea style="resize: none;" rows="2 " cols="20" name="comp_location"></textarea></td>
				</tr>
			</table>
			<br />
			<input type="submit" name="new_company" value="Запиши" />
			<input type="submit" name="new_company" value="Назад" onclick="window.history.back()" />
			</form>
		</div>
	';
}

//==================================================Moduli==================================================================//
if($mode == "modules")
//if(isset($action) && $mode == 'modules')
{
?>

<script type="text/javascript">
$(document).ready(function() {
	$('input:checkbox:not([safari])').checkbox();
		$('input[safari]:checkbox').checkbox({cls:'jquery-safari-checkbox'});
		$('input:radio').checkbox();
			});

			displayForm = function (elementId)
			{
				var content = [];
				$('#' + elementId + ' input').each(function(){
					var el = $(this);
					if ( (el.attr('type').toLowerCase() == 'radio'))
					{
						if ( this.checked )
							content.push([
								'"', el.attr('name'), '": ',
								'value="', ( this.value ), '"',
								( this.disabled ? ', disabled' : '' )
							].join(''));
					}
					else
						content.push([
							'"', el.attr('name'), '": ',
							( this.checked ? 'checked' : 'not checked' ), 
							( this.disabled ? ', disabled' : '' )
						].join(''));
				});
				alert(content.join('\n'));
			}
		</script>
<h1>Модули</h1>
<br />
<?php
echo '
<form id="myform" method="post" action="'.e_SELF.'">
<table cellspacing="6" class="border2" align="center">
			<th>Камион</th>
			<th>Фриго</th>
			<th>ЧМР</th>
			<th>ТАХО</th>
			<th>Гориво</th>
			<th>Товар</th>
			<th>AdBlue</th>
';
for ($i=0; $i<$pref['broi_trucks']; $i++)
	{
		$k = ($i + 1);
		echo '
		<tr>
					<td>'.$pref['trucks_plate'][$i].'</td>
					<td><input type="checkbox" name="frigo['.$k.']" class="top5" '.convert_checked($pref['modules']['frigo'][$k]).'/></td>
					<td><input type="checkbox" name="chmr['.$k.']" class="top5" '.convert_checked($pref['modules']['chmr'][$k]).'/></td>
					<td><input type="checkbox" name="taho['.$k.']" class="top5" '.convert_checked($pref['modules']['taho'][$k]).'/></td>
					<td><input type="checkbox" name="gorivo['.$k.']" class="top5" '.convert_checked($pref['modules']['gorivo'][$k]).'/></td>
					<td><input type="checkbox" name="tovar['.$k.']" class="top5" '.convert_checked($pref['modules']['tovar'][$k]).'/></td>
					<td><input type="checkbox" name="AdBlue['.$k.']" class="top5" '.convert_checked($pref['modules']['AdBlue'][$k]).'/></td>
		</tr>
		';
	}
?>
</table>
<input type="submit" name="settings" value="Запиши" />
</form>
<?php
}
//===================================================Zatvariane za profilaktika=============================================//
if($mode == "maint")
//if(isset($action) && $mode == 'maint')
{
	echo '<div align="center">
		<form name="maint" method="post" action="'.e_SELF.'">
			<table border="1" class="center">
				<tr>
					<th>Включване на затварянето:</th>
					<td><input type="checkbox" name="maint" value="1"'; if($pref['maintenance'] == 1) echo "checked='checked'"; echo'/></td>
				</tr>
				<tr>
					<th>Причина за затварянето:</th>
					<td><textarea rows="4" cols="50" name="maint_text">'.$pref['maintenance_text'].'</textarea></td>
				</tr>
			</table>
			<br />
			<div align="center"><input type="submit" name="mainten" value="Запази"></div>
		</form>
		</div>
	';
}

//=======================================================Obshti Nastroiki===================================================//
if($mode == "pref")
//if(isset($action) && $mode == 'pref')
{
	echo '<script type="text/javascript" src="jscolor/jscolor.js"></script>';
	echo '<div align="center">
			<form name="form" method="post" action="'.e_SELF.'">
				<table width="80%" class="admin_table" border="1" cellpadding="2" cellspacing="0">
					<th colspan="2" align="center"><b>Основни Настройки</b>	 <img src="'.e_THEME.'Images/settings_16.png" /></th>
					<tr>
						<td>Цвят за Кеш горивото:</td><td><input class="color {hash:true}" value="'.$pref['color_cash'].'" name="color1"></td>
					</tr><tr>
						<td>Цвят за Празен курс:</td><td><input class="color {hash:true}" value="'.$pref['color_empty'].'" name="color2"></td>
					</tr><tr>
						<td>Брой камиони:</td><td><input type="number" value="'.$pref['broi_trucks'].'" name="broi_trucks" maxlength="10"></td>
					</tr><tr>
						<td>Гарантирани километри (включено / изключено)</td><td>'.form_checkbox("standby_onoff", "1", $pref["standby_onoff"]).'</td>
					</tr><tr>
						<td>Гарантирани километри:</td><td><input type="number" value="'.$pref['garanted_km'].'" name="garant_trip" maxlength="5" '.($pref["standby_onoff"] ? "" : "disabled").'/></td>
					</tr><tr>
						<td>Брой потебители влизали последно:</td><td><input type="number" value="'.$pref['latest_usersc'].'" name="latest_usersc" maxlength="5"></td>
					</tr><tr>
						<td>Брой потебители на страница (админ панел):</td><td><input type="number" value="'.$pref['users_to_show'].'" name="users_to_show" maxlength="2"></td>
					</tr><tr>
						<td>Включване на емотиконите:</td><td>'.form_checkbox("smiley_activate", "1", $pref["smiley_activate"]).'</td>
					</tr><tr>
						<td>Направи публикуваните линкове активни:</td><td>'.form_checkbox("make_clickable", "1", $pref["make_clickable"]).'</td>
					</tr><tr>
						<td>Замяна на линковете?:</td><td>'.form_checkbox("link_replace", "1", $pref["link_replace"]).'</td>
					</tr><tr>
						<td>Текст за замяна на линковете: </td><td><input type="text" value="'.$pref['link_text'].'" name="link_text" maxlength="50"></td>
					</tr><tr>
					<th colspan="2" align="center"><b>Настройки на качването</b>	 <img src="'.e_THEME.'Images/uploads_16.png" /></th>
					</tr><tr>
						<td>Максимален Размер на качваният файл (в килобайта):</td><td><input type="text" value="'.$pref['upload_size'].'" name="upload_maxsize"></td>
					</tr><tr>
					<th colspan="2" align="center"><b>Настройки на Чата</b>	 <img src="'.e_THEME.'Images/chat_16.png" /></th>
					</tr><tr>
						<td>Брой видими съобщения в чата:</td><td><input type="number" value="'.$pref['chat_lines'].'" name="chat_lines"></td>
					</tr><tr>
					<th colspan="2" align="center"><b>Настройки на форума</b>	 <img src="'.e_THEME.'Images/forum_16.png" /></th>
					</tr><tr>
						<td>Брой постове на страница:</td><td><input type="text" value="'.$pref['forum_cposts'].'" name="forum_cposts"></td>
					</tr><tr>
						<td>Брой най-нови теми:</td><td><input type="text" value="'.$pref['forum_cnew'].'" name="forum_cnew"></td>
					</tr><tr>
					<th colspan="2" align="center"><b>Настройки на SMTP на И-мейла (ЧМР)</b>	 <img src="'.e_THEME.'Images/notify_16.png" /></th>
					<tr>
						<td>Потребител:</td><td><input type="text" value="'.$pref['chmr_username'].'" name="chmr_username" /></td>
					</tr><tr>
						<td>Парола:</td><td><input type="password" value="'.$pref['chmr_password'].'" name="chmr_password" /></td>
					</tr><tr>
						<td>От:</td><td><input type="text" value="'.$pref['chmr_from'].'" name="chmr_from" /></td>
					</tr><tr>
						<td>До:</td><td><input type="text" value="'.$pref['chmr_to1'].'" name="chmr_to1" /></td>
					</tr><tr>
						<td>Допълнително До:</td><td><input type="text" value="'.$pref['chmr_to2'].'" name="chmr_to2" /></td>
					</tr><tr>
						<td>Тема:</td><td><input type="text" value="'.$pref['chmr_subject'].'" name="chmr_subject" /></td>
					</tr>
				</table>
				<br />
				<div align="center"><input type="submit" name="prefs" value="Запази"></div>
			</form>
		</div>
	';
}
//=======================================================Krai Obshti nastroiki===============================================//
//=======================================================Nastroiki kamioni===============================================//
if($mode == "truck")
//if(isset($action) && $mode == 'truck')
{
	echo '
		<div align="center">
		<form name="trucks" method="post" action="'.e_SELF.'">
		<table width="80%" border="1" cellspacing="0" cellpadding="5" class="admin_table">
		<th width="30%">Описание</th>
		<th width="15%">Стойност</th>
		<th width="5%">Изключване</th>
		<th width="15%">Създаване на DB таблици</th>
		<th width="15%">Изтриване на DB таблици</th>
	';
	for ($i=0; $i<=($pref['broi_trucks'] - 1); $i++)
	{
		$k = ($i + 1);
		if($sql -> db_Select_gen("DESCRIBE ".MPREFIX."truck".$k."_lt") AND $sql2 -> db_Select_gen("DESCRIBE ".MPREFIX."truck".$k."_cg"))
		{
			$link_to_dbc_create = "Създадени";
			$link_to_dbc_delete = "<a href='truck_db.php?mode=delete&pr=$k'>!! Изтриване !!</a>";
		}
		else
		{
			$link_to_dbc_create = "<a href='truck_db.php?mode=create&pr=$k'>Създай</a>";
			$link_to_dbc_delete = "<p>!! Изтриване !!</p>";
		}
		error_reporting(0);
		$bckgrnd = (!$pref['trucks_plate'][$i]) ? '#aa4411' : '#FFFFFF';
		echo '<tr>
			<td bgcolor="'.$bckgrnd.'">Регистрационен номер на камион '.$k.'</td>
			<td align="center" bgcolor="'.$bckgrnd.'"><input type="text" value="'.$pref['trucks_plate'][$i].'" name="truck_plate[]" /></td>
			<td align="center" bgcolor="'.$bckgrnd.'"><input type="checkbox" value="'.$i.'" name="off[]" '; if (in_array($i, $pref['trucks_off'])) echo "checked='checked'"; echo '/></td>
			<td align="center" bgcolor="'.$bckgrnd.'">'.$link_to_dbc_create.'</td>
			<td align="center" bgcolor="'.$bckgrnd.'">'.$link_to_dbc_delete.'</td>
			</tr>
			
		';
	}
	echo '
		</table>
		<p class="red">Докато не бъдат въведени номера на съответните камиони, те няма да бъдат активни / видими !!!</p>
		<div align="center"><input type="submit" name="trucks" value="Запази"></div>
		</form>
		</div>
		<br />
	';
}
//========================================================Krai Nastroiki kamioni=============================================//
//=========================================================Potrebiteli=======================================================//
if($mode == "users")
//if(isset($action) && $mode == 'users')
{

$page_limit = $pref['users_to_show'];
date_default_timezone_set('Europe/Sofia');

if(isset($_POST['doBan']) == 'Ban')
{
	if(!empty($_POST['u']))
	{
		foreach ($_POST['u'] as $uid)
		{
			$sql -> db_Update("users", "banned='1' where userid='".$uid."' and username <> 'admin'");	
		}
	}
	header("Location: ".e_ADMIN."index.php?mode=users");
	exit();
}

if(isset($_POST['doUnban']) == 'Unban')
{
	if(!empty($_POST['u']))
	{
		foreach ($_POST['u'] as $uid)
		{
			$sql -> db_Update("users", "banned='0' WHERE userid='".$uid."'");
		}
	}
	header("Location: ".e_ADMIN."index.php?mode=users");
	exit();
}

if(isset($_POST['doDelete']) == 'Delete')
{
	if(!empty($_POST['u']))
	{
		foreach ($_POST['u'] as $uid)
		{
			//mysql_query("delete from users where userid='$uid' and `user_name` <> 'admin'");
			$sql -> db_Delete("users", "userid='".$uid."' AND username <> 'admin'");
		}
	}
	header("Location: ".e_ADMIN."index.php?mode=users");
	exit();
}
$sql3 = new db;
$rs_all = "SELECT COUNT(*) AS total_all FROM #users ";
$rs_active = "SELECT COUNT(*) AS total_active FROM #users WHERE banned='0'";
$not_active = "SELECT COUNT(*) AS not_active FROM #users WHERE banned='1'";
$sql -> db_Select_gen($rs_all);
$sql2 -> db_Select_gen($rs_active);
$sql3 -> db_Select_gen($not_active);
$all = $sql -> db_Fetch();
$active = $sql2 -> db_Fetch();
$not_active = $sql3 -> db_Fetch();

?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="74%" class="center" valign="top" style="padding: 10px;"><h2><font color="#FF0000">Потребители</font></h2>
			<table width="100%" border="0" cellpadding="5" cellspacing="0" class="myaccount">
				<tr>
					<td>Общо потребители: <?php echo $all['total_all'];?></td>
					<td>Активни потребители: <?php echo $active['total_active']; ?></td>
					<td>Неактивни потрбители: <?php echo $not_active['not_active']; ?></td>
				</tr>
			</table>
			<p><?php 
				if(!empty($msg))
				{
					echo $msg[0];
				}
			?></p>
			<?php
			$total = $all['total_all'];
			if (!isset($_GET['page']) )
			{
				$start=0;
			}
			else
			{
				$start = ($_GET['page'] - 1) * $page_limit;
			}
			$query = "SELECT * FROM #users ORDER BY userid ASC LIMIT ".$start.",".$page_limit."";
			$sql -> db_Select_gen($query);
			$total_pages = ceil($total/$page_limit);
 
			if ($total > $page_limit)
			{
				echo "<p align='right'><div><strong>Страници:</strong> ";
				$i = 0;
				while ($i < $total_pages)
				{
					$page_no = $i+1;
					//$qstr = preg_replace("&page=[0-9]+","",e_QUERY);
					//echo e_QUERY;
					//echo "<a href=\"admin.php?$qstr&page=$page_no\">$page_no</a> ";
					echo "<a href=index.php?mode=users&page=".$page_no.">$page_no</a> , ";
					$i++;
				}
				echo "</div></p>";
			} 
			?>
			
			<form name "searchform" action="<?php echo e_SELF; ?>?mode=users" method="post">
				<table width="90%" border="0" align="center" cellpadding="2" cellspacing="0">
					<tr class="header"> 
						<td width="4%"><strong>ID</strong></td>
						<td><strong>Последно посещение</strong></td>
						<td><div align="center"><strong>Потребителско Име</strong></div></td>
						<td width="24%"><strong>Поща</strong></td>
						<td width="10%"><strong>Активен</strong></td>
						<td width="10%"><strong>IP</strong></td>
						<td width="25%"><strong>Инструменти</strong></td>
					</tr>
					<tr> 
						<td>&nbsp;</td>
						<td width="10%">&nbsp;</td>
						<td width="17%"><div align="center"></div></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<?php while ($rrows = $sql -> db_Fetch())
					{
						$last_logged = ($rrows['last_loggedin'] != '') ? bg_date("d.M.y G:i", $rrows['last_loggedin']) : 'Не се е логвал';
						$background= ($rrows['userid'] % 2 == 0) ? "#eeeeee" : "#cccccc";
					echo '
						<tr bgcolor="'.$background.'">
						<td>';
						if($rrows['userid'] != 1)
						{
							echo '<input name="u[]" type="checkbox" value="'.$rrows['userid'].'" id="u[]">';
						}
						echo '
						</td>
							<td>'.$last_logged.'</td>
							<td> <div align="center">'.$rrows['username'].'</div></td>
							<td>'.$rrows['user_email'].'</td>
							<td><span id="ban'.$rrows['userid'].'"> ';
							if(!$rrows['banned']) { echo "Да"; } else {echo "Не"; }
							echo '
							</span> </td>
							<td>'.$rrows['user_ip'].'</td>
							<td><font size="2">';
							if($rrows['userid'] != 1)
							{
							?>
								<a href="javascript:void(0);" onclick='$.get("do.php",{ cmd: "ban", id: "<?php echo $rrows['userid']; ?>" } ,function(data){ $("#ban<?php echo $rrows['userid']; ?>").html(data); });'>Деактивирай</a> |
								<a href="javascript:void(0);" onclick='$.get("do.php",{ cmd: "unban", id: "<?php echo $rrows['userid']; ?>" } ,function(data){ $("#ban<?php echo $rrows['userid']; ?>").html(data); });'>Активирай</a> |
								<a href="javascript:void(0);" onclick='$("#edit<?php echo $rrows['userid'];?>").show("slow");'>Промени</a> 
							<?php
							}
							?></font></td>
						</tr>
						<tr> 
							<td colspan="7">
								<div style="display:none;" id="edit<?php echo $rrows['userid']; ?>">
									<input type="hidden" name="id<?php echo $rrows['userid']; ?>" id="id<?php echo $rrows['userid']; ?>" value="<?php echo $rrows['userid']; ?>">
									<table cellpadding="1" cellspacing="1" border="0" style="font: normal 12px arial; text-align: left; padding:10px; background: #e6f3f9; width: 80%;">
										<tr>
											<td>Потребителско име:</td><td><input name="user_name<?php echo $rrows['userid']; ?>" id="user_name<?php echo $rrows['userid']; ?>" type="text" size="10" value="<?php echo $rrows['username']; ?>" ></td>
										</tr><tr>	
											<td>Име:</td><td><input name="first_name<?php echo $rrows['userid']; ?>" id="first_name<?php echo $rrows['userid']; ?>" type="text" size="10" value="<?php echo $rrows['first_name']; ?>" ></td>
										</tr><tr>
											<td>Фамилия:</td><td><input name="last_name<?php echo $rrows['userid']; ?>" id="last_name<?php echo $rrows['userid']; ?>" type="text" size="10" value="<?php echo $rrows['last_name']; ?>" ></td>
										</tr><tr>
											<td>Потребителска поща:</td><td><input id="user_email<?php echo $rrows['userid']; ?>" name="user_email<?php echo $rrows['userid']; ?>" type="text" size="20" value="<?php echo $rrows['user_email']; ?>" ></td>
										</tr><tr>
											<td>Фирма:</td><td><input id="company<?php echo $rrows['userid']; ?>" name="company<?php echo $rrows['userid']; ?>" type="text" size="1" value="<?php echo $rrows['company']; ?>" /> 1->Гарантауто, 2->Нимар транс</td>
										</tr><tr>
											<td>Ниво:</td><td><input id="user_level<?php echo $rrows['userid']; ?>" name="user_level<?php echo $rrows['userid']; ?>" type="text" size="5" value="<?php echo $rrows['user_level']; ?>" > 0->Форум, 1->Шофьор, 4->Управител, 5->Админ</td>
										</tr><tr>
											<td>Нова парола:</td><td><input id="pass<?php echo $rrows['userid']; ?>" name="pass<?php echo $rrows['userid']; ?>" type="text" size="20" value="" > (leave blank)</td>
										</tr><tr>
											<td><input name="doSave" type="button" id="doSave" value="Запази"
											onclick='$.get("do.php",{ cmd: "edit", pass:$("input#pass<?php echo $rrows['userid']; ?>").val(),user_level:$("input#user_level<?php echo $rrows['userid']; ?>").val(),user_email:$("input#user_email<?php echo $rrows['userid']; ?>").val(),company:$("input#company<?php echo $rrows['userid']; ?>").val(),user_name: $("input#user_name<?php echo $rrows['userid']; ?>").val(),first_name:$("input#first_name<?php echo $rrows['userid']; ?>").val(),last_name:$("input#last_name<?php echo $rrows['userid']; ?>").val(),id: $("input#id<?php echo $rrows['userid']; ?>").val() } ,function(data){ $("#msg<?php echo $rrows['userid']; ?>").html(data); });'> 
											<a  onclick='$("#edit<?php echo $rrows['userid'];?>").hide();' href="javascript:void(0);">Затвори</a></td>
										</tr>
									</table>
									<div style="color:red" id="msg<?php echo $rrows['userid']; ?>" name="msg<?php echo $rrows['userid']; ?>"></div>
								</div>
							</td>
						</tr>
					<?php } ?>
				</table>
				<p><br>
					<input name="doBan" type="submit" id="doBan" value="Деактивирай">
					<input name="doUnban" type="submit" id="doUnban" value="Активирай">
					<input name="doDelete" type="submit" id="doDelete" value="Изтрий">
					<input name="query_str" type="hidden" id="query_str" value="<?php echo e_QUERY; ?>">
				</p>
			</form>
			<?php
			if(isset($_POST['doSubmit']) == 'Create')
			{
				$query = "SELECT COUNT(*) AS total FROM #users WHERE username='".$_POST['user_name']."' OR user_email='".$_POST['user_email']."'";
				$sql -> db_Select_gen($query);
				$dups = $sql -> db_Fetch();
				if($dups['total'] > 0)
				{
					die("<h3>Това потребителско име съществува!</h3>");
				}

				if(!empty($_POST['pwd']))
				{
					$pwd = $_POST['pwd'];	
					$hash = PwdHash($_POST['pwd']);
				}
				else
				{
					$pwd = GenPwd();
					$hash = PwdHash($pwd);
				}
				if($sql -> db_Insert("users", "'', '".$_POST['first_name']."', '".$_POST['last_name']."', '".$_POST['user_email']."', '".$_POST['user_name']."', '".$hash."', '".$_POST['description']."', '', '', '".$_POST['user_level']."', '',1 , '".$_POST['company']."', '', '', '', '', '', ''"))
				echo "<div class=\"msg\">Създаден е потребител с парола $pwd </div>";
			}
			?>
	        <h2><font color="#FF0000">Добавяне на Нов потребител</font></h2>
			<table width="80%" border="0" cellpadding="1" cellspacing="2" class="left">
				<form name="form1" method="post" action="<?php echo e_SELF; ?>?mode=users">
					<tr>
						<td>Потребителско име</td>
						<td><input name="user_name" type="text" id="user_name"></td>
					</tr><tr>
						<td>Име</td>
						<td><input name="first_name" type="text" id="first_name"></td>
					</tr><tr>
						<td>Фамилия</td>
						<td><input name="last_name" type="text" id="last_name"></td>
					</tr><tr>
						<td>Поща </td>
						<td><input name="user_email" type="text" id="user_email"></td>
					</tr><tr>
						<td>Ниво на достъп </td>
						<td>
							<select name="user_level" id="user_level">
								<option value="0">Гост</option>
								<option value="1">Шофьор</option>
								<option value="4">Управител</option>
								<option value="5">Админ</option>
							</select>
						</td>
					</tr><tr>
						<td>Към Фирма</td>
						<td>
							<select name="company" id="company">
								<option value="0">Избиране на фирма</option>
								<?php
								$sql2->db_Select("companies", "*", "ORDER BY id", "no-where");
								while($comp_list = $sql2->db_Fetch())
								{
									echo '<option value="'.$comp_list['id'].'">'.$comp_list['name'].'</option>';
								}
								?>
							</select>
					</tr><tr>
						<td>Описание</td>
						<td><input name="description" type="text" id="description"></td>
					</tr><tr>
						<td>Парола </td>
						<td><input name="pwd" type="text" id="pwd">
						(ако полето е празно, ще се генерира случайна парола)</td>
					</tr><tr> 
						<td colspan="2" class="left"><input name="doSubmit" type="submit" id="doSubmit" value="Създай"></td>
					</tr><tr>
						<td colspan="2"><strong>**Всички нови потребители ще бъдат активни по подразбиране.</strong></td></td>
					</tr>
				</form>
			</table>
		</td>
	</tr>
</table>
<?php
}

//=======================================================za bazata danni=====================================================//

if($mode == "dbase") //za bazata danni
//if(isset($action) && $mode == 'dbase')
{
//	echo e_QUERY;
	$action = (isset($_GET['action'])) ? $_GET['action'] : '';
	if($action == "backup")
	{
		backup_core();
		error_display("ГОТОВО", "Базата Данни е архивирана Успешно!!!");
		echo '<img src="'.e_THEME.'Images/ajax-loader.gif" />';
		include_once(FOOTERF);
		exit;
	}
	if($action == "optim")
	{
		$total_size = 0;
		$optimise_total_size = 0;
		$sql -> db_Select_gen("SHOW TABLE STATUS");
		$tables = array();
		while($row = $sql ->db_Fetch())
		{
			$table_size = ($row[ "Data_length" ] + $row[ "Index_length" ]) / 1024;	// return the size in Kilobytes
			$tables[$row['Name']] = sprintf("%.2f", $table_size);
			$total_size += round($table_size,2);	//get total size of all tables
			$optimise_sql = "OPTIMIZE TABLE {$row['Name']}";
			$optimise_result = mysql_query($optimise_sql);
		}
		$optimised_tables = array();
		$result	= mysql_query("SHOW TABLE STATUS");
		while($row = mysql_fetch_array($result))
		{
			$table_size = ($row[ "Data_length" ] + $row[ "Index_length" ]) / 1024;	// return the size in Kilobytes
			$optimised_tables[$row['Name']] = sprintf("%.2f", $table_size);
			$optimise_total_size += round($table_size,2);	//get total size of all tables after optimization
		}
		?>
		<br />
		<?php
		$text_opt = '
		<div align="center">
			<table border="1" width="50%" cellpadding="2" cellspacing="0" class="border2">
				<tr>
					<th>Таблица</th>
					<th>Размер (KB)</th>
					<th>Оптимизиран <br /> размер (KB)</th>
					<th>Оптимизирани</th>
				</tr>
				<tbody>';
				foreach($tables as $table => $size):
				$text_opt .= '
				<tr>
					<td>'.$table.'</td>
					<td>'.$size.'</td>
					<td>'.$optimised_tables[$table].'</td>
					<td>
					';
						if($size > $optimised_tables[$table]):
						$text_opt .= ($size - $optimised_tables[$table]);
						endif;
					$text_opt .= '
					</td>
				</tr>';
				endforeach;
				$text_opt .= '
				<tr>
					<td class="red"><b>Общо</b></td>
					<td class="red"><b>'.$total_size.'</b></td>
					<td class="red"><b>'.$optimise_total_size.'</b></td>
					<td class="red"><b>'.round($total_size - $optimise_total_size,2).'</b></td>
				</tr>
				</tbody>
			</table>
			<br />
		</div>
		'; 
		error_display("Оптимизацията - Успешна!", $text_opt, 640);
		
		
		echo '
			<br />
			<img src="'.e_THEME.'Images/ajax-loader.gif" />
			<br />
		';
	}
	else
	{
		echo "
			<div class='center'>
			<button type='submit' method='get' onclick=\"location='index.php?mode=dbase&action=backup';\">Бек-ъп</button>
			<button type='submit' method='get' onclick=\"location='index.php?mode=dbase&action=optim';\">Оптимизация</button>
			</div>
		";
	}
}
/*else
{
	echo '<div class="message">Данните са записани успешно!</div><br />';
	header( "refresh:2;url=".Script_Path."/admin/index.php?mode=users" );
}
*/
}
else
{
	error_display("ВНИМАНИЕ", "Нямате право да видате тази страница!!!");
}
include(FOOTERF);
?>