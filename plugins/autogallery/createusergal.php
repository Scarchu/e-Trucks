<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        05/01/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_MEDIAOBJCLASS);

define("e_PAGETITLE", $pref['autogal_title']." - ".AUTOGAL_LANG_USERGALS_L8);
require_once(HEADERF);

$user = stripslashes($_GET['user']);

$text = "<div style='text-align:center'>";

if (!USER)
{
	$text .= "<b>".AUTOGAL_LANG_USERGALS_L2."</b><br />";
}
else if (!$user)
{
	$text .= "<b>".AUTOGAL_LANG_USERGALS_L7."</b><br />";
}
else if (!AUTOGAL_USERGALENABLE)
{
	$text .= "<b>".AUTOGAL_LANG_USERGALS_L3."</b><br />";
}
else if ($user != USERNAME)
{
	$text .= "<b>".AUTOGAL_LANG_USERGALS_L4."</b><br />";
}
else if (!AutoGal_IsUserGalleryAllowed())
{
	$text .= "<b>".AUTOGAL_LANG_USERGALS_L5."</b><br />";
}
else if ($userGal = AutoGal_UserGallery())
{
	$text .= "<b>".AUTOGAL_LANG_USERGALS_L6."</b><br /><br />[<a href=\"".$userGal->BackLink()."\">".AUTOGAL_LANG_USERGALS_L11."</a>]<br />";
}
else
{
	$user = AutoGal_RemoveIllegalFileChars($user);
	require_once(AUTOGAL_EDITFUNCTIONS);
	
	$userRootGal = new AutoGal_CMediaObj(AUTOGAL_USERGALLERYDIR);
	if (!$userRootGal->IsValid())
	{
		$text .= AUTOGAL_LANG_USERGALS_L10.$userRootGal->LastError();
	}
	else
	{
		$msgs = AutoGal_CreateGallery($userRootGal, $user, $userGal);
		$text .= AutoGal_ReturnMsgsHtml($msgs);
		
		if (($userGal)&&($userGal->IsValid()))
		{
			AutoGal_ClearCacheMenu($userRootGal->Element(), 0);
			$text .= "<br />[<a href=\"".$userGal->BackLink()."\">".AUTOGAL_LANG_USERGALS_L11."</a>]<br />";
		}
	}
}

$text .= "<br />".AutoGal_GetBotLinksStr();
$text .= "</div>";
$ns -> tablerender(e_PAGETITLE, $text);

if ($pref['autogal_showfooter']) require_once(FOOTERF);
exit;

?>