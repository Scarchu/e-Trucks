<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        04/08/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_MEDIAOBJCLASS);
require_once(AUTOGAL_RENDERFILE);

$startAG = microtime(true);

if (!$pref['autogal_slidesenable']) {header("location:".AUTOGAL_AUTOGALLERY); exit;}

$startImg = stripslashes(rawurldecode(($_GET['first'] ? $_GET['first'] : $_POST['first'])));
$slideImg = stripslashes(rawurldecode(($_GET['slide'] ? $_GET['slide'] : $_POST['slide'])));
$status = ($_GET['status'] ? $_GET['status'] : $_POST['status']);
$changeTime = ($_GET['chtime'] ? $_GET['chtime'] : $_POST['chtime']);
$repeatOn = (($_GET['repeat'] ? $_GET['repeat'] : $_POST['repeat']) ? 1 : 0);
$showFullImg = (($_GET['fullimg'] ? $_GET['fullimg'] : $_POST['fullimg']) ? 1 : 0);

$startObj = new AutoGal_CMediaObj($startImg);
if (!$startObj->IsValid())
{
	print "Invalid start element: ".htmlspecialchars($startImg)." (".$startObj->LastError().")";
	exit;
}

define("e_PAGETITLE", $pref['autogal_title']. " / " . AUTOGAL_LANG_SLIDESHOW_L1);

if ($status)
{
	$slideObj = new AutoGal_CMediaObj($slideImg);
	if (!$slideObj->IsValid())
	{
		print "Invalid slide element: ".htmlspecialchars($slideObj)." (".$slideObj->LastError().")";
		exit;
	}
	
	if (!preg_match("/^\d+$/", $changeTime))
	{
		print "Invalid change time: ".htmlspecialchars($changeTime);
		exit;
	}
	
	if ($startObj->Gallery() != $slideObj->Gallery())
	{
		print "Galleries do not match: ".htmlspecialchars($startObj->Gallery())."/".htmlspecialchars($slideObj->Gallery());
		exit;
	}

	if (($status == 2)&&(!$repeatOn)&&($startObj->Element() == $slideObj->Element()))
	{
		print AutoGal_SlideShowEnd($startObj);
	}
	else
	{
		print AutoGal_SlideShowNext($startObj, $slideObj, $changeTime, $repeatOn, $showFullImg);
	}
}
else
{
	print AutoGal_SlideShowStart($startObj);
}

function AutoGal_SlideShowNext($startObj, $slideObj, $changeTime, $repeatOn, $showFullImg)
{
	$gallObj = $slideObj->GalleryMediaObj();
	$nextObj = $slideObj->NextMediaObj();
	$prevObj = $slideObj->PrevMediaObj();
	
	$prevURL = AUTOGAL_SLIDESHOW."?first=".rawurlencode($startObj->Element())."&slide=".rawurlencode($prevObj->Element())."&chtime=$changeTime&repeat=$repeatOn&status=2&fullimg=$showFullImg";
	$nextURL = AUTOGAL_SLIDESHOW."?first=".rawurlencode($startObj->Element())."&slide=".rawurlencode($nextObj->Element())."&chtime=$changeTime&repeat=$repeatOn&status=2&fullimg=$showFullImg";
			
	$title = "<h".$pref['autogal_titleheadstyle'].">".$slideObj->Title()."</h".$pref['autogal_titleheadstyle'].">";
	$subTitle = ($slideObj->SubTitle() ? $slideObj->SubTitle()."<br />" : '');
	$fileShow = AutoGal_RenderFileObj($slideObj, $showFullImg, true, true);
	
    $prevFile = "<input type='button' title=\"".$prevObj->Title()."\" class='button' value='".AUTOGAL_LANG_L8."' onclick='javascript:window.location.href=\"$prevURL\"' />";
    $nextFile = "<input type='button' title=\"".$nextObj->Title()."\" class='button' value='".AUTOGAL_LANG_L9."' onclick='javascript:window.location.href=\"$nextURL\"' />";
	
	if ($pref['autogal_slidesnewwindow'])
	{
		$backLink = "<input type='button' class='button' value='".AUTOGAL_LANG_L25."' onClick='javascript:window.close()'>";
	}
	else
	{
		$backLink = "<input type='button' title=\"".AUTOGAL_LANG_L13.$gallObj->Title()."\" class='button' value='".AUTOGAL_LANG_L11."' onclick='javascript:window.location.href=\"".$gallObj->BackLink()."\"' />";
	}
	
	$fileText = "
	$title
	$subTitle
	$fileShow<br />
	$prevFile&#160;$backLink&#160;$nextFile";

	$text = AutoGal_GetNewWindowHeader(e_PAGETITLE, "<meta http-equiv=\"refresh\" content=\"$changeTime; URL=$nextURL\">", $pref['autogal_slidebodyclass'], $pref['autogal_slidebodystyle']);
	$text .= $fileText;
	$text .= AutoGal_GetNewWindowFooter();
	
	return $text;
}

function AutoGal_SlideShowEnd($startObj)
{
	$gallObj = $startObj->GalleryMediaObj();
	
	$text = AutoGal_GetNewWindowHeader(e_PAGETITLE, "", $pref['autogal_slidebodyclass'], $pref['autogal_slidebodystyle']);
	$text .= "<br />";
	$text .= "<h2>".AUTOGAL_LANG_SLIDESHOW_L14."</h2><br />";
	
	if ($pref['autogal_slidesnewwindow'])
	{
		$text .= "<input type='button' class='button' value='".AUTOGAL_LANG_SLIDESHOW_L16."' onClick='javascript:window.close()'><br />";
	}
	else
	{
		$text .= "<input type='button' class='button' value='".AUTOGAL_LANG_SLIDESHOW_L15."' onClick='javascript:window.location.href=\"".$gallObj->Link()."\"'><br />";
	}
	
	$text .= "<br />";
	$slideShowURL = AUTOGAL_SLIDESHOW."?first=".htmlspecialchars($startObj->Element());
	$text .= "<input type='button' class='button' value='".AUTOGAL_LANG_SLIDESHOW_L17."' onClick='javascript:window.location.href=\"$slideShowURL\"'><br />";
	
	$text .= AutoGal_GetNewWindowFooter();
	
	return $text;
}

function AutoGal_SlideShowStart($mediaObj)
{
	$galleryObj = $mediaObj->GalleryMediaObj();
	
	$text = AutoGal_GetNewWindowHeader(e_PAGETITLE, "", $pref['autogal_slidebodyclass'], $pref['autogal_slidebodystyle']);
	
	if ($pref['autogal_slidesnewwindow'])
	{
		$backLink = "<input type='button' class='button' value='".AUTOGAL_LANG_L25."' onClick='javascript:window.close()'>";
	}
	else
	{
		$backLink = "<input type='button' title=\"".AUTOGAL_LANG_L13.$galleryObj->Title()."\" class='button' value='".AUTOGAL_LANG_L11."' onclick='javascript:window.location.href=\"".$galleryObj->BackLink()."\"' />";
	}
	
	$text .= "
	<br />
	<form method='GET'>
	<table class='fborder' colspan='2'>
	<tr>
		<td colspan='2' class='forumheader'><b>".AUTOGAL_LANG_SLIDESHOW_L1."</b></td>
	</tr>
	<tr>
		<td style='width:50%' class='forumheader3'>".AUTOGAL_LANG_SLIDESHOW_L3."</td>
		<td style='width:50%' class='forumheader3'><a href=\"".$galleryObj->BackLink()."\">".$galleryObj->Title()."</a></td>
	</tr>
	<tr>
		<td style='width:50%' class='forumheader3'>".AUTOGAL_LANG_SLIDESHOW_L4."</td>
		<td style='width:50%;text-align:center' class='forumheader3'>
			<a href=\"".$mediaObj->BackLink()."\">".$mediaObj->ThumbImageHtml()."</a><br />
			<a href=\"".$mediaObj->BackLink()."\">".$mediaObj->Title()."</a>
		</td>
	</tr>
	<tr>
		<td style='width:50%' class='forumheader3'>".AUTOGAL_LANG_SLIDESHOW_L5."</td>
		<td style='width:50%' class='forumheader3'>
			<select name='chtime' class='tbox'>
			<option value='5'>".str_replace("[TIME]", 5, AUTOGAL_LANG_SLIDESHOW_L9)."</option>
			<option value='10'>".str_replace("[TIME]", 10, AUTOGAL_LANG_SLIDESHOW_L9)."</option>
			<option value='15'>".str_replace("[TIME]", 15, AUTOGAL_LANG_SLIDESHOW_L9)."</option>
			<option value='20'>".str_replace("[TIME]", 20, AUTOGAL_LANG_SLIDESHOW_L9)."</option>
			<option value='30'>".str_replace("[TIME]", 30, AUTOGAL_LANG_SLIDESHOW_L9)."</option>
			<option value='60'>".str_replace("[TIME]", 1, AUTOGAL_LANG_SLIDESHOW_L10)."</option>
			<option value='90'>".str_replace("[TIME]", 1.5, AUTOGAL_LANG_SLIDESHOW_L11)."</option>
			<option value='120'>".str_replace("[TIME]", 2, AUTOGAL_LANG_SLIDESHOW_L11)."</option>
			<option value='180'>".str_replace("[TIME]", 3, AUTOGAL_LANG_SLIDESHOW_L11)."</option>
			<option value='240'>".str_replace("[TIME]", 4, AUTOGAL_LANG_SLIDESHOW_L11)."</option>
			<option value='300'>".str_replace("[TIME]", 5, AUTOGAL_LANG_SLIDESHOW_L11)."</option>
			<option value='600'>".str_replace("[TIME]", 10, AUTOGAL_LANG_SLIDESHOW_L11)."</option>
			</select>
		</td>
	</tr>
	<tr>
		<td style='width:50%' class='forumheader3'>".AUTOGAL_LANG_SLIDESHOW_L7."</td>
		<td style='width:50%' class='forumheader3'><input type='checkbox' name='repeat' checked='checked'></td>
	</tr>
	<tr>
		<td style='width:50%' class='forumheader3'>".AUTOGAL_LANG_SLIDESHOW_L13."</td>
		<td style='width:50%' class='forumheader3'><input type='checkbox' name='fullimg'></td>
	</tr>
	<tr style='vertical-align:top'>
		<td colspan='2'  style='text-align:center' class='forumheader2'>
			<input class='button' type='submit' value='".AUTOGAL_LANG_SLIDESHOW_L12."'>&#160;$backLink 
		</td>
	</tr>
	<input type='hidden' name='first' value=\"".rawurlencode($mediaObj->Element())."\">
	<input type='hidden' name='slide' value=\"".rawurlencode($mediaObj->Element())."\">
	<input type='hidden' name='status' value=\"1\">
	</table>
	</form>";
	
	$text .= AutoGal_GetNewWindowFooter();
	
	return $text;
}

?>