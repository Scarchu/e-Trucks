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

//

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");  
require_once(AUTOGAL_MEDIAOBJCLASS);

$g_agStartRender = microtime(true);
$g_agRenderTime = 0;
$text = '';

AutoGal_LoadGlobals();
if (!$g_mediaObj->IsValid())
{
	define("e_PAGETITLE", AUTOGAL_TITLE);
	
	$g_agRenderTime += microtime(true) - $g_agStartRender;
	require_once(HEADERF);
	$g_agStartRender = microtime(true);
	
	print "\n<!-- AUTOGALLERY START -->\n\n";
	$text .= "<div style='text-align:center'><br />".AUTOGAL_LANG_L2."<br /><br /><b>".htmlspecialchars($g_element)."</b><br />".$g_mediaObj->LastError()."<br /><br /><a href=\"".AUTOGAL_AUTOGALLERY."\">".AUTOGAL_LANG_L3."</a><br /><br /></div>";
	$ns -> tablerender(AUTOGAL_TITLE, $text);
	if (AUTOGAL_SHOW_FOOTER){require_once(FOOTERF);}
	exit;
}

// DO PAGE HEADER
define("e_PAGETITLE", AUTOGAL_TITLE." : ".$g_mediaObj->PathTitle());

if ((AUTOGAL_SHOWINNEWWINDOW)&&($g_mediaObj->IsFile())&&($g_isNewWindow))
{
	$g_showInNewWindow = true;
	$text .= "\n\n<!-- AUTOGALLERY START -->\n";
	$text .= AutoGal_GetNewWindowHeader(e_PAGETITLE);
}
else
{
	// RENDER e107 HEADER
	$g_agRenderTime += microtime(true) - $g_agStartRender;
	require_once(HEADERF);
	$g_agStartRender = microtime(true);
	
	print "\n\n<!-- AUTOGALLERY START -->\n";

	// RENDER NAVIGATION BAR
	$text .=
	"\n".
	"<br />\n".
	"<table class='border' style='width:97%' align='center'>\n".
	"<tr>\n".
	"<td class='".AUTOGAL_NAVCLASS."' style='text-align:left'>".$g_mediaObj->NavLinks()."</td>\n".
	"</tr>\n".
	"</table>\n".
	"<br />\n";
}

$g_mediaObj->LoadMeta();

// CHECK IF XML FILE WAS OK
if ($g_mediaObj->IsError())
{
	$ns -> tablerender(AUTOGAL_TITLE, "<font color='red'><b>".AUTOGAL_LANG_L38."</b></font> ".$g_mediaObj->LastError());
}

if ($g_isAdminMode) $text .= "<form name='autogallery_admin' method='POST' action='".AUTOGAL_ADMINEDIT."'>\n";

// SHOW GALLERY/IMAGE
if ($g_mediaObj->IsGallery())
{
	$text .= AutoGal_ShowGallery($g_mediaObj, $g_startFile, $g_startGallery, $g_sortOrder);
}
else
{
	$text .= AutoGal_ShowFile($g_mediaObj, $g_showFullImage);
}


if ($g_mediaObj->CheckUserPriv('adminmenu')) $text .= "<div style='text-align:center'>".AutoGal_AdminModeLink($g_mediaObj)."</div>";

$ns -> tablerender(AUTOGAL_TITLE, $text);

// RENDER ADMIN MENU
if (($g_mediaObj->CheckUserPriv('adminmenu'))&&($g_isAdminMode))
{
	require_once(AUTOGAL_ADMINFUNCTIONS);
	AutoGal_ShowAdmin($g_mediaObj, $ns);
	print "</form>\n";
}

require_once(AUTOGAL_RENDERMETA);

// RENDER DESCRIPTION IF ENABLED
AutoGal_RenderDescription($g_mediaObj);

// RENDER ARCADE TOP SCORES
AutoGal_RenderTopScores($g_mediaObj);

// RENDER RATINGS IF ENABLES
AutoGal_RenderRating($g_mediaObj);

// RENDER COMMENTS IF ENABLED
AutoGal_RenderComments($g_mediaObj);

// UPDATE VIEW HITS IF ENABLED
if (AUTOGAL_USEXMLMETAVHITS) $g_mediaObj->ViewHitsInc();

// PRINT VERSION/FOOTER
$g_agRenderTime += microtime(true) - $g_agStartRender;
$g_agRenderTime = number_format(abs($g_agRenderTime), 4);

print "
<div class='smalltext' style='text-align:center'>".
str_replace("[TIME]", $g_agRenderTime, AUTOGAL_LANG_L5).
(((AUTOGAL_SHOWPEAKMEMORY)&&(function_exists('memory_get_peak_usage'))) ? ' '.str_replace("[MEMORY]", AutoGal_FormatBytes(memory_get_peak_usage(true)), AUTOGAL_LANG_L60) : '').
"</div>";

if (AUTOGAL_SHOWAUTOGALVER)
{
	$agVer = AutoGal_GetVersion();
	print 
	"<div class='smalltext' style='text-align:center'>".
	AUTOGAL_LANG_L1.
	"<a target='_blank' href='".AUTOGAL_DOWNLOADURL."'>Auto Gallery</a> v$agVer".
	"</div>";
}

$g_mediaObj->SaveMeta();
	
print "\n<!-- AUTOGALLERY END -->\n\n";

if ($g_showInNewWindow)
{
	print AutoGal_GetNewWindowFooter();
}
elseif (AUTOGAL_SHOW_FOOTER)
{
	require_once(FOOTERF);
}


// SHOWS A SINGLE IMAGE
function AutoGal_ShowFile(&$mediaObj, $showFullImage)
{
	$element = $mediaObj->Element();
	$gallery = $mediaObj->Gallery();
    $title = $mediaObj->Title();
	$subTitle = $mediaObj->SubTitle();
	
	$botLinks = AutoGal_GetBotLinks($gallery);
	$botLinks = (count($botLinks) > 0 ? "<br />".implode(' ', $botLinks) : '');
	
	# Check user's class
    if (!$mediaObj->CheckUserPriv('view'))
    {
        return ("<div style='text-align:center'><b>".AUTOGAL_LANG_L6."</b><br />$botLinks</div>");    
    }
	
	$emailLink = $mediaObj->EmailLink();
	$navButtons = $mediaObj->NavButtons(AUTOGAL_SLIDESENABLE, AUTOGAL_SHOWINNEWWINDOW); 
	
	require_once(AUTOGAL_RENDERFILE);
	$previewHTML = AutoGal_RenderFileObj($mediaObj, $showFullImage);
	
	// Arcade top score update
	if ($mediaObj->FileType() == 'flash')
	{
		if ((AUTOGAL_ARCADEUSEXMLTRACK)&&(AUTOGAL_ARCTOPSCORES))
		{
			require_once (AUTOGAL_ARCADEPLAYERS);
			$players = new AutoGal_ArcadePlayers(AUTOGAL_ARCADEPLAYERSXML);
			
			if (file_exists(AUTOGAL_ARCADEPLAYERSXML))
			{
				if (!$players->Open())
				{
					$text .= $players->LastError()."<br />";
				}
			}
			
			$players->UpdatePlayer(USERID, USERNAME, $mediaObj->Element());
			$players->DeleteOldPlayers(AUTOGAL_ARCADEMAXPLAYERXMLTIME);
			
			if (!$players->Save(AUTOGAL_PERMSCFGXML))
			{
				$text .= $players->LastError()."<br />";
			}
		}
	}
	
	// Update/Show XML Statistics
	$hitStat = "";
	
	if ((AUTOGAL_USEXMLMETAVHITS)&&(AUTOGAL_USEXMLMETAEHITS))
	{
		$hitStat = AUTOGAL_LANG_L28;
		$hitStat = str_replace("[TYPE]", $mediaObj->FileTypeTitle(), $hitStat);
		$hitStat = str_replace("[HITS]", ($mediaObj->ViewHits() ? $mediaObj->ViewHits() : 1), $hitStat);
		$hitStat = str_replace("[TIMES]", ($mediaObj->ViewHits() == 1 ? AUTOGAL_LANG_L32 : AUTOGAL_LANG_L33), $hitStat);
		
		$hitStat .= AUTOGAL_LANG_L29.AUTOGAL_LANG_L30;
		$hitStat = str_replace("[HITS]", ($mediaObj->EmailHits() ? $mediaObj->EmailHits() : 0), $hitStat);
		$hitStat = str_replace("[TIMES]", ($mediaObj->EmailHits() == 1 ? AUTOGAL_LANG_L32 : AUTOGAL_LANG_L33), $hitStat);
	}
	else if (AUTOGAL_USEXMLMETAVHITS)
	{
		$hitStat = AUTOGAL_LANG_L28;
		$hitStat = str_replace("[TYPE]", $mediaObj->FileTypeTitle(), $hitStat);
		$hitStat = str_replace("[HITS]", ($mediaObj->ViewHits() ? $mediaObj->ViewHits() : 1), $hitStat);
		$hitStat = str_replace("[TIMES]", ($mediaObj->ViewHits() == 1 ? AUTOGAL_LANG_L32 : AUTOGAL_LANG_L33), $hitStat);
	}
	else if (AUTOGAL_USEXMLMETAEHITS)
	{
		$hitStat = AUTOGAL_LANG_L59;
		$hitStat = str_replace("[TYPE]", $mediaObj->FileTypeTitle(), $hitStat);
		$hitStat = str_replace("[HITS]", ($mediaObj->EmailHits() ? $mediaObj->ViewHits() : 0), $hitStat);
		$hitStat = str_replace("[TIMES]", ($mediaObj->EmailHits() == 1 ? AUTOGAL_LANG_L32 : AUTOGAL_LANG_L33), $hitStat);
	}
	
	$hitStat = ($hitStat ? "$hitStat.<br />" : '');
	
	$submitStat = "";
	if (AUTOGAL_SHOWSUBMITINFO)
	{
		if ($mediaObj->SubmitDate() > 0)
		{
			$submitStat = AUTOGAL_LANG_L31;
			
			if ($mediaObj->SubmitByUserID() == 0)
			{
				$subUser = AUTOGAL_LANG_L34;
			}
			else
			{
				$subUser = "<a href=\"".e_BASE."user.php?id.".$mediaObj->SubmitByUserID()."\">".$mediaObj->SubmitByUsername()."</a>";
			}
			
			$submitStat = str_replace("[USER]", $subUser, $submitStat);
			$submitStat = str_replace("[DATE]", strftime(AUTOGAL_SUBMITTIMEFORMAT, $mediaObj->SubmitDate()), $submitStat);
			$submitStat .= "<br />";
		}
	}
		
	$stats = $hitStat.$submitStat;
	$stats = ($stats ? "$stats<br />" : '');
	$emailLink = ($emailLink ? "<br />$emailLink<br />" : '');
	$subTitle = ($mediaObj->SubTitle() ? $mediaObj->SubTitle()."<br />" : '');
	$title = "<h".AUTOGAL_TITLEHEADSTYLE.">$title</h".AUTOGAL_TITLEHEADSTYLE.">";
		
	$text = 
	"<div style='text-align:center'>\n".
    "$title\n".
	"$subTitle\n".
	"<br />\n".
    "$previewHTML\n".
    "<br />\n".
	"$stats\n".
    "$navButtons<br />\n".
    "$emailLink\n".
    "$botLinks\n".
    "</div>\n";
	
	return $text;
}

function AutoGal_ShowGallery(&$mediaObj)
{
	$gallery = $mediaObj->Element();
	
	$botLinks = AutoGal_GetBotLinks($gallery);
	$botLinks = (count($botLinks) > 0 ? implode(' ', $botLinks) : '');
	
	# Check view class
    if (!$mediaObj->CheckUserPriv('view'))
    {
        return ("<div style='text-align:center'><b>".AUTOGAL_LANG_L15."</b><br />$botLinks</div>");    
    }
	
	if ((AUTOGAL_USERGALENABLE)&&(AutoGal_IsUserGalleryAllowed())&&($mediaObj->IsUserGalleryRoot()))
	{
		$userGallery = AutoGal_UserGallery(USERNAME, $mediaObj);
		
		$text .= "<table class='border' align='center'>\n".
		"<tr><td style='text-align:center' class='".AUTOGAL_USERGALTOPCAPCLASS."'>".AUTOGAL_LANG_USERGALS_L1."</td></tr>\n".
		"<tr><td style='text-align:center' class='".AUTOGAL_SUBGALLERYCLASS."'>";
			
		if ($userGallery)
		{
			$text .= $userGallery->ThumbAndTitleHtml(0, 1);
		}
		else
		{
			$text .= "<a href=\"".AUTOGAL_CREATEUSERGALLERY."?user=".htmlspecialchars(USERNAME)."\">".AUTOGAL_LANG_USERGALS_L9."</a>";
		}
		
		$text .= "</td></tr>\n</table><br />";
	}
	
	$nextEditID = 0;
  	$mediaGalleryHtml = AutoGal_RenderGallerySubGals($mediaObj, $nextEditID);
	$mediaFileHtml = AutoGal_RenderGalleryFiles($mediaObj, $nextEditID);
    
    $newImagesTable = '';
	if (($mediaObj->IsRoot())&&(AUTOGAL_SHOWNEWESTINROOT))
	{
		$newFilesTable = AutoGal_RenderLatestFiles();
	}
	
	$text .= 
	"\n".
	"<script type='text/javascript'>var ag_ignoreClick = 0;</script>\n".
	"<div style='text-align:center'>\n".
	($mediaObj->SubTitle() ? $mediaObj->SubTitle()."<br /><br />" : '');
	
	if ((!$mediaGalleryHtml)&&(!$mediaFileHtml))
	{
		$text .= "<b>".AUTOGAL_LANG_L16."</b><br /><br />";
	}
	else
	{
		$text .= $mediaGalleryHtml;
		$text .= $mediaFileHtml;
		
		$sortOrderText = AutoGal_SortOrderText($mediaObj);
		if ($sortOrderText)
		{
			$text .= $sortOrderText."<br /><br />";
		}
	}
	
	if ($newFilesTable)	$text .= $newFilesTable;
	$text .= "$botLinks</div>";

    return $text;
}

function AutoGal_RenderGallerySubGals(&$mediaObj, &$nextEditID)
{
	global $g_startGallery;
	global $g_isAdminMode;
	
    $galObjs = $mediaObj->ChildMediaObjs($sortOrder);
	$totalGals = count($galObjs['galleries']);
	if (!$totalGals) return;
    
    $divCellBy = ($totalGals < AUTOGAL_NUMGALLCOLS ? $totalGals : AUTOGAL_NUMGALLCOLS);
    	
	$galleryI = 0; 
	$colCount = 0;
	$rowCount = 0;
	$numShown = 0;
	foreach ($galObjs['galleries'] as $subMediaObj)
	{
		if ($galleryI >= $g_startGallery)
		{
			if ((!AUTOGAL_NOFILEVALIDATION)&&(!$subMediaObj->IsValid())) continue;
			if ((AUTOGAL_CHECKSUBGALVCLASS)&&(!$subMediaObj->CheckUserPriv('view'))) continue;
			
			$text .= AutoGal_RenderSubGalleryCell($subMediaObj, $nextEditID, $divCellBy);
			
			$colCount ++;
			if ($colCount == AUTOGAL_NUMGALLCOLS)
			{
				$text .= "</tr>\n<tr>\n";
				$colCount = 0;
				$rowCount ++;
			}
			
			$numShown ++;
		}
			
		$galleryI ++;
		$nextEditID ++;
		
		if ((AUTOGAL_MAXGALSPERPAGE)&&($numShown >= AUTOGAL_MAXGALSPERPAGE)) break;
	}
	
	if (!$numShown) return $text;
	
	if ($colCount == 0)
	{
		$text = substr($text, 0, strlen($text) - 11);
	}
	
	while (($rowCount > 0)&&($colCount)&&($colCount < AUTOGAL_NUMGALLCOLS))
	{
		$text .= "<td class='".AUTOGAL_SUBGALLERYCLASS."'>&#160;</td>";
		$colCount ++;
	}
	
	$text = "<tr>$text</tr>";
	
	if (AUTOGAL_SHOWSUBGALTOPCAP)
	{
		$topCapText = 
		"<tr>\n".
		"<td colspan='".AUTOGAL_NUMGALLCOLS."' class='".AUTOGAL_SUBGALTOPCAPCLASS."' style='text-align:center'>".AUTOGAL_LANG_L66."</td>\n".
		"</tr>\n";
	}
	
	if (AUTOGAL_MAXGALSPERPAGE) $botCapText = AutoGal_RenderBottomCap($mediaObj, $totalGals, $galleryI, 'gallery');
	
	$text = 
	"<table class='border' style='width:97%'>\n".
	$topCapText.
	$text.
	$botCapText.
	"</table>\n".
	"<br />\n";
		
	return $text;
}

function AutoGal_RenderGalleryFiles(&$mediaObj, &$nextEditID)
{
	global $g_startFile;
	global $g_isAdminMode;
	
	$showDate = 0;
	if ((AUTOGAL_SHOWDATEORDNAME)&&(($sortOrder == 'nameasc')||($sortOrder == 'namedsc')))
	{
		$showDate = 1;
	}
	else if ((AUTOGAL_SHOWDATEORDDATE)&&(($sortOrder == 'dateasc')||($sortOrder == 'datedsc')))
	{
		$showDate = 1;
	}
	
	$galObjs = $mediaObj->ChildMediaObjs($sortOrder);
	$totalFiles = count($galObjs['files']);
	$divCellBy = ($totalFiles < AUTOGAL_NUMCOLS ? $totalFiles : AUTOGAL_NUMCOLS);
    if (!$totalFiles) return;
	
	$fileIndex = 0;
	$colCount = 0;
	$rowCount = 0;
	$numShown = 0;
	foreach ($galObjs['files'] as $subMediaObj)
	{
		if ((!AUTOGAL_NOFILEVALIDATION)&&(!$subMediaObj->IsValid())) continue;
		
		if ($fileIndex >= $g_startFile)
		{
			$text .= AutoGal_RenderMediaFileCell($subMediaObj, $nextEditID, $divCellBy, $showDate);
			
			$colCount ++;
			if ($colCount == AUTOGAL_NUMCOLS)
			{
				$text .= "</tr><tr>";
				$colCount = 0;
				$rowCount ++;
			}
			
			$numShown ++;
			
			if ((AUTOGAL_MAXPERPAGE)&&($numShown >= AUTOGAL_MAXPERPAGE)) break;
		}
		
		$fileIndex ++;
		$nextEditID ++;
	}
	
	$pageList = '';
	if ($numShown <= 0) return $text;
	
	if ($colCount == 0)
	{
		$text = preg_replace("/\<\/tr\>\<tr\>$/", "", $text);
	}
	
	# Finish of remaining cells with blank ones
	while (($rowCount > 0)&&($colCount)&&($colCount < AUTOGAL_NUMCOLS))
	{
		$text .= "<td class='".AUTOGAL_IMAGECELLCLASS."'>&#160;</td>";
		$colCount ++;
	}
	
	$text = "<tr>$text</tr>";
	
	# Create the top cap cell
	if (AUTOGAL_SHOWFILETOPCAP)
	{
		$topCapText = 
		"\n".
		"<tr>\n".
		"<td colspan='".AUTOGAL_NUMCOLS."' class='".AUTOGAL_FILETOPCAPCLASS."' style='text-align:center'>".AUTOGAL_LANG_L67."</td>\n".
		"</tr>\n";
	}
	
	if (AUTOGAL_MAXPERPAGE) $botCapText = AutoGal_RenderBottomCap($mediaObj, $totalFiles, $fileIndex, 'file');
		
	$text = 
	"<table class='border' style='width:97%'>\n".
	"$topCapText\n".
	"$text\n".
	"$botCapText\n".
	"</table>\n".
	"<br />\n";
	
	return $text;
}

function AutoGal_RenderSubGalleryCell($subMediaObj, $nextEditID, $divCellBy)
{
	global $g_isAdminMode;
	
	if ($g_isAdminMode)
	{
		$adminCellJS = "onclick=\"if (!ag_ignoreClick) {javascipt:document.getElementById('ag_ele$nextEditID').checked = !document.getElementById('ag_ele$nextEditID').checked;  if (document.getElementById('ag_adminselection').selectedIndex == 0) {document.getElementById('ag_adminselection').selectedIndex=1;}} ag_ignoreClick=0;\"";
		$adminChkBoxH = 
		"<table style='border:none;width:100%'>\n".
		"<tr>\n".
		"<td style='text-align:left;border:none;width:1%'>\n".
		"<input type='checkbox' name=\"".AutoGal_HtmlVar("ag_ele_".$subMediaObj->Element())."\" style='visibility:visible' id='ag_ele$nextEditID' onclick=\"ag_ignoreClick=1; if (document.getElementById('ag_adminselection').selectedIndex == 0) {document.getElementById('ag_adminselection').selectedIndex=1;};\">\n".
		"</td>\n".
		"<td style='text-align:center;border:none'>\n";
		$adminChkBoxF = "</td></tr></table>\n";
	}
	
	$text = 
	"<td class='".AUTOGAL_SUBGALLERYCLASS."' style='text-align:center;width:".sprintf("%0.0f", (100 / $divCellBy))."%'$adminCellJS>\n".
	$adminChkBoxH.
	$subMediaObj->ThumbImageHtml(1, 1)."\n".
	$subMediaObj->TitleLink()."\n".
	($subMediaObj->SubTitle() && AUTOGAL_SHOWSUBTITLESGAL ? "<br />\n<span class='smalltext'>(".$subMediaObj->SubTitle().")</span>\n" : '').
	$adminChkBoxF.
	"</td>\n";
	
	return $text;
}

function AutoGal_RenderMediaFileCell($subMediaObj, $nextEditID, $divCellBy, $showDate)
{
	global $g_isAdminMode;
	
	if ($g_isAdminMode)
	{
		$adminCellJS = "onclick=\"if (!ag_ignoreClick) {javascipt:document.getElementById('ag_ele$nextEditID').checked = !document.getElementById('ag_ele$nextEditID').checked;  if (document.getElementById('ag_adminselection').selectedIndex == 0) {document.getElementById('ag_adminselection').selectedIndex=1;}} ag_ignoreClick=0;\"";
		$adminChkBoxH = 
		"<table style='border:none;width:100%'>\n".
		"<tr>\n".
		"<td style='text-align:left;border:none;width:1%'>\n".
		"<input type='checkbox' name=\"".AutoGal_HtmlVar("ag_ele_".$subMediaObj->Element())."\" style='visibility:visible' id='ag_ele$nextEditID' onclick=\"ag_ignoreClick=1; if (document.getElementById('ag_adminselection').selectedIndex == 0) {document.getElementById('ag_adminselection').selectedIndex=1;};\">\n".
		"</td>\n".
		"<td style='text-align:center;border:none'>\n";
		
		$adminChkBoxF = "</td>\n</tr>\n</table>\n";
	}
	
	$text = 
	"<td class='".AUTOGAL_IMAGECELLCLASS."' style='text-align:center;width:".sprintf("%0.0f", (100 / $divCellBy))."%' $adminCellJS>\n".
		$adminChkBoxH.
		$subMediaObj->ThumbImageHtml(1, 1).
		(AUTOGAL_SHOWTITLEINGALL ? 
			"\n".$subMediaObj->TitleLink()."\n".
			($subMediaObj->SubTitle() && AUTOGAL_SHOWSUBTITLESGAL ? "<br /><span class='smalltext'>(".$subMediaObj->SubTitle().")</span>" : '')
		: '').
		($showDate ? "<br /><br />\n<span class='smalltext'>".strftime(AUTOGAL_THUMBTIMEFORMAT, $subMediaObj->UpdateTime())."</span>" : '').
		$adminChkBoxF.
	"</td>\n";
	
	return $text;
}

function AutoGal_RenderBottomCap($mediaObj, $total, $index, $type)
{
	global $g_startFile;
	global $g_startGallery;
	
	if ($type == 'gallery')
	{
		$max = AUTOGAL_MAXPERPAGE;
		$styleClass = AUTOGAL_SUBGALBOTCAPCLASS;
		$numCols = AUTOGAL_NUMGALLCOLS;
		$start = $g_startGallery;
		$startVar = 'startgal';
	}
	else
	{
		$max = AUTOGAL_MAXPERPAGE;
		$styleClass = AUTOGAL_FILEBOTCAPCLASS;
		$numCols = AUTOGAL_NUMCOLS;
		$start = $g_startFile;
		$startVar = 'start';
	}
		
	if ($start)
	{
		$prevPage = $start - $max;
		$prevPage = ($prevPage >= 0 ? $prevPage : 0);
		$prevLink = "<a href=\"".$mediaObj->BackLink(array($startVar => $prevPage))."\">".AUTOGAL_LANG_L8."</a>";
		$isLimted = 1;
	}
	else
	{
		$prevLink = AUTOGAL_LANG_L8;
	}
	
	if ($index < $total)
	{
		$nextPage = $start + $max;
		$nextLink ="<a href=\"".$mediaObj->BackLink(array($startVar => $nextPage))."\">".AUTOGAL_LANG_L9."</a>";
		$isLimted = 1;
	}
	else
	{
		$nextLink = AUTOGAL_LANG_L9;
	}
	
	$capText = AutoGal_ThumbnailPageList($mediaObj, $type, $total);
	
	if ($isLimted)
	{
		$text = 
		"<tr>\n".
		"<td colspan='$numCols' class='$styleClass' style='text-align:center'>\n".
		"<span style='clear:both;width:100%'>\n".
		"<span style='vertical-align:middle;float:left;text-align:left'>$prevLink</span>\n".
		"<span style='vertical-align:middle;float:right;text-align:right'>$nextLink</span>\n".
		"$capText\n".
		"</span>\n".
		"</tr>\n";
	}
	
	return $text;
}

function AutoGal_ThumbnailPageList($mediaObj, $type, $total)
{
	global $g_startFile;
	global $g_startGallery;
	
	if ($type == 'gallery')
	{
		$maxPerPage = AUTOGAL_MAXGALSPERPAGE;
		$start = $g_startGallery;
		$startVar = 'startgal';
	}
	else
	{
		$maxPerPage = AUTOGAL_MAXPERPAGE;
		$start = $g_startFile;
		$startVar = 'start';
	}
	
	$numPages = ceil($total / ($maxPerPage ? $maxPerPage : 1));
	$currPage = floor($start / ($maxPerPage ? $maxPerPage : 1));
	$showNum = ($total - $start > $maxPerPage ? $maxPerPage : $total - $start);
	
	#$text = str_replace("[TOTALIMAGES]", $total, str_replace("[IMAGERANGE]", ($start + 1).'-'.($start + $showNum), AUTOGAL_LANG_L17))."<br />";
	
	if ($numPages > 1)
	{
		$maxDist = (AUTOGAL_PAGEMAXDIST ? AUTOGAL_PAGEMAXDIST : $numPages);
		$pages = array();
		
		# FIRST PAGES
		$dist = 0;
		for ($pageNum = 1; (($pageNum < $numPages)&&($dist < $maxDist)); $pageNum ++)
		{
			$pages[$pageNum] = 1;
			$dist ++;
		}
		
		# LAST PAGES
		$dist = 0;
		for ($pageNum = $numPages - 2; (($pageNum > 0)&&($dist < $maxDist)); $pageNum --)
		{
			$pages[$pageNum] = 1;
			$dist ++;
		}
					
		# NEXT PAGES
		$dist = 0;
		for ($pageNum = $currPage + 1; (($pageNum < $numPages)&&($dist < $maxDist)); $pageNum ++)
		{
			$pages[$pageNum] = 1;
			$dist ++;
		}
		
		# PREV PAGES
		$dist = 0;
		for ($pageNum = $currPage - 1; (($pageNum > 0)&&($dist < $maxDist)); $pageNum --)
		{
			$pages[$pageNum] = 1;
			$dist ++;
		}
		
		$pages[$currPage] = 1;
		$pages[0] = 1;
		$pages[$numPages - 1] = 1;
		
		# WORK OUT SEPERATOR POSITIONS
		for ($pageI = 0; $pageI < $numPages; $pageI ++)
		{
			if (!$pages[$pageI])
			{
				$pages[$pageI] = 0;
			}
		}
		
		ksort($pages);
	
		$sepDone = 0;
		#$text .= AUTOGAL_LANG_L18;
		foreach ($pages as $pageNum => $val)
		{
			if ($val == 1)
			{
				if ($pageNum == $currPage)
				{
					$text .= "<b>".($pageNum + 1)."</b>&#160;";    
				}
				else
				{
					$pageStart = $pageNum * $maxPerPage;
					$text .= "<a href=\"".$mediaObj->BackLink(array($startVar => $pageStart))."\">".($pageNum + 1)."</a>&#160;";
				}
				
				$sepDone = 0;
			}
			else if (!$sepDone)
			{
				$text .= "... ";
				$sepDone = 1;
			}
		}
	}
	
	return $text;
}

function AutoGal_RenderLatestFiles($startFile)
{
	$newFiles = AutoGal_GetLatestFiles(AUTOGAL_NUMCOLS, 0);
	if (count($newFiles) <= 0) return '';
	
	$text = 
	"<table style='width:97%' class='border'>\n".
	"<tr>\n".
	"<td class='".AUTOGAL_LATESTTOPCAPCLASS."' colspan='".AUTOGAL_NUMCOLS."' style='text-align:center'>\n".
	AUTOGAL_LANG_STAT_L12."\n".
	"</td>\n".
	"</tr>\n".
	"<tr>\n";
	
	$newFileCount = 0;
	foreach ($newFiles as $subMediaObj)
	{
		$text .= 
		"<td class='".AUTOGAL_IMAGECELLCLASS."' style='text-align:center;width:".sprintf("%0.0f", (100 / AUTOGAL_NUMCOLS))."%'>\n".
			$subMediaObj->ThumbImageHtml(0, 1).
			(AUTOGAL_SHOWTITLEINGALL ? 
				"<br />\n".$subMediaObj->TitleLink()."\n".
				($subMediaObj->SubTitle() && AUTOGAL_SHOWSUBTITLESGAL ? "<br /><span class='smalltext'>(".$subMediaObj->SubTitle().")</span>" : '')
			: '').
			"<br /><br />\n<span class='smalltext'>".strftime(AUTOGAL_THUMBTIMEFORMAT, $subMediaObj->UpdateTime())."</span>".
		"</td>\n";
		
		$newFileCount ++;
	}
	
	while ($newFileCount < AUTOGAL_NUMCOLS)
	{
		$text .= "<td class='".AUTOGAL_IMAGECELLCLASS."' style='width:".sprintf("%0.0f", (100 / AUTOGAL_NUMCOLS))."%'>&#160;</td>";
		$newFileCount ++;
	}
	
	$text .= "</tr></table><br />";
		
	return $text;
}

function AutoGal_SortOrderText($mediaObj)
{
	if (!AUTOGAL_ENABLEGALDISPORD) return;
	
	global $g_sortOrder;
	
	$srtOrdLnk{'nameasc'} = AUTOGAL_LANG_L62;
	$srtOrdLnk{'namedsc'} = AUTOGAL_LANG_L63;
	$srtOrdLnk{'datedsc'} = AUTOGAL_LANG_L64;
	$srtOrdLnk{'dateasc'} = AUTOGAL_LANG_L65;
	
	if (!$srtOrdLnk{$g_sortOrder})
	{
		$g_sortOrder = AUTOGAL_DEFAULTDISPORD;
	}
	
	foreach ($srtOrdLnk as $lstSrtOrd => $srtOrdTitle)
	{
		if ($g_sortOrder == $lstSrtOrd)
		{
			$srtOrdLnk{$lstSrtOrd} = "<b>$srtOrdTitle</b>";
		}
		else
		{
			$srtOrdLnk{$lstSrtOrd} = "<a href=\"".$mediaObj->Link("start=0&amp;order=$lstSrtOrd")."\">$srtOrdTitle</a>";
		}
	}
	
	$srtOrdText = AUTOGAL_LANG_L61;
	foreach ($srtOrdLnk as $lstSrtOrd => $srtOrdTitle)
	{
		$srtOrdText = str_replace("[".strtoupper($lstSrtOrd)."]", $srtOrdTitle, $srtOrdText);
	}
	
	return $srtOrdText;		
}
?>
