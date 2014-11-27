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
	$pref['autogal_revuploaduc']	   = $_POST['autogal_revuploaduc'];
    $pref['autogal_adminreviewuc']     = $_POST['autogal_adminreviewuc'];
	$pref['autogal_checksubgalvclass'] = $_POST['autogal_checksubgalvclass'];
	$pref['autogal_checklatestvclass'] = $_POST['autogal_checklatestvclass'];
	$pref['autogal_checksearchvclass'] = $_POST['autogal_checksearchvclass'];
	$pref['autogal_checklcommsvclass'] = $_POST['autogal_checklcommsvclass'];
	$pref['autogal_checkuploadvclass'] = $_POST['autogal_checkuploadvclass'];
	$pref['autogal_edituserclass']     = $_POST['autogal_edituserclass'];
	$pref['autogal_rateclass']         = $_POST['autogal_rateclass'];
	
	save_prefs();
		
	$message = "<b>".AUTOGAL_LANG_ADMIN_SLIDESHOW_2."</b>";
}

if ($message)
{
    $ns -> tablerender("", "<div style='text-align:center'>$message</div>");
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
        <b>".AUTOGAL_LANG_ADMIN_USERACCESS_2."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L21."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L22."</span></td>
    <td style='width:50%' class='forumheader3'>".AutoGal_UserClassSelect("autogal_revuploaduc", $pref['autogal_revuploaduc'])."</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_MAIN_L25."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_MAIN_L26."</span></td>
    <td style='width:50%' class='forumheader3'>".AutoGal_UserClassSelect("autogal_adminreviewuc", $pref['autogal_adminreviewuc'])."</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L26."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L27."</span></td>
    <td style='width:50%' class='forumheader3'>".AutoGal_UserClassSelect("autogal_rateclass", $pref['autogal_rateclass'])."</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_USERACCESS_16."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_USERACCESS_17."</span></td>
    <td style='width:50%' class='forumheader3'>".AutoGal_UserClassSelect("autogal_edituserclass", $pref['autogal_edituserclass'])."</td>
</tr>
</table>
<br />

<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_USERACCESS_3."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_USERACCESS_6."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_USERACCESS_7.' '.AUTOGAL_LANG_ADMIN_USERACCESS_5."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_checksubgalvclass' name='autogal_checksubgalvclass'".($pref['autogal_checksubgalvclass'] ? " checked" : "")." onclick='javascript:checkRecs(this)'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_USERACCESS_8."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_USERACCESS_9.' '.AUTOGAL_LANG_ADMIN_USERACCESS_5."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_checklatestvclass' name='autogal_checklatestvclass'".($pref['autogal_checklatestvclass'] ? " checked" : "")." onclick='javascript:checkRecs(this)'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_USERACCESS_10."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_USERACCESS_11.' '.AUTOGAL_LANG_ADMIN_USERACCESS_5."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_checksearchvclass' name='autogal_checksearchvclass'".($pref['autogal_checksearchvclass'] ? " checked" : "")." onclick='javascript:checkRecs(this)'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_USERACCESS_12."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_USERACCESS_13.' '.AUTOGAL_LANG_ADMIN_USERACCESS_5."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_checklcommsvclass' name='autogal_checklcommsvclass'".($pref['autogal_checklcommsvclass'] ? " checked" : "")." onclick='javascript:checkRecs(this)'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_USERACCESS_14."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_USERACCESS_15.' '.AUTOGAL_LANG_ADMIN_USERACCESS_5."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_checkuploadvclass' name='autogal_checkuploadvclass'".($pref['autogal_checkuploadvclass'] ? " checked" : "")." onclick='javascript:checkRecs(this)'></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2'  style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updatesettings' value='".AUTOGAL_LANG_ADMIN_SLIDESHOW_5."' />
    </td>
</tr>
</table>
<br />
</form>
</div>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_USERACCESS_1, $text);
require_once(e_ADMIN."footer.php");
exit;
