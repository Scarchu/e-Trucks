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
	$pref['autogal_slidesenable']	 = $_POST['autogal_slidesenable'];
    $pref['autogal_slidesnewwindow'] = $_POST['autogal_slidesnewwindow'];
	
	$pref['autogal_slidenwinwidth']  = $_POST['autogal_slidenwinwidth'];
    $pref['autogal_slidenwinheight'] = $_POST['autogal_slidenwinheight'];
	$pref['autogal_slidenwintoobar'] = $_POST['autogal_slidenwintoobar'];
	$pref['autogal_slidenwinlocbar'] = $_POST['autogal_slidenwinlocbar'];
	$pref['autogal_slidenwinstsbar'] = $_POST['autogal_slidenwinstsbar'];
	$pref['autogal_slidenwinmnubar'] = $_POST['autogal_slidenwinmnubar'];
	$pref['autogal_slidenwinscrbar'] = $_POST['autogal_slidenwinscrbar'];
	$pref['autogal_slidenwindirect'] = $_POST['autogal_slidenwindirect'];
	$pref['autogal_slidenwincphist'] = $_POST['autogal_slidenwincphist'];
	$pref['autogal_slidenwinresize'] = $_POST['autogal_slidenwinresize'];
	$pref['autogal_slidenwinexargs'] = $_POST['autogal_slidenwinexargs'];
	$pref['autogal_slidebodyclass']  = $_POST['autogal_slidebodyclass'];
	$pref['autogal_slidebodystyle']  = $_POST['autogal_slidebodystyle'];

	save_prefs();
		
	$message = "<b>".AUTOGAL_LANG_ADMIN_SLIDESHOW_2."</b>";
}

if ($message)
{
    $ns -> tablerender("", "<div style='text-align:center'>$message</div>");
}

#$slidesDis = (!$pref['autogal_slidesenable'] ? " disabled='disabled'" : '');
#$slideNWDis = (!$pref['autogal_slidesnewwindow'] || !$pref['autogal_slidesenable'] ? " disabled='disabled'" : '');

################
# INPUT FIELDS #
################
$text = "
<div style='text-align:center'>
<form method='post' name='autogal_xmladmin' action='".e_SELF."'>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_1."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_SLIDESHOW_3."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_SLIDESHOW_4."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidesenable'".($pref['autogal_slidesenable'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_SLIDESHOW_6."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_SLIDESHOW_7."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidesnewwindow'".($pref['autogal_slidesnewwindow'] ? " checked='true'" : "")."></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_SLIDESHOW_9."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_SLIDESHOW_10."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_SLIDESHOW_11."</span></td>
    <td style='width:50%' class='forumheader3'><input type='text' class='tbox' size='30' name='autogal_slidebodyclass' value=\"".$pref['autogal_slidebodyclass']."\"></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_SLIDESHOW_12."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_SLIDESHOW_13."</span></td>
    <td style='width:50%' class='forumheader3'><input type='text' class='tbox' size='30' name='autogal_slidebodystyle' value=\"".$pref['autogal_slidebodystyle']."\"></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_SLIDESHOW_8."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_1."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_2."</span></td>
    <td style='width:50%' class='forumheader3'>
		<input type='text' class='tbox' size='4' name='autogal_slidenwinwidth' value=\"".$pref['autogal_slidenwinwidth']."\"> x <input type='text' size='4' class='tbox' name='autogal_slidenwinheight' value=\"".$pref['autogal_slidenwinheight']."\">
	</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_3."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_4."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidenwintoobar'".($pref['autogal_slidenwintoobar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_5."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_6."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidenwinlocbar'".($pref['autogal_slidenwinlocbar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_7."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_8."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidenwinstsbar'".($pref['autogal_slidenwinstsbar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_9."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_10."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidenwinmnubar'".($pref['autogal_slidenwinmnubar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_11."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_12."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidenwinscrbar'".($pref['autogal_slidenwinscrbar'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_13."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_14."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidenwinresize'".($pref['autogal_slidenwinresize'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_15."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_16."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidenwindirect'".($pref['autogal_slidenwindirect'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_17."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_18."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_slidenwincphist'".($pref['autogal_slidenwincphist'] ? " checked='true'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_NEWWINDOW_19."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_NEWWINDOW_20."</span></td>
    <td style='width:50%' class='forumheader3'><input type='text' class='tbox' size='30' name='autogal_slidenwinexargs' value=\"".$pref['autogal_slidenwinexargs']."\"></td>
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
<a href='http://www.cerebralsynergy.com'><img style='border:0' alt='Cerebral Synergy' src='".e_PLUGINS."autogallery/Images/button.png' /></a><br />
<a href='".AUTOGAL_SUPPORTLINK."'>".AUTOGAL_LANG_ADMIN_METADATA_L17."</a><br />
<br />
</form>
</div>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_SLIDESHOW_14, $text);
require_once(e_ADMIN."footer.php");
exit;
