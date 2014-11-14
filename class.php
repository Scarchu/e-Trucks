<?php
define("CHARSET", "UTF-8");

$pageTitle = 'e-КАМИОНИ';

if(($pos = strpos(strtolower($_SERVER['PHP_SELF']), ".php/")) !== false) // redirect bad URLs to the correct one.
{
	$new_url = substr($_SERVER['PHP_SELF'], 0, $pos+4);
	$new_loc = ($_SERVER['QUERY_STRING']) ? $new_url."?".$_SERVER['QUERY_STRING'] : $new_url;
	header("Location: ".$new_loc);
	exit();
}
// If url contains a .php in it, PHP_SELF is set wrong (imho), affecting all paths.  We need to 'fix' it if it does.
$_SERVER['PHP_SELF'] = (($pos = strpos(strtolower($_SERVER['PHP_SELF']), ".php")) !== false ? substr($_SERVER['PHP_SELF'], 0, $pos+4) : $_SERVER['PHP_SELF']);
unset($pos);

define('eTR_INIT',  TRUE);
define('E107_DEBUG_LEVEL', false);

@include_once(realpath(dirname(__FILE__).'/etruck_config.php'));

if(!isset($ADMIN_DIRECTORY))
{
  header("Location: install.php");
  exit();
}

require_once(realpath(dirname(__FILE__).'/'.$HANDLERS_DIRECTORY).'/init_class.php');
$sctruck_paths = compact('ADMIN_DIRECTORY', 'UPLOADS_DIRECTORY', 'FORUM_DIRECTORY', 'THEMES_DIRECTORY', 'HANDLERS_DIRECTORY', 'PM_DIRECTORY');
$sc_trucks = new sc_trucks($sctruck_paths, realpath(dirname(__FILE__)));
$inArray = array("'", ";", "/**/", "/UNION/", "/SELECT/", "AS ");
if (strpos($_SERVER['PHP_SELF'], "trackback") === false)
{
	foreach($inArray as $res)
	{
		if(stristr($_SERVER['QUERY_STRING'], $res))
		{
			die("Access denied.");
		}
	}
}
unset($inArray);

include_once(realpath(dirname(__FILE__).'/etruck_config.php'));

//=========================LOAD PARSER==============================//

/**
 * G: Retrieve Query data from URI
 * (Until this point, we have no idea what the user wants to do)
 */

if (preg_match("#\[(.*?)](.*)#", $_SERVER['QUERY_STRING'], $matches)) {
	define("e_MENU", $matches[1]);
	$e_QUERY = $matches[2];
	unset($matches);
}
else
{
	define("e_MENU", "");
	$e_QUERY = $_SERVER['QUERY_STRING'];
}

//
// Start the parser; use it to grab the full query string
//

require_once(e_HANDLER.'parse_class.php');
$tp = new e_parse;

$e_QUERY = str_replace(array('{', '}', '%7B', '%7b', '%7D', '%7d'), '', rawurldecode($e_QUERY));
$e_QUERY = str_replace('&', '&amp;', $tp->post_toForm($e_QUERY));

/**
 * e_QUERY notes:
 * It seems _GET / _POST / _COOKIE are doing pre-urldecode on their data.
 * There is no official documentation/php.ini setting to confirm this.
 * We could add rawurlencode() after the replacement above if problems are reported.
 *
 * @var string
 */
define('e_QUERY', $e_QUERY);

//$e_QUERY = e_QUERY;
//echo e_QUERY;
define("e_TBQS", $_SERVER['QUERY_STRING']);
$_SERVER['QUERY_STRING'] = e_QUERY;

//=================================================================//

//---------------MySQL------------------//
$eTimingStart = microtime();

@require_once(e_HANDLER.'traffic_class.php');
$eTraffic=new e107_traffic; // We start traffic counting ASAP
$eTraffic->Calibrate($eTraffic);

define("MPREFIX", $mySQLprefix);

require_once(e_HANDLER."mysql_class.php");

$sql = new db;
$sql2 = new db;

$sql->db_SetErrorReporting(FALSE);

$sql->db_Mark_Time('Start: SQL Connect');
$merror=$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
$sql->db_Mark_Time('Start: Prefs, misc tables');

//--------------------------------------//
//-----------------------------------------NASTROIKI (PREFS)----------------------------------------------------//

require_once(e_HANDLER."pref_class.php");
$sysprefs = new prefs;

require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();

$retrieve_prefs[] = 'SitePrefs';
$sysprefs->ExtractPrefs($retrieve_prefs, TRUE);
$PrefData = $sysprefs->get('SitePrefs');
$pref = $eArrayStorage->ReadArray($PrefData);
if(!$pref){
	//$admin_log->log_event("CORE_LAN8", "CORE_LAN7", E_LOG_WARNING); // Core prefs error, core is attempting to
	// Try for the automatic backup..
	$PrefData = $sysprefs->get('SitePrefs_Backup');
	$pref = $eArrayStorage->ReadArray($PrefData);
	if(!$pref){
		// No auto backup, try for the 'old' prefs system.
		$PrefData = $sysprefs->get('pref');
		
		$pref = unserialize($PrefData);
		if(!is_array($pref)){
			message_handler("CRITICAL_ERROR", 3, __LINE__, __FILE__);
			//echo "seriozen problem s Bazata Danni!";
			// No old system, so point in the direction of resetcore :(
			message_handler("CRITICAL_ERROR", 4, __LINE__, __FILE__);
		//	$admin_log->log_event("CORE_LAN8", "CORE_LAN9", E_LOG_FATAL); // Core could not restore from automatic backup. Execution halted.
			exit;
		} else {
		
			// old prefs found, remove old system, and update core with new system
			$PrefOutput = $eArrayStorage->WriteArray($pref);
			if(!$sql->db_Update('core', "value='{$PrefOutput}' WHERE name='SitePrefs'")){
				$sql->db_Insert('core', "'SitePrefs', '{$PrefOutput}'");
			}
			if(!$sql->db_Update('core', "value='{$PrefOutput}' WHERE name='SitePrefs_Backup'")){
				$sql->db_Insert('core', "'SitePrefs_Backup', '{$PrefOutput}'");
			}
			$sql->db_Delete('core', "`name` = 'pref'");
		}
	} else {
		message_handler("CRITICAL_ERROR", 3, __LINE__, __FILE__);
		// auto backup found, use backup to restore the core
		if(!$sql->db_Update('core', "`value` = '".addslashes($PrefData)."' WHERE `name` = 'SitePrefs'")){
			$sql->db_Insert('core', "'SitePrefs', '".addslashes($PrefData)."'");
		}
	}
}

// extract menu prefs
//$menu_pref = unserialize(stripslashes($sysprefs->get('menu_pref')));

$months = array('Януари', 'Февруари', 'Март', 'Април', 'Май', 'Юни', 'Юли', 'Август', 'Септември', 'Октомври', 'Ноември', 'Декември');
$short_months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
//--------------------------------------KRAI NASTROIKI (PREFS)--------------------------------------------------//

//-----------------------------------------USERS & SESSIONS-----------------------------------------------------//
define("SITEURLBASE", "http://".$_SERVER['HTTP_HOST']);
define("SITEURL", SITEURLBASE.e_HTTP);

if(!defined('e_SELF')) // user override option 
{
	$pref['ssl_enabled'] = '';
	define("e_SELF", ($pref['ssl_enabled'] == '1' ? "https://".$_SERVER['HTTP_HOST'] : "http://".$_SERVER['HTTP_HOST']) . ($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_FILENAME']));	
}

//$user_registration = 0;  // set 0 or 1

define("COOKIE_TIME_OUT", 10); //specify cookie timeout in days (default is 10 days)
define('SALT_LENGTH', 9); // salt for password

/* Specify user levels */
define ("ADMIN_LEVEL", 5);
define ("BOSS_LEVEL", 4);
define ("USER_LEVEL", 1);
define ("GUEST_LEVEL", 0);

	session_start();
	//global $sql;

	/* Secure against Session Hijacking by checking user agent */
	if (isset($_SESSION['HTTP_USER_AGENT']))
	{
		if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT']))
		{
			logout();
			exit;
		}
	}
	
	$tmp = explode(e_HTTP, $_SERVER["REQUEST_URI"]);
	$requested_page = $tmp[1];
	unset ($tmp);

	// before we allow sessions, we need to check authentication key - ckey and ctime stored in database
	/* If session not set, check for cookies set by Remember me */
	if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_name']) ) 
	{
		if(isset($_COOKIE['userid']) && isset($_COOKIE['userkey']))
		{
			/* we double check cookie expiry time against stored in database */
			$cookie_user_id  = filter($_COOKIE['userid']);
			$sql -> db_Select("users", "*", "WHERE userid ='$cookie_user_id'", "no-where");
			$result = $sql -> db_Fetch();
			$ctime = $result['ctime'];
			$ckey = $result['ckey'];
			// coookie expiry
			if( (time() - $ctime) > 60*60*24*COOKIE_TIME_OUT)
			{
				logout();
			}
			/* Security check with untrusted cookies - dont trust value stored in cookie. 		
			/* We also do authentication check of the `ckey` stored in cookie matches that stored in database during login*/
			
			if( !empty($ckey) && is_numeric($_COOKIE['userid']) && isUserID($_COOKIE['username']) && $_COOKIE['userkey'] == sha1($ckey)  )
			{
				session_regenerate_id(); //against session fixation attacks.
				$_SESSION['user_id'] = $_COOKIE['userid'];
				$_SESSION['user_name'] = $_COOKIE['username'];
				/* query user level from database instead of storing in cookies */	
				$sql -> db_Select("users", "*", "userid='$_SESSION[user_id]'");
				$user_level = $sql -> db_Fetch();
				$_SESSION['user_level'] = $user_level;
				$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
		    }
			else
			{
				logout();
			}
		}
		elseif($requested_page !== "login.php")
		{
			header("Location:" .e_BASE."login.php");
			exit();
		}
	}

//-----------------------------------END OF USERS---------------------------------------------------//

define("HEADERF", e_THEME."header.php");
define("FOOTERF", e_THEME."footer.php");

//require_once(e_HANDLER.'functions.php');
require_once(e_HANDLER.'java_includes.php');
//require_once(e_HANDLER.'pagination_class.php');
//require_once(e_HANDLER.'truck_class.php');
//require_once(e_HANDLER.'userprofile_class.php');
require_once(e_HANDLER.'emote_filter.php');

echo'
<script type="text/javascript">
tinymce.init({
	selector: "textarea.useTM",
		theme : "modern",
		language: "bg_BG",
		plugins: "media emoticons preview textcolor image paste directionality",
		entity_encoding: "raw",
		toolbar: "undo redo | styleselect | bold italic | link image | media preview | emoticons | textcolor | link"
	 
 });
</script>
';

$modal_var = '<script>
				$(function() {
					$( "#dialog-message" ).dialog({
						modal: true,
						width: 800,
						height: 640,
						autoOpen: true,
						buttons: {
							Ok: function() {
								$( this ).dialog( "close" );
								window.location = "'.e_BASE.'logout.php";
							}
						}
					});
				});
			</script>
			</head>
			<body>
				<div id="dialog-message" title="Сайтът е затворен!" style="text-align:center; background:url('.e_THEME.'Images/closed_background.jpg);">
					<p>'.$tp -> toHTML(html_entity_decode($pref['maintenance_text'])).'</p>
				</div>
			</body>
';
//--------------------------------------//

/******************************************************
*
*	checkup if the user IS admin 
*	and to which company is, if not Admin
*	setting up some constants
*
/******************************************************/

if(isset($_SESSION['user_id']))
{
	$sql->db_Select("users", "*", "userid=".$_SESSION['user_id']."");
	$result = $sql->db_Fetch();
	
	if($result['user_level'] == 5)
	{
		define("ADMIN", TRUE);
		define("USER", TRUE); 
	}
	else
	{
		define("ADMIN", FALSE);
		define("USER", TRUE); 
	}
	
	define("USERID", $result['userid']);
	define("USERNM", $result['username']);
	define("USERNAME", $result['username']);
	define("USERLV", $result['user_level']);
	define("USERFN", ($result['first_name']." ".$result['last_name']));
	define("USERAV", $result['avatar']);
	define("USERCO", $result['company']);
	//define("av_PATH", e_BASE."uploads/avatars/");
	//define("ta_PATH", e_BASE."uploads/tacho_files/");
	$sql->db_Select("companies", "*", "id=".$result['company']."");
	$result2 = $sql->db_Fetch();
	define("USERCO_name", $result2['name']);
	$user_trucks_array = unserialize($result2['trucks']);

	if(($result['user_level'] < 4) or (count($user_trucks_array) == 1))
	{
		define("DRIVERTR", $result['trucks']);
	}
	if(empty($user_trucks_array) and USERLV != ADMIN_LEVEL)
	{
		$user_trucks_array = array();
		error_display("ГРЕШКА", "Не е дефиниран камион за потребителя / фирмата!!!");
	}
}


/*****************************/
/*							 */
/*		FUNCTIONS			 */
/*							 */
/*****************************/

function varset(&$val,$default='')
{
	if (isset($val))
	{
		return $val;
	}
	return $default;
}

function defset($str,$default='')
{
	if (defined($str))
	{
		return constant($str);
	}
	return $default;
}

function save_prefs($table = 'core', $uid = USERID, $row_val = '')
{
  global $pref, $user_pref, $tp, $PrefCache, $sql, $eArrayStorage;
  if ($table == 'core')
  {
	if ($row_val == '')
	{		// Save old version as a backup first
	  $sql->db_Select_gen("REPLACE INTO `#core` (name,value) values ('SitePrefs_Backup', '".addslashes($PrefCache)."') ");

	  // Now save the updated values
	  // traverse the pref array, with toDB on everything
	  $_pref = $tp -> toDB($pref, true, true, 'pReFs');
	  // Create the data to be stored
	  $sql->db_Select_gen("REPLACE INTO `#core` (name,value) values ('SitePrefs', '".$eArrayStorage->WriteArray($_pref)."') ");
	  //ecache::clear('SitePrefs');
	}
  }
  else
  {
	$_user_pref = $tp -> toDB($user_pref);
	$tmp=addslashes(serialize($_user_pref));
	$sql->db_Update("user", "user_prefs='$tmp' WHERE user_id=".intval($uid));
	return $tmp;
  }
}



function message_handler($mode, $message, $line = 0, $file = "")
{
	require_once(e_HANDLER."message_handler.php");
	show_emessage($mode, $message, $line, $file);
}

require_once(e_HANDLER.'override_class.php');
$override = new override;

if (!class_exists('e107table'))
{
	class eTRtable
	{
		function tablerender($caption, $text, $mode = "default", $return = false) {
			/*
			# Render style table
			# - parameter #1:                string $caption, caption text
			# - parameter #2:                string $text, body text
			# - return                                null
			# - scope                                        public
			*/
			global $override;

			if ($override_tablerender = $override->override_check('tablerender')) {
				$result=call_user_func($override_tablerender, $caption, $text, $mode, $return);

				if ($result == "return") {
					return;
				}
				extract($result);
			}

			if ($return) {
				ob_start();
				tablestyle($caption, $text, $mode);
				$ret=ob_get_contents();
				ob_end_clean();
				return $ret;
			} else {
				tablestyle($caption, $text, $mode);
			}
		}
	}
}

$ns = new eTRtable;

function tablestyle($caption, $text)
{
	echo "<div style='width:100%;vertical-align:top'>
	<table style='width:100%' cellpadding='0' cellspacing='0'>
	<tr >
	<td>
	<div class=\"caption2\" style='color:black;padding:3px'>
	$caption
	</div>
	<div class=\"forumheader3\" style='padding:6px'>{$text}</div>
	</td></tr></table></div>";
}

function filter($data)
{
	$data = trim(htmlentities(strip_tags($data)));
	if (get_magic_quotes_gpc())
	$data = stripslashes($data);
	$data = mysql_real_escape_string($data);
	return $data;
}

/*function ChopStr($str, $len) 
{
    if (strlen($str) < $len)
	return $str;
    $str = substr($str,0,$len);
    if ($spc_pos = strrpos($str," "))
    $str = substr($str,0,$spc_pos);
    return $str . "...";
}*/	

/*function isEmail($email)
{
	return preg_match('/^\S+@[\w\d.-]{2,}\.[\w]{2,6}$/iU', $email) ? TRUE : FALSE;
}*/

function isUserID($username)
{
	return preg_match('/^[a-z\d_]{5,20}$/i', $username) ? true : false;
}
 
/*function isURL($url) 
{
	return preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $url) ? true : false;
}*/

function checkPwd($x,$y) 
{
	if(empty($x) || empty($y) ) { return false; }
	if (strlen($x) < 4 || strlen($y) < 4) { return false; }
	if (strcmp($x,$y) != 0)
	{
		return false;
	}
	return true;
}

function GenPwd($length = 7)
{
	$password = "";
	$possible = "0123456789bcdfghjkmnpqrstvwxyz"; //no vowels
	$i = 0; 
    while ($i < $length)
	{
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);    
		if (!strstr($password, $char))
		{
			$password .= $char;
			$i++;
		}
	}
	return $password;
}

function GenKey($length = 7)
{
	$password = "";
	$possible = "0123456789abcdefghijkmnopqrstuvwxyz"; 
    $i = 0; 
    while ($i < $length)
	{
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        if (!strstr($password, $char))
		{
			$password .= $char;
			$i++;
		}
	}
	return $password;
}

function logout()
{
	global $sql;
	session_start();
	$sess_user_id = strip_tags(mysql_real_escape_string($_SESSION['user_id']));
	$cook_user_id = strip_tags(mysql_real_escape_string($_COOKIE['userid']));
	if(isset($sess_user_id) || isset($cook_user_id))
	{
		$sql -> db_Update("users", "ckey='', ctime='' WHERE userid='$sess_user_id' OR userid='$cook_user_id'");
	}		
	
	/************ Delete the sessions****************/
	unset($_SESSION['user_id']);
	unset($_SESSION['user_name']);
	unset($_SESSION['user_level']);
	unset($_SESSION['HTTP_USER_AGENT']);
	session_unset();
	session_destroy(); 
	
	/* Delete the cookies*******************/
	setcookie("userid", '', time()-60*60*24*COOKIE_TIME_OUT, "/");
	setcookie("username", '', time()-60*60*24*COOKIE_TIME_OUT, "/");
	setcookie("userkey", '', time()-60*60*24*COOKIE_TIME_OUT, "/");
	setcookie("userlevel", '', time()-60*60*24*COOKIE_TIME_OUT, "/");
	
	header("Location: ".e_BASE."login.php");
}

// Password and salt generation
function PwdHash($pwd, $salt = null)
{
    if ($salt === null)
	{
        $salt = substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
    }
    else
	{
        $salt = substr($salt, 0, SALT_LENGTH);
    }
    return $salt . sha1($pwd . $salt);
}

// -----------------------------------------------------------------------------

function truck_select($truck)
{
	global $Truck, $broi_trucks, $pref, $pageTitle;
	require_once(e_HANDLER.'truck_class.php');
	
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
			include e_HANDLER."form_fuel.php";
		}
		elseif(($pref['modules']['AdBlue'][$pr] == 1) and ($pref['modules']['frigo'][$pr] == 0))
		{
			$mod = "adblue";
			include e_HANDLER."form_fuel.php";
		}
		elseif(($pref['modules']['AdBlue'][$pr] == 1) and ($pref['modules']['frigo'][$pr] == 1))
		{
			$mod = "frigo_adblue";
			include e_HANDLER."form_fuel.php";
		}
		else
		{
			$mod = "fuel";
			include e_HANDLER."form_fuel.php";
		}
		$off3 = '';
	}
	else { $off3 = 'disabled'; }
	if($pref['modules']['tovar'][$pr] == 1) { include e_HANDLER."form2.php"; $off4 = '';} else { $off4 = 'disabled'; }
	
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