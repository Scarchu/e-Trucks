<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        03/09/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");

/*require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");
*/
###################
# SET PREFERENCES #
###################
if (IsSet($_POST['updatesettings']))
{
	$pref['autogal_usergalenable']    = $_POST['autogal_usergalenable'];
	$pref['autogal_usergalname']      = $_POST['autogal_usergalname'];
	$pref['autogal_usergaluserclass'] = $_POST['autogal_usergaluserclass'];
	
	save_prefs();
		
	$message = "<b>".AUTOGAL_LANG_ADMIN_USERGALS_2."</b>";
}

if ($message)
{
    $ns -> tablerender("", "<div style='text-align:center'>$message</div>");
}

$userGalPath = AutoGal_GetAbsGalPath(AUTOGAL_USERGALLERYDIR);
if ($pref['autogal_usergalenable'])
{
	if (!is_dir($userGalPath))
	{
		require_once(AUTOGAL_EDITFUNCTIONS);
		require_once(AUTOGAL_MEDIAOBJCLASS);
		$rootGalObj = new AutoGal_CMediaObj('');
		
		$msgs = AutoGal_CreateGallery($rootGalObj, AUTOGAL_USERGALLERYDIR, $newGal);
		AutoGal_ClearCacheMenu($rootGalObj->Element());
		$ns->tablerender(AUTOGAL_LANG_ADMIN_USERGALS_11, AutoGal_ReturnMsgsHtml($msgs));
	}
}
else
{
	if (is_dir($userGalPath))
	{
		$ns->tablerender(AUTOGAL_LANG_ADMIN_USERGALS_11, str_replace("[PATH]", $userGalPath, str_replace("[GALLERY]", AUTOGAL_USERGALLERYDIR, AUTOGAL_LANG_ADMIN_USERGALS_12)));
	}
}

################
# INPUT FIELDS #
################
$text = "
<div style='text-align:center'>
<form method='post' name='autogal_xmladmin' action='".e_SELF."?".e_QUERY."'>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_USERGALS_10."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_USERGALS_3."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_USERGALS_4."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_usergalenable' name='autogal_usergalenable'".($pref['autogal_usergalenable'] ? " checked='checked'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_USERGALS_5."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_USERGALS_6."</span></td>
    <td style='width:50%' class='forumheader3'><input type='text' class='tbox' id='autogal_usergalname' name='autogal_usergalname' value=\"".$pref['autogal_usergalname']."\"></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_USERGALS_7."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_USERGALS_8."</span></td>
    <td style='width:50%' class='forumheader3'>".AutoGal_UserClassSelect("autogal_usergaluserclass", $pref['autogal_usergaluserclass'], 'admin,member,classes')."</td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2'  style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updatesettings' value='".AUTOGAL_LANG_ADMIN_USERGALS_9."' />
    </td>
</tr>
</table>
<br />
</form>
</div>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_USERGALS_1, $text);
require_once(FOOTERF);
exit;
