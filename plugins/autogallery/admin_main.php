<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        25/08/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
require_once(dirname(__FILE__)."/language.php");
require(dirname(__FILE__)."/def.php");		
require_once(dirname(__FILE__)."/admin_functions.php");

/*require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");
*/
$currUsingHttps = (defined('AUTOGAL_USINGHTTPS') ? AUTOGAL_USINGHTTPS : "");
if (!$currUsingHttps) $currUsingHttps = "detect";

###################
# SET PREFERENCES #
###################
if ((isset($_POST['updatesettings']))||(isset($_POST['resetthumbs'])))
{
    $pref['autogal_title']             = $_POST['autogal_title'];
    $pref['autogal_rootname']          = $_POST['autogal_rootname'];
    $pref['autogal_uploadmaxsize']     = $_POST['autogal_uploadmaxsize'];
    $pref['autogal_emailtofriend']     = $_POST['autogal_emailtofriend'];
    $pref['autogal_defaultetfcom']     = $_POST['autogal_defaultetfcom'];
    $pref['autogal_gallerydir']        = $_POST['autogal_gallerydir'];
	$pref['autogal_uploadnumber']      = $_POST['autogal_uploadnumber'];
	$pref['autogal_chmodwarnoff']      = $_POST['autogal_chmodwarnoff'];
	$pref['autogal_shownewest']        = $_POST['autogal_shownewest'];
	$pref['autogal_shownewestinroot']  = $_POST['autogal_shownewestinroot'];
	$pref['autogal_showinnewwindow']   = $_POST['autogal_showinnewwindow'];
	$pref['autogal_openlargewindow']   = $_POST['autogal_openlargewindow'];
	$pref['autogal_newwindowargs']     = $_POST['autogal_newwindowargs'];
	$pref['autogal_enablesearch']      = $_POST['autogal_enablesearch'];
	$pref['autogal_largeimgnewwindow'] = $_POST['autogal_largeimgnewwindow'];
	$pref['autogal_sortdatectime']     = $_POST['autogal_sortdatectime'];
	$pref['autogal_uploadexts']        = $_POST['autogal_uploadexts'];
	$pref['autogal_resizepreviewimgs'] = $_POST['autogal_resizepreviewimgs'];
	$pref['autogal_shownwinwidth']     = $_POST['autogal_shownwinwidth'];
	$pref['autogal_shownwinheight']    = $_POST['autogal_shownwinheight'];
	$pref['autogal_shownwintoobar']    = $_POST['autogal_shownwintoobar'];
	$pref['autogal_shownwinlocbar']    = $_POST['autogal_shownwinlocbar'];
	$pref['autogal_shownwindirect']    = $_POST['autogal_shownwindirect'];
	$pref['autogal_shownwinstsbar']    = $_POST['autogal_shownwinstsbar'];
	$pref['autogal_shownwinmnubar']    = $_POST['autogal_shownwinmnubar'];
	$pref['autogal_shownwinscrbar']    = $_POST['autogal_shownwinscrbar'];
	$pref['autogal_shownwincphist']    = $_POST['autogal_shownwincphist'];
	$pref['autogal_shownwinresize']    = $_POST['autogal_shownwinresize'];
	$pref['autogal_shownwinexargs']    = $_POST['autogal_shownwinexargs'];
	$pref['autogal_enablesearche107']  = $_POST['autogal_enablesearche107'];
	$pref['autogal_enablegaldispord']  = $_POST['autogal_enablegaldispord'];
	$pref['autogal_defaultdisporder']  = $_POST['autogal_defaultdisporder'];
	
	save_prefs();
	
    $message = AUTOGAL_LANG_ADMIN_MAIN_L48;
	
	$cusHttpPath = $_POST['autogal_customhttppath'];
	$cusAbsPath = $_POST['autogal_customabspath'];
	
	$usingHttps = $_POST['autogal_usinghttps'];
	if (!$usingHttps) $usingHttps = "detect";
		
	$cusAbsPath = preg_replace('/[\\/\\\\]$/', "", $cusAbsPath);
	$cusHttpPath = preg_replace('/[\\/\\\\]$/', "", $cusHttpPath);
	if ((!preg_match('/^\//', $cusHttpPath))&&($cusHttpPath)) $cusHttpPath = "/$cusHttpPath";
	
	if (($cusHttpPath != AUTOGAL_CUSTOMHTTPPATH)||($cusAbsPath != AUTOGAL_CUSTOMABSPATH)||($usingHttps != $currUsingHttps))
	{
		$baseFile = dirname(__FILE__)."/def_basedirs.php";
		if (!AutoGal_WriteCustomBasePaths($cusHttpPath, $cusAbsPath, $usingHttps, $data))
		{
			$msg = "<b>".str_replace("[FILE]", $baseFile, AUTOGAL_LANG_ADMIN_MAIN_L70)."</b><br />
			<br />".str_replace("[FILE]", $baseFile, AUTOGAL_LANG_ADMIN_MAIN_L71)."<br />
			<br /><pre>".htmlspecialchars($data)."</pre><br />";
		}
		else
		{
			$self = ($_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : AUTOGAL_CONFIG);
			$self = preg_replace("/\?.*$/", '', $self);
			header("location:$self?cpthup=1");
			exit;
		}
		
		$ns->tablerender("", $msg);
	}
}

$e107SearchFile = AUTOGAL_BASEABS.'/e_search.php';
$e107NoSearchFile = AUTOGAL_BASEABS.'/no_e_search.php';
$enSearch = ($pref['autogal_enablesearche107'] ? 1 : 0);
$e107Search = (file_exists($e107SearchFile) ? 1 : 0);

#print "SEARCH: [$enSearch] E107 SEARCH: [$e107Search]<br />";
#print "FILE: $e107SearchFile<br />";
#print "NO FILE: $e107NoSearchFile<br />";

if ((($enSearch)&&(!$e107Search))&&(file_exists($e107NoSearchFile)))
{
	#print "SEARCH ON, E107SEACH OFF<br />";
	
	if (rename($e107NoSearchFile, $e107SearchFile))
	{
		$message .= "<br />".AUTOGAL_LANG_ADMIN_MAIN_L85;
	}
	else
	{
		$message .= "<br />".str_replace("[SRCFILE]", $e107NoSearchFile, str_replace("[DSTFILE]", $e107SearchFile, AUTOGAL_LANG_ADMIN_MAIN_L87));
	}
}
else if ((!$enSearch)&&($e107Search))
{
	#print "SEARCH OFF, E107SEARCH ON<br />";
	
	if (rename($e107SearchFile, $e107NoSearchFile))
	{
		$message .= "<br />".AUTOGAL_LANG_ADMIN_MAIN_L86;
	}
	else
	{
		$message .= "<br />".str_replace("[SRCFILE]", $e107NoSearchFile, str_replace("[DSTFILE]", $e107SearchFile, AUTOGAL_LANG_ADMIN_MAIN_L88));
	}
}

if ($_GET['cpthup'])
{
	$message = AUTOGAL_LANG_ADMIN_MAIN_L48;
	$baseFile = dirname(__FILE__)."/def_basedirs.php";
	$msg = "<b>".AUTOGAL_LANG_ADMIN_MAIN_L72."</b><br /><br />".str_replace("[FILE]", $baseFile, AUTOGAL_LANG_ADMIN_MAIN_L73)."<br />";
	$ns->tablerender("", $msg);
}
	
if ($message)
{
    $ns -> tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}
else
{
    ######################
    # CHECK RESIZE MODES #
    ######################
	$resizeMethValid = AutoGal_CheckResizeMethod($resizeMethodText);
    if (!$resizeMethValid)
	{
		$ns -> tablerender("", "<div style='text-align:center'>$resizeMethodText</div>");
	}
}

$usingHttps = (isset($_POST['autogal_usinghttps']) ? $_POST['autogal_usinghttps'] : $currUsingHttps);

########################
# CHECK GALLERY FOLDER #
########################
$galDir = $pref['autogal_gallerydir'];
if (($galDir)&&(!is_dir(realpath($galDir))))
{
	$ns -> tablerender("", "<div style='text-align:center'><font color='red'><b>".AUTOGAL_LANG_ADMIN_MAIN_L1."</b></font> ".str_replace('[GALLERYDIR]', $galDir, AUTOGAL_LANG_ADMIN_MAIN_L2)."<br />".AUTOGAL_LANG_ADMIN_MAIN_L3."\"".realpath($galDir)."\"</div>");
}

####################################
# CHECK DATABASE TABLE DEFINITIONS #
####################################
AutoGal_CheckTableDefs();

###############################
# CHECK FILE STUCTURE UPDATES #
###############################
AutoGal_CheckFileUpdates();

############################
# CHECK FOLDER PERMISSIONS #
############################
if ($uploadPerms = IsBadGalleryDirPerms()) $ns -> tablerender(AUTOGAL_LANG_ADMIN_MAIN_L4, $uploadPerms); 
if ($uploadPerms = IsBadUploadDirPerms()) $ns -> tablerender(AUTOGAL_LANG_ADMIN_MAIN_L5, $uploadPerms); 
if ($uploadPerms = IsBadLogDirPerms()) $ns -> tablerender(AUTOGAL_LANG_ADMIN_MAIN_L6, $uploadPerms); 

################
# INPUT FIELDS #
################
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_MAIN_L7."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L8."</b></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='30' name='autogal_title' value='".$pref['autogal_title']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L9."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L10."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='30' name='autogal_rootname' value='".$pref['autogal_rootname']."'></td>
</tr>
</table>
<br />

<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_MAIN_L93."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L94."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L95.' '.AUTOGAL_LANG_ADMIN_MAIN_L98."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_enablegaldispord'".($pref['autogal_enablegaldispord'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L96."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L97.' '.AUTOGAL_LANG_ADMIN_MAIN_L99."</span></td>
    <td style='width:50%' class='forumheader3'>
		<select name='autogal_defaultdisporder' class='tbox'>
		<option value='nameasc'".($pref['autogal_defaultdisporder'] == 'nameasc' ? " selected='selected'" : '').">".AUTOGAL_LANG_L62."</option>
		<option value='namedsc'".($pref['autogal_defaultdisporder'] == 'namedsc' ? " selected='selected'" : '').">".AUTOGAL_LANG_L63."</option>
		<option value='datedsc'".($pref['autogal_defaultdisporder'] == 'datedsc' ? " selected='selected'" : '').">".AUTOGAL_LANG_L64."</option>
		<option value='dateasc'".($pref['autogal_defaultdisporder'] == 'dateasc' ? " selected='selected'" : '').">".AUTOGAL_LANG_L65."</option>
		</select>
	</td>
</tr>
</table>
<br />

<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_MAIN_L56."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L27."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L28."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='10' name='autogal_uploadmaxsize' value='".$pref['autogal_uploadmaxsize']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L29."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L30."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='3' name='autogal_uploadnumber' value='".$pref['autogal_uploadnumber']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L54."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L55."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='50' name='autogal_uploadexts' value='".$pref['autogal_uploadexts']."'></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_MAIN_L57."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L11."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L12."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_emailtofriend'".($pref['autogal_emailtofriend'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L13."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L14."</span></td>
    <td style='width:50%' class='forumheader3'><textarea class='tbox' name='autogal_defaultetfcom' cols='40' rows='3'>".$pref['autogal_defaultetfcom']."</textarea></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_MAIN_L59."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L15."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L16." <a href='".AUTOGAL_XMLMETASETTINGS."'>".AUTOGAL_LANG_ADMIN_MAIN_L17."</a> ".AUTOGAL_LANG_ADMIN_MAIN_L18."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_enablesearch'".($pref['autogal_enablesearch'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L89."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L90."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_enablesearche107'".($pref['autogal_enablesearche107'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L19."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L20."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='4' name='autogal_searchmaxresults' value='".$pref['autogal_searchmaxresults']."'></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_MAIN_L60."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L39."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L40."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showinnewwindow'".($pref['autogal_showinnewwindow'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_1."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_2."</span></td>
    <td style='width:50%' class='forumheader3'>
		<input type='text' class='tbox' size='4' name='autogal_shownwinwidth' value=\"".$pref['autogal_shownwinwidth']."\"> x <input type='text' size='4' class='tbox' name='autogal_shownwinheight' value=\"".$pref['autogal_shownwinheight']."\">
	</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_3."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_4."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownwintoobar'".($pref['autogal_shownwintoobar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_5."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_6."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownwinlocbar'".($pref['autogal_shownwinlocbar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_7."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_8."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownwinstsbar'".($pref['autogal_shownwinstsbar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_9."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_10."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownwinmnubar'".($pref['autogal_shownwinmnubar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_11."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_12."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownwinscrbar'".($pref['autogal_shownwinscrbar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_13."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_14."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownwinresize'".($pref['autogal_shownwinresize'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_15."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_16."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownwindirect'".($pref['autogal_shownwindirect'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_17."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_18."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownwincphist'".($pref['autogal_shownwincphist'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_19."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_20."</span></td>
    <td style='width:50%' class='forumheader3'><input type='text' class='tbox' size='30' name='autogal_shownwinexargs' value=\"".$pref['autogal_shownwinexargs']."\"></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L43."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L44."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_largeimgnewwindow'".($pref['autogal_largeimgnewwindow'] ? " checked" : "")."></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_MAIN_L61."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L35."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L36."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownewest'".($pref['autogal_shownewest'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L37."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L38."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_shownewestinroot'".($pref['autogal_shownewestinroot'] ? " checked" : "")."></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_MAIN_L58."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L64."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L63."<br /><br />".str_replace("[FILE]", basename(__FILE__), str_replace("[SERVER]", $_SERVER['HTTP_HOST'], AUTOGAL_LANG_ADMIN_MAIN_L65))."</span></td>
    <td style='width:50%' class='forumheader3'>
		<input class='tbox' type='text' size='50' name='autogal_customhttppath' value='".(isset($_POST['autogal_customhttppath']) ? $cusHttpPath : AUTOGAL_CUSTOMHTTPPATH)."'><br />".
		AUTOGAL_LANG_ADMIN_MAIN_L83."<i>".AutoGal_DefCorePath()."</i><br />".
		AUTOGAL_LANG_ADMIN_MAIN_L84."<i>".AutoGal_DocumentRoot()."</i><br />".
		AUTOGAL_LANG_ADMIN_MAIN_L68."<i>".AutoGal_GuessBaseHTTPPath()."</i><br />".
	"</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L66."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L63."<br /><br />".AUTOGAL_LANG_ADMIN_MAIN_L67."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='50' name='autogal_customabspath' value='".(isset($_POST['autogal_customabspath'])? $cusAbsPath : AUTOGAL_CUSTOMABSPATH)."'><br />".AUTOGAL_LANG_ADMIN_MAIN_L68."<i>".AutoGal_GuessBaseAbsPath()."</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L76."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L77."</span></td>
    <td style='width:50%' class='forumheader3'>
	<select class='tbox' name='autogal_usinghttps'>
	<option value='detect'".($usingHttps == 'detect' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_MAIN_L78."</option>
	<option value='never'". ($usingHttps == 'never'  ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_MAIN_L79."</option>
	<option value='always'".($usingHttps == 'always' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_MAIN_L80."</option>
	</select>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L31."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L32."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='50' name='autogal_gallerydir' value='".$pref['autogal_gallerydir']."'></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_MAIN_L62."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L33."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L34."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_chmodwarnoff'".($pref['autogal_chmodwarnoff'] ? " checked" : "")."></td>
</tr>

<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L52."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L53."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_sortdatectime'".($pref['autogal_sortdatectime'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L81."</b><br /><span class='smalltext'>".str_replace("[WIDTH]", $pref['autogal_maximagewidth'], str_replace("[HEIGHT]", $pref['autogal_maximageheight'], str_replace("[PREFIX]", AUTOGAL_PREVIEWIMGPREFIX, AUTOGAL_LANG_ADMIN_MAIN_L82)))."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_resizepreviewimgs'".($pref['autogal_resizepreviewimgs'] ? " checked" : "")."></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2'  style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updatesettings' value='".AUTOGAL_LANG_ADMIN_MAIN_L45."' />
    </td>
</tr>
</table>
<br />
</form>
</div>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_MAIN_L47, $text);
if ($resizeMethValid) $ns -> tablerender("", "<div style='text-align:center'>$resizeMethodText</div>");
require_once(e_ADMIN."footer.php");

?>
