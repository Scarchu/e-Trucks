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

/*require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");
*/
unset ($message);

$galHtaccessFile = AutoGal_GetAbsGalPath('').'/.htaccess';

###################
# SET PREFERENCES #
###################
if(IsSet($_POST['updatesettings']))
{
    $pref['autogal_apacheindexignore']  = $_POST['autogal_apacheindexignore'];
    $pref['autogal_apachedenyexts']     = $_POST['autogal_apachedenyexts'];
    $pref['autogal_apacheleechprotect'] = $_POST['autogal_apacheleechprotect'];
	$pref['autogal_apacheallowedsites'] = str_replace("\r", "", $_POST['autogal_apacheallowedsites']);
	$pref['autogal_apacheleechimage']   = $_POST['autogal_apacheleechimage'];
	
    save_prefs();
	
	$htaccessFile = AUTOGAL_HTACCESS;
	if (!file_exists($htaccessFile))
	{
		if (!touch($htaccessFile))
		{
			$message = "<b>".AUTOGAL_LANG_ADMIN_SECURITY_L18."</b>".str_replace("[FILE]", $htaccessFile, AUTOGAL_LANG_ADMIN_SECURITY_L19)." (file_exists) ".AUTOGAL_LANG_ADMIN_SECURITY_L17;
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
		if (!AutoGal_WriteHtaccess($htaccessFile, 'security'))
		{
			$message = "<b>".AUTOGAL_LANG_ADMIN_SECURITY_L18."</b>".str_replace("[FILE]", $htaccessFile, AUTOGAL_LANG_ADMIN_SECURITY_L20)." (fopen) ".AUTOGAL_LANG_ADMIN_SECURITY_L17;
		}
		else
		{
			$message .= AUTOGAL_LANG_ADMIN_SECURITY_L1;
		}
		
		# Write the gallery .htaccess file
		$htaccessFile = $galHtaccessFile;
		if (!AutoGal_WriteHtaccess($htaccessFile, 'security,leech,watermark'))
		{
			$message .= "<br /><b>".AUTOGAL_LANG_ADMIN_SECURITY_L18."</b>".str_replace("[FILE]", $htaccessFile, AUTOGAL_LANG_ADMIN_SECURITY_L20)." (fopen) ".AUTOGAL_LANG_ADMIN_SECURITY_L17;
		}
		else
		{
			$message .= "<br />".AUTOGAL_LANG_ADMIN_SECURITY_L25;
		}
	}
}

if ($message)
{
    $ns -> tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}

################
# INPUT FIELDS #
################
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<br />
<b>".AUTOGAL_LANG_ADMIN_SECURITY_L12."</b>".AUTOGAL_LANG_ADMIN_SECURITY_L13."<br />
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_SECURITY_L23."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_SECURITY_L2."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_SECURITY_L3."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_apacheindexignore'".($pref['autogal_apacheindexignore'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_SECURITY_L4."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_SECURITY_L5."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_apachedenyexts'".($pref['autogal_apachedenyexts'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_SECURITY_L6."</b> (<a href=\"http://en.wikipedia.org/wiki/Direct_linking\">?</a>)<br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_SECURITY_L7."<br /><br /><b><font color='red'>".AUTOGAL_LANG_ADMIN_SECURITY_L8."</font></b>".AUTOGAL_LANG_ADMIN_SECURITY_L9."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_apacheleechprotect'".($pref['autogal_apacheleechprotect'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_SECURITY_L10."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_SECURITY_L11."</span></td>
    <td style='width:50%' class='forumheader3'><textarea name='autogal_apacheallowedsites' cols='30' rows='7' wrap='off'>".$pref['autogal_apacheallowedsites']."</textarea></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_SECURITY_L21."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_SECURITY_L22.($pref['autogal_apacheleechimage'] ? "<br /><br /><img style='border:0' src=\"".AUTOGAL_LEECH_IMAGE."\" />" : '')."</span></td>
    <td style='width:50%' class='forumheader3'><input type='text' name='autogal_apacheleechimage' size='50' value=\"".$pref['autogal_apacheleechimage']."\"></td>
</tr>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updatesettings' value='".AUTOGAL_LANG_ADMIN_SECURITY_L14."' />
    </td>
</tr>
</table>
<br />";

if (is_readable(AUTOGAL_HTACCESS)) $text .= AutoGal_HtaccessPreview(AUTOGAL_HTACCESS);
if (is_readable($galHtaccessFile)) $text .= AutoGal_HtaccessPreview($galHtaccessFile);

$text .= "
<a href='http://www.cerebralsynergy.com'><img style='border:0' alt='Cerebral Synergy' src='".e_PLUGINS."autogallery/Images/button.png' /></a><br />
<a href='".AUTOGAL_SUPPORTLINK."'>".AUTOGAL_LANG_ADMIN_MAIN_L46."</a><br />
</form>
</div>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_SECURITY_L16, $text);

require_once(e_ADMIN."footer.php");

function AutoGal_HtaccessPreview($filePath)
{
	$H_HTACCESS = fopen($filePath, 'r');
	
	if ($H_HTACCESS)
	{
		flock($H_HTACCESS, LOCK_EX);
		$htaccessData = fread($H_HTACCESS, filesize($filePath));
		flock($H_HTACCESS, LOCK_UN);
		
		fclose($H_HTACCESS);
		
		if ($htaccessData)
		{
			$htaccessData = htmlspecialchars($htaccessData);
			
			$text .= 
			"<br />
			<table style='width:97%' class='fborder'>
			<tr style='vertical-align:top'>
				<td style='text-align:center' class='forumheader'>".AUTOGAL_LANG_ADMIN_SECURITY_L24."<br />(".realpath($filePath).")</td>
			</tr>
			<tr style='vertical-align:top'>
				<td style='text-align:left' class='forumheader3'><font face='courier new'><pre>$htaccessData</pre></font></td>
			</tr>
			</table>
			<br />";
		}
	}
	
	return $text;
}

?>

