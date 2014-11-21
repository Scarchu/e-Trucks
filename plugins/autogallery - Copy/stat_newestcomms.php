<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org).
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        18/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

$startAG = microtime(true);
 
require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_LTSTCOMSHANDLER);
require_once(AUTOGAL_MEDIAOBJCLASS);

if (!AUTOGAL_DOLATESTCOMMS) {header("location:".AUTOGAL_AUTOGALLERY); exit;}
define("e_PAGETITLE", AUTOGAL_TITLE. " / " . AUTOGAL_LANG_STAT_L13);
require_once(HEADERF);

$botLinks = AutoGal_GetBotLinks('', true, true, true, true, false);

$lComms = new AutoGal_LatestComms(AUTOGAL_MAXLATESTCOMMS);
if (!$lComms->LoadFile(AUTOGAL_LATESTCOMMSXML))
{
	$text = $lComms->GetLastError();
	$text = "<br />$text<div style='text-align:center'>".(count($botLinks) > 0 ? "<br />".implode(' ', $botLinks) : '')."</div>";
	$ns->tablerender(AUTOGAL_TITLE.' - '.AUTOGAL_LANG_STAT_L13, $text);
	if (AUTOGAL_SHOW_FOOTER){require_once(FOOTERF);}
	exit;
}

$comments = $lComms->GetComments();
if (count($comments) <= 0)
{
	$text = "<div style='text-align:center'><b>".AUTOGAL_LANG_STAT_L14."</b></div>";
}
else
{
	$text = "
	<table class='border' width='97%' style='text-align:center'>
	<tr>
		<th class='forumheader' style='text-align:center'>".AUTOGAL_LANG_STAT_L19."</th>
		<th class='forumheader' style='text-align:center'>".AUTOGAL_LANG_STAT_L18."</th>
		<th class='forumheader' style='text-align:center'>".AUTOGAL_LANG_STAT_L17."</th>
	</th>";
	
	$commentNum = 1;
	foreach ($comments as $comment)
	{
		$element = $comment['element'];
		$mediaObj = new AutoGal_CMediaObj($element);
	
		$author = ($comment['authorid'] > 0 ? "<a href=\"".e_BASE."user.php?id.".$comment['authorid']."\">".$comment['authorusername']."</a>" : $comment['authorusername']);
		$date = strftime(AUTOGAL_LATCOMMTIMEFORMAT, $comment['date']);
		$commentText = $comment['text'];
		
		if (AUTOGAL_COMMENTBBCODE)
		{
			$commentText = AutoGal_DoBBCode($commentText);
		}
		else
		{
			$commentText = nl2br($commentText);
			$commentText = htmlspecialchars($commentText);
		}
		
		$textNoTags = $commentText;
		$textNoTags = str_replace("<br>", " ", $textNoTags);
		$textNoTags = str_replace("<br />", " ", $textNoTags);
		$textNoTags = strip_tags($textNoTags);
		$textNoTags = str_replace("\n", "", $textNoTags);
		
		if ((AUTOGAL_LCMAXTEXTLENGTH)&&(strlen($textNoTags) > AUTOGAL_LCMAXTEXTLENGTH))
		{
			$commentText = $textNoTags;
			$commentText = substr($commentText, 0, AUTOGAL_LCMAXTEXTLENGTH).'...';
		}
		elseif (AUTOGAL_LCSTRIPBBCODE)
		{
			$commentText = $textNoTags;
		}
		
		$text .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>$commentNum.</td>
			<td class='forumheader3' style='text-align:center'>".
				$mediaObj->ThumbImageHtml(1, 1).$mediaObj->TitleLink()."
			</td>
			<td class='forumheader3' style='text-align:left;vertical-align:top'><b>$author @ $date</b><br /><br />$commentText</td>
		</tr>";
		
		$commentNum ++;
	}
	
	$text .= "</table>";
}

$text = "<br />$text<div style='text-align:center'>$prevButton $nextButton".(count($botLinks) > 0 ? "<br />".implode(' ', $botLinks) : '')."</div>";

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

$ns->tablerender(AUTOGAL_TITLE.' - '.AUTOGAL_LANG_STAT_L13, $text);
$AGTime = abs(microtime(true) - $startAG);
print "<div class='smalltext' style='text-align:center'>".str_replace("[TIME]", substr($AGTime, 0, 5), AUTOGAL_LANG_L5)."</div>";
print $autoGalVer;

if (AUTOGAL_SHOW_FOOTER){require_once(FOOTERF);}

?>