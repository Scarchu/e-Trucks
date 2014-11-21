<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org).
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        02/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");

$galPathTitle = wordwrap(realpath($pref['autogal_gallerydir']), 30, "<br />\n", true);
 
$ec_dir = e_PLUGINS."lazyadmin_menu/";
$caption = AUTOGAL_LANG_HELP_L0;
$text = "
<table width='97%'>
<tr><td class='forumheader'><b>".AUTOGAL_LANG_HELP_L1."</b></td></tr>
<tr><td class='forumheader3' style='text-align:center'>
<span class='smalltext'>$galPathTitle</span>
</td></tr>
<tr><td class='forumheader'><b>".AUTOGAL_LANG_HELP_L2."</b></td></tr>
<tr><td class='forumheader3' style='text-align:center'>
<a href=\"".AUTOGAL_AUTOGALLERY."\">".AUTOGAL_LANG_HELP_L17."</a>
</td></tr>
<tr><td class='forumheader'><b>".AUTOGAL_LANG_HELP_L3."</b></td></tr>
<tr><td class='forumheader3' style='text-align:center'>
".AUTOGAL_LANG_HELP_L4."<br />
<hr size='1' />
".AUTOGAL_LANG_HELP_L5."<br />
<hr size='1' />
".AUTOGAL_LANG_HELP_L6."<br />
<hr size='1' />
".AUTOGAL_LANG_HELP_L7."<br />
<hr size='1' />
".AUTOGAL_LANG_HELP_L8."<br />
<hr size='1' />
".AUTOGAL_LANG_HELP_L9."<br />
<hr size='1' />
".str_replace("[PREFIX]", AUTOGAL_THUMBPREFIX, AUTOGAL_LANG_HELP_L19)."<br />
<hr size='1' />
".str_replace("[DEFIMAGE]", "<font face='courier new'>".AUTOGAL_GALLERYTHUMBFILENAME.".[".AUTOGAL_LANG_HELP_L18."]</font>", AUTOGAL_LANG_HELP_L12)."<br />
<hr size='1' />
".str_replace("[PREFIX]", AUTOGAL_THUMBPREFIX, AUTOGAL_LANG_HELP_L11)."<br />
</td></tr>
<tr><td class='forumheader'><b>".AUTOGAL_LANG_HELP_L16."</b></td></tr>
<tr><td class='forumheader3' style='text-align:center'>
".AUTOGAL_LANG_HELP_L15."<br /><a href='".AUTOGAL_SUPPORTLINK."'>".AUTOGAL_LANG_HELP_L17."</a>
</td></tr>
</table>";

$ns -> tablerender($caption, $text);
?>
