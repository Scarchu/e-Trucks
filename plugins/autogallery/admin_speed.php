<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A very simple image gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        18/08/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
require_once(dirname(__FILE__)."/admin_functions.php");

/*require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");
*/
$speedSettingRecs = array
(
	'autogal_enabledbcache'     => array('imp' => 1, 'rec' => 1, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_CACHE_7, 'desc' => str_replace('[TABLENAME]', AUTOGAL_DIRCACHETABLE, AUTOGAL_LANG_ADMIN_CACHE_8)),
	'autogal_shownewestinroot'  => array('imp' => 1, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_MAIN_L37, 'desc' => AUTOGAL_LANG_ADMIN_MAIN_L38),
	'autogal_xmlsearch'         => array('imp' => 1, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_METADATA_L11, 'desc' => AUTOGAL_LANG_ADMIN_METADATA_L12),
	'autogal_enablegaldispord'  => array('imp' => 1, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_MAIN_L94, 'desc' => AUTOGAL_LANG_ADMIN_MAIN_L95.' '.AUTOGAL_LANG_ADMIN_MAIN_L98),
	'autogal_defaultdisporder'  => array('imp' => 1, 'rec' => array('nameasc', 'namedsc'), 'type' => 'list', listvals => array('nameasc' => AUTOGAL_LANG_L62, 'namedsc' => AUTOGAL_LANG_L63, 'datedsc' => AUTOGAL_LANG_L64, 'dateasc' => AUTOGAL_LANG_L65), 'title' => AUTOGAL_LANG_ADMIN_MAIN_L96, 'desc' => AUTOGAL_LANG_ADMIN_MAIN_L97.' '.AUTOGAL_LANG_ADMIN_MAIN_L99),
	'autogal_showdateorddate'   => array('imp' => 1, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_APPEARENCE_L66, 'desc' => AUTOGAL_LANG_ADMIN_APPEARENCE_L67.' '.AUTOGAL_LANG_ADMIN_APPEARENCE_L68),
	'autogal_showdateordname'   => array('imp' => 1, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_APPEARENCE_L64, 'desc' => AUTOGAL_LANG_ADMIN_APPEARENCE_L65.' '.AUTOGAL_LANG_ADMIN_APPEARENCE_L68),
	
	'autogal_randomdefaultimg'  => array('imp' => 2, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_MAIN_L50, 'desc' => AUTOGAL_LANG_ADMIN_MAIN_L51),
	'autogal_metaviewhits'      => array('imp' => 2, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_METADATA_L7, 'desc' => AUTOGAL_LANG_ADMIN_METADATA_L8),
	'autogal_wmarkauto'         => array('imp' => 2, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_WATERMARK_17, 'desc' => AUTOGAL_LANG_ADMIN_WATERMARK_18),
	'autogal_resizepreviewimgs' => array('imp' => 2, 'rec' => 1, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_MAIN_L81, 'desc' => str_replace("[WIDTH]", $pref['autogal_maximagewidth'], str_replace("[HEIGHT]", $pref['autogal_maximageheight'], str_replace("[PREFIX]", AUTOGAL_PREVIEWIMGPREFIX, AUTOGAL_LANG_ADMIN_MAIN_L82)))),
	'autogal_autosizegalthumbs' => array('imp' => 2, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_THUMBS_L61, 'desc' => AUTOGAL_LANG_ADMIN_THUMBS_L62),

	'autogal_checksubgalvclass' => array('imp' => 2, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_USERACCESS_6, 'desc' => AUTOGAL_LANG_ADMIN_USERACCESS_7),
	'autogal_checklatestvclass' => array('imp' => 2, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_USERACCESS_8, 'desc' => AUTOGAL_LANG_ADMIN_USERACCESS_9),
	'autogal_checksearchvclass' => array('imp' => 2, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_USERACCESS_10, 'desc' => AUTOGAL_LANG_ADMIN_USERACCESS_10),
	'autogal_checkuploadvclass' => array('imp' => 2, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_USERACCESS_14, 'desc' => AUTOGAL_LANG_ADMIN_USERACCESS_15),
		
	'autogal_authcachelatest'   => array('imp' => 2, 'rec' => 1, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_CACHE_26, 'desc' => AUTOGAL_LANG_ADMIN_CACHE_27),
	'autogal_authcachesearch'   => array('imp' => 2, 'rec' => 1, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_CACHE_24, 'desc' => AUTOGAL_LANG_ADMIN_CACHE_25),

	'autogal_checklcommsvclass' => array('imp' => 3, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_USERACCESS_11, 'desc' => AUTOGAL_LANG_ADMIN_USERACCESS_12),
	'autogal_showreviewcount'   => array('imp' => 3, 'rec' => 0, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_APPEARENCE_L62, 'desc' => AUTOGAL_LANG_ADMIN_APPEARENCE_L63),
	'autogal_usethumbnailcache' => array('imp' => 3, 'rec' => 1, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_CACHE_28, 'desc' => AUTOGAL_LANG_ADMIN_CACHE_29),
	'autogal_nofilevalidation'  => array('imp' => 3, 'rec' => 1, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_CACHE_33, 'desc' => AUTOGAL_LANG_ADMIN_CACHE_34),
	'autogal_usequickgaldetect' => array('imp' => 3, 'rec' => 1, 'type' => 'check', 'title' => AUTOGAL_LANG_ADMIN_CACHE_31, 'desc' => AUTOGAL_LANG_ADMIN_CACHE_32),
);

unset($message);

###################
# SET PREFERENCES #
###################
if(isset($_POST['updatesettings']))
{
    $pref['autogal_enabledbcache']     = $_POST['autogal_enabledbcache'];
	$pref['autogal_authcachelatest']   = $_POST['autogal_authcachelatest'];
	$pref['autogal_authcachesearch']   = $_POST['autogal_authcachesearch'];
	$pref['autogal_usethumbnailcache'] = $_POST['autogal_usethumbnailcache'];
	$pref['autogal_usequickgaldetect'] = $_POST['autogal_usequickgaldetect'];
	$pref['autogal_nofilevalidation']  = $_POST['autogal_nofilevalidation'];
	
	#AutoGal_Dump($_POST);
	
	foreach ($speedSettingRecs as $setting => $bits)
	{
		if (isset($_POST[$setting."_isrec"]))
		{
			$pref[$setting] = $_POST[$setting.'_rec'];
			#print "$setting = ".$_POST[$setting.'_rec']."<br />";
		}
	}
	   	
    save_prefs();
	$message = AUTOGAL_LANG_ADMIN_MAIN_L48;

	if ($pref['autogal_enabledbcache'])
	{
		if (!AutoGal_DBTableExists('cache')) 
		{
			$message .= "<br />".AutoGal_CreateDBTable('cache', 0);
		}
		else
		{
			if ($_POST['autogal_clearcache'])
			{
				AutoGal_ClearCacheMenu('', 1);
			}
		}
	}
	else
	{
		if (AutoGal_DBTableExists('cache'))  $message .= "<br />".AutoGal_DropDBTable('cache');
	}	
	
}

if ($message)
{
    $ns -> tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}

$cacheDis = (!$pref['autogal_enabledbcache'] ? " disabled='disabled'" : '');

if ($pref['autogal_enabledbcache'])
{
	unset($speedSettingRecs['autogal_enablegaldispord']);
	unset($speedSettingRecs['autogal_defaultdisporder']);
	unset($speedSettingRecs['autogal_showdateorddate']);
	unset($speedSettingRecs['autogal_showdateordname']);
}
else
{
	unset($speedSettingRecs['autogal_authcachelatest']);
	unset($speedSettingRecs['autogal_authcachesearch']);
	unset($speedSettingRecs['autogal_usethumbnailcache']);
}

if ($pref['autogal_authcachesearch'])
{
	unset($speedSettingRecs['autogal_xmlsearch']);
}

if ($pref['autogal_enabledbcache'])
{
	AutoGal_CheckTableDefs('cache', 0);
}

################
# INPUT FIELDS #
################
$text = "
<script type='text/javascript'>
function enableCacheSettings()
{
	var cacheOn;
	cacheOn = document.getElementById('autogal_enabledbcache').checked;
	document.getElementById('autogal_authcachesearch').disabled = !cacheOn;
	document.getElementById('autogal_authcachelatest').disabled = !cacheOn;
	document.getElementById('autogal_usethumbnailcache').disabled = !cacheOn;
	document.getElementById('autogal_clearcache').disabled = !cacheOn;
}

function checkRecs(obj)
{
	var recObj;
	var name;
	
	if (obj.name.match('_rec$'))
	{
		name = obj.name.substring(0, obj.name.length - 4);
		sister = document.getElementById(name);
	}
	else
	{
		sister = document.getElementById(obj.id + '_rec');
	}
	
	if (!sister) return;
	
	if (sister.type == 'checkbox')
	{
		sister.checked = obj.checked;
	}
}
</script>
<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_CACHE_6."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_CACHE_7."</b><br /><span class='smalltext'>".str_replace('[TABLENAME]', AUTOGAL_DIRCACHETABLE, AUTOGAL_LANG_ADMIN_CACHE_8)."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_enabledbcache' name='autogal_enabledbcache'".($pref['autogal_enabledbcache'] ? " checked" : "")." onclick='javascript:enableCacheSettings();checkRecs(this)'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_CACHE_24."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_CACHE_25."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_authcachesearch' name='autogal_authcachesearch'".($pref['autogal_authcachesearch'] ? " checked" : "")." onclick='javascript:checkRecs(this)'$cacheDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_CACHE_26."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_CACHE_27."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_authcachelatest' name='autogal_authcachelatest'".($pref['autogal_authcachelatest'] ? " checked" : "")." onclick='javascript:checkRecs(this)'$cacheDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_CACHE_28."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_CACHE_29."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_usethumbnailcache' name='autogal_usethumbnailcache'".($pref['autogal_usethumbnailcache'] ? " checked" : "")." onclick='javascript:checkRecs(this)'$cacheDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_CACHE_16."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_CACHE_17."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_clearcache' name='autogal_clearcache'$cacheDis></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_CACHE_30."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_CACHE_31."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_CACHE_32."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_usequickgaldetect' name='autogal_usequickgaldetect'".($pref['autogal_usequickgaldetect'] ? " checked" : "")." onclick='javascript:checkRecs(this)'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_CACHE_33."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_CACHE_34."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_nofilevalidation' name='autogal_nofilevalidation'".($pref['autogal_nofilevalidation'] ? " checked" : "")." onclick='javascript:checkRecs(this)'></td>
</tr>
</table>
<br />
".AutoGal_GetSpeedRecs()."
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updatesettings' value='".AUTOGAL_LANG_ADMIN_MAIN_L45."' />
    </td>
</tr>
</table>
</form>
<br />";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_CACHE_5, $text);

require_once(FOOTERF);
exit;

function AutoGal_GetSpeedRecs()
{
	global $pref;
	global $speedSettingRecs;
	
	foreach ($speedSettingRecs as $setting => $bits)
	{
		$existVal = $pref[$setting];
		$action = '';
			
		if ($bits['type'] == 'check')
		{
			if ((!$pref[$setting])&&($bits['rec']))
			{
				#print "$setting ".AUTOGAL_LANG_ADMIN_CACHE_11."<br />";
				$action = AUTOGAL_LANG_ADMIN_CACHE_11;
			}
			elseif (($pref[$setting])&&(!$bits['rec']))
			{
				#print "$setting ".AUTOGAL_LANG_ADMIN_CACHE_10."<br />";
				$action = AUTOGAL_LANG_ADMIN_CACHE_10;
			}
			
			$formEle = "<input type='checkbox' id='${setting}_rec' name='${setting}_rec'".($pref[$setting] ? " checked" : "")." onclick='javascript:checkRecs(this)'>";
		}
		else if ($bits['type'] == 'list')
		{
			if (!in_array($existVal, $bits['rec']))
			{
				$action = AUTOGAL_LANG_ADMIN_CACHE_18;
				foreach ($bits['rec'] as $val)
				{
					$vals[] = "'".$bits['listvals'][$val]."'";
				}
				$action .= implode(AUTOGAL_LANG_ADMIN_CACHE_19, $vals);
			}
			
			$formEle = "<select id='${setting}_rec' name='${setting}_rec' class='tbox'>";
			foreach ($bits['listvals'] as $val => $title)
			{
				$formEle .= "<option value='$val'".($existVal == $val ? " selected='selected'" : '').">$title</option>\n";
			}
			$formEle .= "</select>";
		}
		
		if ($bits['imp'] == 1){
			$impact = "<font color='red'><b>".AUTOGAL_LANG_ADMIN_CACHE_21."</b></font>";
		}elseif ($bits['imp'] == 3){
			$impact = "<font color='gold'><b>".AUTOGAL_LANG_ADMIN_CACHE_23."</b></font>";
		}else{
			$impact = "<font color='orange'><b>".AUTOGAL_LANG_ADMIN_CACHE_22."</b></font>";
		}
		
		if ($action)
		{
			$text .= "
			<tr>
				<td class='forumheader3' style='text-align:center'>$impact</td>
				<td class='forumheader3' style='text-align:center'>$action</td>
				<td class='forumheader3'><b>".$bits['title']."</b><br /><span class='smalltext'>".$bits['desc']."</span></td>
				<td class='forumheader3' style='text-align:center'>
					$formEle
					<input type='hidden' id='${setting}_isrec'  name='${setting}_isrec' value='1'>
				</td>
			</tr>";
		}
	}
	
	if ($text)
	{
		$text = "
		<table style='width:97%' class='fborder'>
		<tr style='vertical-align:top'>
			<td colspan='4' style='text-align:center' class='forumheader'>
				<b>".AUTOGAL_LANG_ADMIN_CACHE_9."</b>
			</td>
		</tr>
		<tr style='vertical-align:top'>
			<td colspan='4' style='text-align:center' class='forumheader3'><span class='smalltext'>".AUTOGAL_LANG_ADMIN_CACHE_37."</span></td>
		</tr>
		<tr>
			<th class='forumheader2'><b>".AUTOGAL_LANG_ADMIN_CACHE_20."</b></th>
			<th class='forumheader2'><b>".AUTOGAL_LANG_ADMIN_CACHE_12."</b></th>
			<th class='forumheader2'><b>".AUTOGAL_LANG_ADMIN_CACHE_13."</b></th>
			<th class='forumheader2'><b>".AUTOGAL_LANG_ADMIN_CACHE_14."</b></th>
		</tr>
		$text
		</table>";
	}
	else
	{
		$text = "<table style='width:97%' class='fborder'><tr><td class='forumheader3' style='text-align:center'>".AUTOGAL_LANG_ADMIN_CACHE_15."</td></tr></table>";
	}
	
	return $text;
}

?>

