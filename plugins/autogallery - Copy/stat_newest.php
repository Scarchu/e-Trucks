<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        13/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

$startAG = microtime(true);

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");

if (!AUTOGAL_SHOWNEWESTLINK) {header("location:".AUTOGAL_AUTOGALLERY); exit;}

$start = ($_GET['start'] ? $_GET['start'] : 0);
$newObjects = AutoGal_GetLatestFiles(AUTOGAL_SHOWNEWESTNUM, $start, $totalImages);

define("e_PAGETITLE", AUTOGAL_TITLE." / ".AUTOGAL_LANG_STAT_L12);
require_once(HEADERF);

$rank = $start + 1;
$text = '';
foreach ($newObjects as $mediaObj)
{
	$galObj = $mediaObj->GalleryMediaObj();

	$text .= "
	<tr>
		<td class='forumheader3' style='text-align:center'>$rank.</td>
		<td class='forumheader3' style='text-align:center'>".$mediaObj->ThumbAndTitleHtml(AUTOGAL_SHOWSUBTITLESGAL)."</td>
		<td class='forumheader3' style='text-align:center'>".$galObj->TitleLink()."</td>
		<td class='forumheader3' style='text-align:center'>".strftime(AUTOGAL_LATESTTIMEFORMAT, $mediaObj->UpdateTime())."</td>
	</tr>";
	
	$rank ++;
}

if ($text)
{
	$text = "
	<table width='97%' class='border'>
	<tr>
		<td class='forumheader' style='text-align:center'>".AUTOGAL_LANG_STAT_L1."</td>
		<td class='forumheader' style='text-align:center'>".AUTOGAL_LANG_STAT_L2."</td>
		<td class='forumheader' style='text-align:center'>".AUTOGAL_LANG_STAT_L3."</td>
		<td class='forumheader' style='text-align:center'>".AUTOGAL_LANG_STAT_L4."</td>
	</tr>
	$text
	</table>";
}
else
{
	$text = "<div style='text-align:center'><b>".AUTOGAL_LANG_STAT_L6."</b></div>";
}

if (($start + AUTOGAL_SHOWNEWESTNUM) < $totalImages)
	$nextButton = "<input type='button' class='button' value='".AUTOGAL_LANG_L9."' onclick='javascript:window.location.href=\"".AUTOGAL_STATNEWEST."?start=".($start + AUTOGAL_SHOWNEWESTNUM)."\"' />";
else
	$nextButton = '';

if ($start > 0)
	$prevButton = "<input type='button' class='button' value='".AUTOGAL_LANG_L8."' onclick='javascript:window.location.href=\"".AUTOGAL_STATNEWEST."?start=".($start - AUTOGAL_SHOWNEWESTNUM)."\"' />";
else
	$prevButton = '';
	
$botLinks = AutoGal_GetBotLinks('', true, false);

$text = "<br />$text<br /><div style='text-align:center'>$prevButton $nextButton".(count($botLinks) > 0 ? "<br />".implode(' ', $botLinks) : '')."</div>";

if (AUTOGAL_SHOWAUTOGALVER)
{
	$agVer = AutoGal_GetVersion();
	$autoGalVer = 
	"<div class='smalltext' style='text-align:center'>".
	AUTOGAL_LANG_L1.
	"<a target='_blank' href='".AUTOGAL_DOWNLOADURL."'>Auto Gallery</a> v$agVer".
	"</div>";
}
else
{
	$autoGalVer = '';
}

$ns->tablerender(AUTOGAL_TITLE.' - '.AUTOGAL_LANG_STAT_L12, $text);
$AGTime = abs(microtime(true) - $startAG);
print "<div class='smalltext' style='text-align:center'>".str_replace("[TIME]", substr($AGTime, 0, 5), AUTOGAL_LANG_L5)."</div>";
print $autoGalVer;

if (AUTOGAL_SHOW_FOOTER){require_once(FOOTERF);}

?>