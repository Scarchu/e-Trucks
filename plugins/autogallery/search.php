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

$startAG = microtime(true);

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");

if (!$pref['autogal_enablesearch']) {header("location:".AUTOGAL_AUTOGALLERY); exit;}

$searchMatch    = ($_POST['autogal_searchMatch'] ? $_POST['autogal_searchMatch'] : "");
$searchForGal   = ($_POST['autogal_searchForGal'] ? $_POST['autogal_searchForGal'] : ($searchMatch ? 0 : 1));
$searchForImg   = ($_POST['autogal_searchForImg'] ? $_POST['autogal_searchForImg'] : ($searchMatch ? 0 : 1));
$searchForFla   = ($_POST['autogal_searchForFla'] ? $_POST['autogal_searchForFla'] : ($searchMatch ? 0 : 1));
$searchForMov   = ($_POST['autogal_searchForMov'] ? $_POST['autogal_searchForMov'] : ($searchMatch ? 0 : 1));
$searchForAud   = ($_POST['autogal_searchForAud'] ? $_POST['autogal_searchForAud'] : ($searchMatch ? 0 : 1));

$searchGallery  = ($_GET['gallery'] ? rawurldecode($_GET['gallery']) : '');
$searchGallery  = (isset($_POST['autogal_searchGallery']) ? rawurldecode($_POST['autogal_searchGallery']) : $searchGallery);

$searchFieldNam = ($_POST['autogal_searchFieldNam'] ? $_POST['autogal_searchFieldNam'] : ($searchMatch ? 0 : 1));
$searchFieldExt = ($_POST['autogal_searchFieldExt'] ? $_POST['autogal_searchFieldExt'] : 0);
$searchFieldSub = ($_POST['autogal_searchFieldSub'] ? $_POST['autogal_searchFieldSub'] : 0);
$searchFieldDes = ($_POST['autogal_searchFieldDes'] ? $_POST['autogal_searchFieldDes'] : 0);
$searchGo       = $_POST['autogal_searchGo'];

$start = ($_POST['start'] ? $_POST['start'] : 0);
if (isset($_POST['agNextPage']))
{
	$start += $pref['autogal_searchmaxresults'];
}
else if (isset($_POST['agPrevPage']))
{
	$start -= $pref['autogal_searchmaxresults'];
	if ($start < 0) $start = 0;
}
else if (isset($_POST['agFirstPage']))
{
	$start = 0;
}

# e107 integration
$isFromE107 = 0;
if ($query)
{
	$searchMatch = $query;
	$searchGallery = '';
	
	$searchFieldNam = 1;
	$searchFieldExt = 0;
	$searchFieldSub = 0;
	$searchFieldDes = 0;
	
	$searchForGal = 1;
	$searchForImg = 1;
	$searchForFla = 1;
	$searchForMov = 1;
	$searchForAud = 1;
	
	$isFromE107 = 1;
	
	$searchGo = 1;
}

$botLinksList = AutoGal_GetBotLinks('', true, true, true, false);
$botLinks = "<div style='text-align:center'>".(count($botLinksList) > 0 ? "<br />".implode(' ', $botLinksList) : '')."</div>";

$text = '';
if (!$isFromE107)
{
	define("e_PAGETITLE", $pref['autogal_title']. " / " . AUTOGAL_LANG_SEARCH_L1);
	require_once(HEADERF);
	
	$text = "
	<form name='autogal_search' method='post'>
	<input type='hidden' name='start' value='$start'>
	<input type='hidden' name='autogal_searchGo' value='1'>
	<table width='97%' class='border' colspan='2'> 
	<tr> 
		<td class='forumheader3' width='30%'><b>".AUTOGAL_LANG_SEARCH_L2."</b><br /><span class='smalltext'>".str_replace("[MINSEARCHLEN]", AUTOGAL_MINSEARCHSTRLEN, AUTOGAL_LANG_SEARCH_L3)."</span></td>
		<td class='forumheader3' width='70%'><input type='text' name='autogal_searchMatch' size='30' class='tbox' value=\"".htmlspecialchars($searchMatch)."\"></td>
	</tr>
	<tr>
		<td class='forumheader3'><b>".AUTOGAL_LANG_SEARCH_L4."</b><br /><span class='smalltext'>".AUTOGAL_LANG_SEARCH_L5."</span></td>
		<td class='forumheader3'>
			<input type='checkbox' id='autogal_searchForGal' name='autogal_searchForGal'".($searchForGal ? " checked='checked'" : "")."><label for='autogal_searchForGal'>".AUTOGAL_LANG_SEARCH_L6."</label>&nbsp;&nbsp;
			<input type='checkbox' id='autogal_searchForImg' name='autogal_searchForImg'".($searchForImg ? " checked='checked'" : "")."><label for='autogal_searchForImg'>".AUTOGAL_LANG_SEARCH_L7."</label>&nbsp;&nbsp;
			<input type='checkbox' id='autogal_searchForFla' name='autogal_searchForFla'".($searchForFla ? " checked='checked'" : "")."><label for='autogal_searchForFla'>".AUTOGAL_LANG_SEARCH_L8."</label>&nbsp;&nbsp;
			<input type='checkbox' id='autogal_searchForMov' name='autogal_searchForMov'".($searchForMov ? " checked='checked'" : "")."><label for='autogal_searchForMov'>".AUTOGAL_LANG_SEARCH_L9."</label>&nbsp;&nbsp;
			<input type='checkbox' id='autogal_searchForAud' name='autogal_searchForAud'".($searchForAud ? " checked='checked'" : "")."><label for='autogal_searchForAud'>".AUTOGAL_LANG_SEARCH_L30."</label>&nbsp;&nbsp;
		</td>
	</tr>
	<tr>
		<td class='forumheader3'><b>".AUTOGAL_LANG_SEARCH_L10."</b><br /><span class='smalltext'>".AUTOGAL_LANG_SEARCH_L11."</span></td>
		<td class='forumheader3'><select class='tbox' name='autogal_searchGallery'>".AutoGal_GallerySelect($searchGallery)."</select></td>
	</tr>
	<tr>
		<td class='forumheader3'><b>".AUTOGAL_LANG_SEARCH_L12."</b><br /><span class='smalltext'>".AUTOGAL_LANG_SEARCH_L13."</span></td>
		<td class='forumheader3'>
			<input type='checkbox' id='autogal_searchFieldNam' name='autogal_searchFieldNam'".($searchFieldNam ? " checked='checked'" : "")."><label for='autogal_searchFieldNam'>".AUTOGAL_LANG_SEARCH_L14."</label>&nbsp;&nbsp;
			<input type='checkbox' id='autogal_searchFieldExt' name='autogal_searchFieldExt'".($searchFieldExt ? " checked='checked'" : "")."><label for='autogal_searchFieldExt'>".AUTOGAL_LANG_SEARCH_L15."</label>&nbsp;&nbsp;
			".($pref['autogal_xmlsearch'] && !$pref['autogal_authcachesearch'] ? "
			<input type='checkbox' id='autogal_searchFieldDes' name='autogal_searchFieldDes'".($searchFieldDes ? " checked='checked'" : "")."><label for='autogal_searchFieldDes'>".AUTOGAL_LANG_SEARCH_L17."</label>&nbsp;&nbsp;
			<input type='checkbox' id='autogal_searchFieldSub' name='autogal_searchFieldSub'".($searchFieldSub ? " checked='checked'" : "")."><label for='autogal_searchFieldSub'>".AUTOGAL_LANG_SEARCH_L16."</label>&nbsp;&nbsp;
			" : "")."
		</td>
	</tr>
	<tr>
		<td class='forumheader3' colspan='2' style='text-align:center'><input name='agFirstPage' class='button' type='submit' value='".AUTOGAL_LANG_SEARCH_L0."'></td>
	</tr>
	</table>";
}

$resultsText = '';
if ($searchGo)
{
	$searchExts = array();
	if ($searchForGal) $searchExts[] = '';
	if ($searchForImg) $searchExts = array_merge($searchExts, explode('|', AUTOGAL_EXTCLASS_IMAGE));
	if ($searchForFla) $searchExts = array_merge($searchExts, explode('|', AUTOGAL_EXTCLASS_ANIMATION));
	if ($searchForMov) $searchExts = array_merge($searchExts, explode('|', AUTOGAL_EXTCLASS_MOVIE));
	if ($searchForAud) $searchExts = array_merge($searchExts, explode('|', AUTOGAL_EXTCLASS_AUDIO));
	
	$searchTargets = array(); 
	if ($searchFieldNam) $searchTargets[] = 'title';
	if ($searchFieldExt) $searchTargets[] = 'extension';
	if (($searchFieldSub)&&($pref['autogal_xmlsearch'] && !$pref['autogal_authcachesearch'])) $searchTargets[] = 'submitbyusername';
	if (($searchFieldDes)&&($pref['autogal_xmlsearch'] && !$pref['autogal_authcachesearch'])) $searchTargets[] = 'description';
	
	if (strlen(str_replace("*", "", $searchMatch)) < AUTOGAL_MINSEARCHSTRLEN)
	{
		$resultsText .= "<div style='text-align:center'><b>".str_replace("[MINSEARCHLEN]", AUTOGAL_MINSEARCHSTRLEN, AUTOGAL_LANG_SEARCH_L18)."</b></div>";
	}
	elseif (!$searchExts)
	{
		$resultsText .= "<div style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L19."</b></div>";
	}
	elseif (!$searchTargets)
	{
		$resultsText .= "<div style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L20."</b></div>";
	}
	else
	{
		$matchedObjs = AutoGal_SearchMediaObjs($searchGallery, $searchMatch, $searchTargets, $searchExts);
		$numResults = count($matchedObjs);
		
		if ($numResults <= 0)
		{
			$resultsText .= "<div style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L21."</b></div>";
		}
		else
		{
			$matchedObjs = array_slice($matchedObjs, $start, $pref['autogal_searchmaxresults']);
			
			$showNum = count($matchedObjs);
			$showMsg = str_replace("[FIRST]", $start + 1, str_replace("[LAST]", $start + $showNum, str_replace("[TOTAL]", $numResults, AUTOGAL_LANG_SEARCH_L34))); 
			
			$resultsText .= "
			<div style='text-align:center'>
			<b>$showMsg</b>
			</div>
			<br />
			<table class='border' width='97%'>
			<tr>
				<td class='forumheader' style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L33."</b></td>
				<td class='forumheader' style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L23."</b></td>
				<td class='forumheader' style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L24."</b></td>
				<td class='forumheader' style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L25."</b></td>".
				($searchFieldExt ? "<td class='forumheader' style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L26."</b></td>" : '').
				($searchFieldSub ? "<td class='forumheader' style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L27."</b></td>" : '').
				($searchFieldDes ? "<td class='forumheader' style='text-align:center'><b>".AUTOGAL_LANG_SEARCH_L28."</b></td>" : '').
			"</tr>\n";
			
			$count = 0;
			foreach ($matchedObjs as $mediaObj)
			{
				$galleryObj = $mediaObj->GalleryMediaObj();
				
				$title = $mediaObj->Title();
				$extension = $mediaObj->Extension();
				$typeTitle = $mediaObj->TypeTitle();
				$match = $mediaObj->SearchMatch();
				
				if ($match == 'title')
				{
					$title = AutoGal_ReplaceMatchStr($mediaObj, $title);
				}
				else if ($match == 'extension')
				{
					$extension = AutoGal_ReplaceMatchStr($mediaObj, $extension);
				}
				else if ($match == 'description')
				{
					$description = $mediaObj->DescriptionStripBB();
					$description = str_replace("\n", ' ', $description);
					$description = AutoGal_ReplaceMatchStr($mediaObj, $description);
				}
				else if ($match == 'submitbyusername')
				{
					$submitByUsername = $mediaObj->SubmitByUsername();
					$submitByUsername = AutoGal_ReplaceMatchStr($mediaObj, $submitByUsername);
					$submitByUsername = "<a href=\"".$mediaObj->SubmitByUrl()."\">$submitByUsername</a>";
				}
				
				$resultsText .= "
				<tr>
					<td class='forumheader3' style='text-align:center'>".($start + $count + 1).".</td>
					<td class='forumheader3' style='text-align:center'>".
						$mediaObj->ThumbImageHtml(1, 1).$title."
					</td>
					<td class='forumheader3' style='text-align:center'>".
						$galleryObj->TitleLink()."
					</td>
					<td class='forumheader3' style='text-align:center'>$typeTitle</td>".
					($searchFieldExt ? "<td class='forumheader3' style='text-align:center'>$extension</td>" : '').
					($searchFieldSub ? "<td class='forumheader3' style='text-align:center'>$submitByUsername</td>" : '').
					($searchFieldDes ? "<td class='forumheader3' style='text-align:center'>$description</td>" : '').
				"</tr>\n";
				
				$count ++;
			}
			
			$resultsText .= "</table>\n";
			
			$text .= "$botLinks<br /><br />$resultsText";
			$nextButton  = '';
			if ($start + $pref['autogal_searchmaxresults'] < $numResults)
			{
				$nextButton = "<input name='agNextPage' type='submit' class='button' value='".AUTOGAL_LANG_L9."'/>";
			}
			
			if ($start > 0)
			{
				$prevButton = "<input name='agPrevPage' type='submit' class='button' value='".AUTOGAL_LANG_L8."'/>";
				$firstButton = "<input name='agFirstPage' type='submit' class='button' value='".AUTOGAL_LANG_L7."'/>";
			}
						
			if ($nextButton || $prevButton)
			{
				$text .= "<br /><div style='text-align:center'>".($prevButton ? "$firstButton&nbsp;$prevButton&nbsp;" : '')."$nextButton</div>";
			}
		}	
	}
	
	if ($isFromE107) return $resultsText;
}

$text .= "</form>";
$text = "<br />$text$botLinks";

if ($pref['autogal_showautogalver'])
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

$ns->tablerender($pref['autogal_title'].' - '.AUTOGAL_LANG_SEARCH_L1, $text);
$AGTime = microtime(true) - $startAG;
print "<div class='smalltext' style='text-align:center'>".str_replace("[TIME]", substr("$AGTime", 0, 5), AUTOGAL_LANG_L5)."</div>";
print $autoGalVer;

if ($pref['autogal_showfooter']){require_once(FOOTERF);}

function AutoGal_ReplaceMatchStr($mediaObj, $str)
{
	$replace = preg_quote($mediaObj->SearchMatchStr(), "/");
	$with = $mediaObj->SearchMatchStr();
	$with = "<font color='red'><b>$with</b></font>";
	$str = preg_replace("/$replace/im", $with, $str);

	return $str;
}

?>