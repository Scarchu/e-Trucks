<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        11/04/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");
/*
require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");
*/
unset ($message);

###################
# SET PREFERENCES #
###################
if(IsSet($_POST['updatesettings']))
{
    $autoWas = $pref['autogal_wmarkauto'];
	$autoNow = $_POST['autogal_wmarkauto'];
	
	$pref['autogal_wmarkimage']     = $_POST['autogal_wmarkimage'];
	$pref['autogal_wmarkintensity'] = (($_POST['autogal_wmarkintensity'] > 0 && $_POST['autogal_wmarkintensity'] <= 100) ? $_POST['autogal_wmarkintensity'] : 30);
    $pref['autogal_wmarkxalign']    = $_POST['autogal_wmarkxalign'];
    $pref['autogal_wmarkyalign']    = $_POST['autogal_wmarkyalign'];
	$pref['autogal_wmarkxoffset']   = (preg_match("/^[0-9]+$/", $_POST['autogal_wmarkxoffset']) ? $_POST['autogal_wmarkxoffset'] : 0);
	$pref['autogal_wmarkyoffset']   = (preg_match("/^[0-9]+$/", $_POST['autogal_wmarkyoffset']) ? $_POST['autogal_wmarkyoffset'] : 0);
	$pref['autogal_wmarkauto']      = $_POST['autogal_wmarkauto'];
	$pref['autogal_wmarknosmall']   = $_POST['autogal_wmarknosmall'];
	$pref['autogal_wmarknotgals']   = $_POST['autogal_wmarknotgals'];

    save_prefs();
	
	if ($autoWas != $autoNow) 
	{
		$htaccessFile = AutoGal_GetAbsGalPath('').'/.htaccess';
		
		if (!file_exists($htaccessFile))
		{
			if (!touch($htaccessFile))
			{
				$message = "<b>".AUTOGAL_LANG_ADMIN_SECURITY_L18."</b>".str_replace("[FILE]", $htaccessFile, AUTOGAL_LANG_ADMIN_SECURITY_L19)." ".AUTOGAL_LANG_ADMIN_SECURITY_L17;
			}
		}
		
		if (!is_writable($htaccessFile))
		{
			if (!chmod($htaccessFile, octdec(AUTOGAL_PERMSHTACCESS)))
			{
				$message = "<b>".AUTOGAL_LANG_ADMIN_SECURITY_L18."</b>".str_replace("[FILE]", $htaccessFile, AUTOGAL_LANG_ADMIN_SECURITY_L20)." (is_writable) ".AUTOGAL_LANG_ADMIN_SECURITY_L17;
			}
			
			if (!is_writable($htaccessFile))
			{
				$message = "<b>".AUTOGAL_LANG_ADMIN_SECURITY_L18."</b>".str_replace("[FILE]", $htaccessFile, AUTOGAL_LANG_ADMIN_SECURITY_L20)." (is_writable) ".AUTOGAL_LANG_ADMIN_SECURITY_L17;
			}
		}
		else
		{
			if (!AutoGal_WriteHtaccess($htaccessFile, 'security,leech,watermark'))
			{
				$message = "<b>".AUTOGAL_LANG_ADMIN_SECURITY_L18."</b>".str_replace("[FILE]", $htaccessFile, AUTOGAL_LANG_ADMIN_SECURITY_L20)." (fopen) ".AUTOGAL_LANG_ADMIN_SECURITY_L17;
			}
			else
			{
				$message = AUTOGAL_LANG_ADMIN_SECURITY_L1;
			}
		}
	}
	else
	{
		$message = AUTOGAL_LANG_ADMIN_WATERMARK_1;
	}
}

if ($message)
{
    $ns -> tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}

# GET LIST OF WATERMARKS
$dh = opendir(AUTOGAL_WATERMARKDIRABS);
while ($file = readdir($dh))
{
	if (!AutoGal_IsImage($file)) continue;
	if (!$firstWMark) $firstWMark = $file;
	$wmImageOpts .= "<option value=\"".rawurlencode($file)."\"".($file == $pref['autogal_wmarkimage'] ? " selected='selected'" : '').">$file</option>";
}

$selWMarkImg = ($pref['autogal_wmarkimage'] ? $pref['autogal_wmarkimage'] : $firstWMark);
if ($selWMarkImg)
{
	$selWMarkUrl = AUTOGAL_WATERMARKDIR."/$selWMarkImg";
}

################
# INPUT FIELDS #
################
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_WATERMARK_19."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_3."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_WATERMARK_4."</span></td>
    <td style='width:50%' class='forumheader3'><select class='tbox' name='autogal_wmarkimage'>$wmImageOpts</select>".($selWMarkUrl ? "<br /><br /><img src=\"$selWMarkUrl \" style='border:0' />" : '')."</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_5."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_WATERMARK_6."</span></td>
    <td style='width:50%' class='forumheader3'><input type='text' class='tbox' name='autogal_wmarkintensity' size='3' value=\"".$pref['autogal_wmarkintensity']."\"></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_7."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_WATERMARK_8."</span></td>
    <td style='width:50%' class='forumheader3'>
		<select name='autogal_wmarkyalign' class='tbox'>
		<option value='t'".($pref['autogal_wmarkyalign'] == 't' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_9."</option>
		<option value='m'".($pref['autogal_wmarkyalign'] == 'm' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_10."</option>
		<option value='b'".($pref['autogal_wmarkyalign'] == 'b' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_11."</option>
		</select>
		&nbsp;
		<select name='autogal_wmarkxalign' class='tbox'>
		<option value='l'".($pref['autogal_wmarkxalign'] == 'l' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_12."</option>
		<option value='c'".($pref['autogal_wmarkxalign'] == 'c' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_13."</option>
		<option value='r'".($pref['autogal_wmarkxalign'] == 'r' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_14."</option>
		</select>
	</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_15."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_WATERMARK_16."</span></td>
    <td style='width:50%' class='forumheader3'>
		X: <input type='text' class='tbox' name='autogal_wmarkxoffset' size='3' value=\"".$pref['autogal_wmarkxoffset']."\"> 
		Y: <input type='text' class='tbox' name='autogal_wmarkyoffset' size='3' value=\"".$pref['autogal_wmarkyoffset']."\">
	<td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_17."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_WATERMARK_18."<br/ ><br/ >".AUTOGAL_LANG_ADMIN_WATERMARK_27."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_wmarkauto'".($pref['autogal_wmarkauto'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_24."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_WATERMARK_25."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_wmarknosmall'".($pref['autogal_wmarknosmall'] ? " checked" : "")."></td>
</tr>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updatesettings' value='".AUTOGAL_LANG_ADMIN_WATERMARK_20."' />
    </td>
</tr>
</table>";

$htaccessFile = AutoGal_GetAbsGalPath('').'/.htaccess';
if (is_readable($htaccessFile))
{
	$H_HTACCESS = fopen($htaccessFile, 'r');
	
	if ($H_HTACCESS)
	{
		flock($H_HTACCESS, LOCK_EX);
		$htaccessData = fread($H_HTACCESS, filesize($htaccessFile));
		flock($H_HTACCESS, LOCK_UN);
		
		fclose($H_HTACCESS);
		
		if ($htaccessData)
		{
			$htaccessData = htmlspecialchars($htaccessData);
			
			$text .= 
			"<br />
			<table style='width:97%' class='fborder'>
			<tr style='vertical-align:top'>
				<td style='text-align:center' class='forumheader2'>".AUTOGAL_LANG_ADMIN_WATERMARK_28."<br />($htaccessFile)</td>
			</tr>
			<tr style='vertical-align:top'>
				<td style='text-align:left' class='forumheader3'><font face='courier new'><pre>$htaccessData</pre></font></td>
			</tr>
			</table>";
		}
	}
}

$text .= "
<br />
".str_replace("[DIR]", AUTOGAL_WATERMARKDIRABS, AUTOGAL_LANG_ADMIN_WATERMARK_23)."<br />
<br />
".AUTOGAL_LANG_ADMIN_WATERMARK_26."<br />
</form>
</div>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_WATERMARK_2, $text);

require_once(FOOTERF);
exit;
?>

