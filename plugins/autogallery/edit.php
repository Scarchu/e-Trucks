<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        06/08/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

$startAG = microtime(true);

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_EDITFUNCTIONS);
require_once(AUTOGAL_ADMINFUNCTIONS);

define("e_PAGETITLE", $pref['autogal_title']." / ".AUTOGAL_LANG_ADMIN_EDIT_98);
require_once(HEADERF);
print "\n<!-- AUTOGALLERY START -->\n\n";

$g_agStartRender = microtime(true);
$g_agRenderTime = 0;

$g_element = AutoGal_GetHtmlVar('show');
$g_mediaObj = new AutoGal_CMediaObj($g_element);
AutoGal_LoadGlobals();

if (!$g_mediaObj->IsValid())
{
	$text = "<div style='text-align:center'><br />".AUTOGAL_LANG_L2."<br /><br /><b>".htmlspecialchars($g_element)."</b><br />".$g_mediaObj->LastError()."<br /><br /><a href=\"".AUTOGAL_AUTOGALLERY."\">".AUTOGAL_LANG_L3."</a><br /><br /></div>";
}
else
{
	$text = AutoGal_DoAdminAction($g_mediaObj);
}

$ns -> tablerender(AUTOGAL_LANG_ADMIN_EDIT_0, $text);
print "\n<!-- AUTOGALLERY END -->\n\n";
if ($pref['autogal_showfooter']){require_once(FOOTERF);}

function AutoGal_DoAdminAction($mediaObj)
{
	global $g_startFile;
	global $g_startGallery;
	global $g_isAdminMode;
	
	if (!$g_isAdminMode) return AUTOGAL_LANG_MENU_L27;
	$action = $_POST['ag_adminaction'];
	$selection = $_POST['ag_adminselection'];
	
	switch ($action)
	{
		case 'uploadthumb': $isAdmin = $mediaObj->CheckUserPriv('setfilethumb'); break;
		case 'clearcachesubgals': $isAdmin = $mediaObj->CheckUserPriv('clearcache'); break;
		case 'regencachesubgals': $isAdmin = $mediaObj->CheckUserPriv('regencache'); break;
		default: $isAdmin = $mediaObj->CheckUserPriv($action); break;
	}
	
	if (!$isAdmin)
	{
		return str_replace('[ACTION]', $action, AUTOGAL_LANG_MENU_L28);
	}
	
	if ($selection == 'current')
	{
		$objs[] = $mediaObj;
	}
	else
	{
		if ($mediaObj->IsGallery()){
			$allObjs = $mediaObj->ChildMediaObjs();
		}else{
			$allObjs = $mediaObj->GalleryMediaObjs();
		}
			
		if ($selection == 'all')
		{
			$objs = array_merge($allObjs['galleries'], $allObjs['files']);
		}
		else if ($selection == 'files')
		{
			$objs = $allObjs['files'];
		}
		else if ($selection == 'galleries')
		{
			$objs = $allObjs['galleries'];
		}
		else
		{
			$allObjs = array_merge($allObjs['galleries'], $allObjs['files']);
		
			foreach ($allObjs as $obj)
			{
				$isChecked = AutoGal_GetHtmlVar("ag_ele_".$obj->Element());
				
				if (($selection == 'checked')&&($isChecked))
				{
					$objs[] = $obj;
				}
				else if (($selection == 'unchecked')&&(!$isChecked))
				{
					$objs[] = $obj;
				}
			}
		}
	}
	
	$text = "<form method='post'".($action == 'uploadthumb' ? " enctype='multipart/form-data'" : '').">
	<input type='hidden' name='ag_adminaction' value='$action'>
	<input type='hidden' name='show' value=\"".AutoGal_HtmlVar($mediaObj->Element())."\">
	<input type='hidden' name='ag_adminselection' value='$selection'>
	<input type='hidden' name='start' value='$g_startFile'>
	<input type='hidden' name='startgal' value='$g_startGallery'>";
	
	foreach ($_POST as $postVar => $postVal)
	{
		if (preg_match('/^ag\_ele\_/', $postVar))
		{
			$text .= "<input type='hidden' name='$postVar' value=\"$postVal\">\n";
		}
	}
		
	if ($action == 'rename')
	{
		$text .= AutoGal_PageRenameElements($mediaObj, $objs);
	}
	else if ($action == 'delete')
	{
		$text .= AutoGal_PageDeleteElements($mediaObj, $objs);
	}
	else if ($action == 'move')
	{
		$text .= AutoGal_PageMoveElements($mediaObj, $objs);
	}
	else if ($action == 'setgallerythumb')
	{
		$text .= AutoGal_PageSetGalleryThumb($mediaObj, $objs);
	}
	else if ($action == 'creategallery')
	{
		$text .= AutoGal_PageCreateGallery($mediaObj, $objs);
	}
	else if ($action == 'editaccess')
	{
		$text .= AutoGal_PageChangeUserClasses($mediaObj, $objs);
	}
	else if ($action == 'editdescription')
	{
		$text .= AutoGal_PageChangeDescription($mediaObj, $objs);
	}
	else if ($action == 'watermark')
	{
		$text .= AutoGal_PageWatermark($mediaObj, $objs);
	}
	else if ($action == 'rotate')
	{
		$text .= AutoGal_PageRotate($mediaObj, $objs);
	}
	else if ($action == 'uploadthumb')
	{
		$text .= AutoGal_PageUploadThumb($mediaObj, $objs);
	}
	else if ($action == 'autowatermark')
	{
		$text .= AutoGal_PageAutoWatermarking($mediaObj, $objs);
	}
	else if ($action == 'setviewsize')
	{
		$text .= AutoGal_PageSetViewSize($mediaObj, $objs);
	}
	else if ($action == 'clearmeta')
	{
		$text .= AutoGal_PageClearMeta($mediaObj, $objs);
	}
	else if ($action == 'clearcache')
	{
		$text .= AutoGal_PageClearCache($mediaObj, $objs, 0);
	}
	else if ($action == 'clearcachesubgals')
	{
		$text .= AutoGal_PageClearCache($mediaObj, $objs, 1);
	}
	else if ($action == 'regencache')
	{
		$text .= AutoGal_PageRegenCache($mediaObj, $objs, 0);
	}
	else if ($action == 'regencachesubgals')
	{
		$text .= AutoGal_PageRegenCache($mediaObj, $objs, 1);
	}
	
	$text .= "</form>";
	
	$botLinks = AutoGal_GetBotLinks();
	$text .= (count($botLinks) > 0 ? "<div style='text-align:center'><br />".implode(' ', $botLinks)."</div>" : '');
		
	return $text;
}

function AutoGal_PageRenameElements($selObj, $objs)
{
	global $ns;
	
	if ($_POST['ag_doit'])
	{
		if (($selObj->Element() == $objs[0]->Element())&&(count($objs) == 1))
		{
			$backObj = $selObj->GalleryMediaObj();
		}
		else
		{
			$backObj = $selObj;
		}
		
		// Submit button pressed
		foreach ($objs as $objI => $obj)
		{
			if ($obj->IsRoot()) continue;
			
			$newTitle = AutoGal_GetHtmlVar("ag_title_".$obj->Element());
			$newOrderNum = AutoGal_GetHtmlVar("ag_num_".$obj->Element());
			$newOrderNum = (preg_match("/\d+$/", $newOrderNum) ? $newOrderNum : -1);
			
			if (($obj->FullTitle() != $newTitle)||($obj->OrderNumber() != $newOrderNum))
			{
				$atLeast1 = 1;
			
				$objMsgs = AutoGal_EditRename($objs[$objI], $newTitle, $newOrderNum);
				$msgs = array_merge($msgs, $objMsgs);
				$cacheGalleries[] = $obj->Gallery();
				
			}
		}
		
		$text .= AutoGal_ReturnMsgsHtml($msgs);
		
		AutoGal_ClearCacheMenu($cacheGalleries, 0);
		$text .= AutoGal_BackLink($backObj);		
		return $text;
	}
	
	foreach ($objs as $obj)
	{
		if ($obj->IsRoot()) continue;
		
		$renameObjsTable .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".
				$obj->TypeTitle()."
			</td>
			<td class='forumheader3' style='text-align:center'>".
				$obj->ThumbAndTitleHtml()."
			</td>
			<td class='forumheader3' style='text-align:center'>
				<input type='text' name=\"ag_num_".AutoGal_HtmlVar($obj->Element())."\" size='3' class='tbox' value=\"".($obj->OrderNumber() >= 0 ? $obj->OrderNumber() : '')."\">
			</td>
			<td class='forumheader3' style='text-align:center'>
				<input type='text' name=\"ag_title_".AutoGal_HtmlVar($obj->Element())."\" size='40' class='tbox' value=\"".$obj->FullTitle()."\">
			</td>
		</tr>";
	}
	
	if (!$renameObjsTable)
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		".AUTOGAL_LANG_ADMIN_EDIT_102."<br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
		
		return $text;
	}
	
	$text = "
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_5."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_6."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_7."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_8."</th>
	</tr>
	$renameObjsTable
	<tr>
		<td colspan='4' class='forumheader' style='text-align:center'>
			<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_9."'>
			<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_15."' onclick='javascript:history.go(-1)'>
		</td>
	</tr>
	</table>";
	
	return $text;
}

function AutoGal_BackLink($backObj)
{
	return "<br /><div style='text-align:center'>[<a href=\"".$backObj->BackLink()."\">".AUTOGAL_LANG_ADMIN_EDIT_3.$backObj->TypeTitle()." '".$backObj->Title()."'</a>]</div><br />";
}

function AutoGal_PageDeleteElements($selObj, $objs)
{
	if ($_POST['ag_doit'])
	{
		foreach ($objs as $obj)
		{
			if ($obj->IsRoot()) continue;
			
			$cacheGalleries[] = $obj->Gallery();
			$objMsgs = AutoGal_EditDelete($obj);
			$msgs = array_merge($msgs, $objMsgs);
		}
		
		$text .= AutoGal_ReturnMsgsHtml($msgs);
		
		if (($selObj->Element() == $objs[0]->Element())&&(count($objs) == 1))
		{
			$backObj = $selObj->GalleryMediaObj();
		}
		else
		{
			$backObj = $selObj;
		}
		
		AutoGal_ClearCacheMenu($cacheGalleries, 0);
		$text .= AutoGal_BackLink($backObj);
		
		return $text;
	}
	
	foreach ($objs as $obj)
	{
		if ($obj->IsRoot()) continue;
		
		$galObj = $obj->GalleryMediaObj();
		
		$delObjsTable .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".$obj->TypeTitle()."</td>
			<td class='forumheader3' style='text-align:center'>".$galObj->Title()."</td>
			<td class='forumheader3' style='text-align:center'>".$obj->ThumbAndTitleHtml(1)."</td>
		</tr>";
	}
	
	if (!$delObjsTable)
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		".AUTOGAL_LANG_ADMIN_EDIT_100."<br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
		
		return $text;
	}
	
	$text = "
	<br />
	<div style='text-align:center'>
	<b>".AUTOGAL_LANG_ADMIN_EDIT_41."</b><br />
	<br />
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_5."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_16."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_6."</th>
	</tr>
	$delObjsTable
	</table>
	<br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_40."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_30."' onclick='javascript:history.go(-1)'>
	</div>";
	
	return $text;
}

function AutoGal_PageMoveElements($selObj, $objs)
{
	if (($selObj->Element() == $objs[0]->Element())&&(count($objs) == 1))
	{
		$galObj = $selObj->GalleryMediaObj();
	}
	else
	{
		$galObj = $selObj;
	}
	
	if ($_POST['ag_doit'])
	{
		$moveTo = rawurldecode($_POST['ag_moveloc']);
		$moveToObj = new AutoGal_CMediaObj($moveTo);
		
		$cacheGalleries[] = $moveToObj->Element();
		
		foreach ($objs as $obj)
		{
			if ($obj->IsRoot()) continue;
			
			$cacheGalleries[] = $obj->Gallery();
			$objMsgs = AutoGal_EditMove($obj, $moveToObj);
			$msgs = array_merge($msgs, $objMsgs);
		}
		
		$text .= AutoGal_ReturnMsgsHtml($msgs);
		$text .= AutoGal_BackLink($galObj);
		
		AutoGal_ClearCacheMenu($cacheGalleries, 0);
		
		return $text;
	}
	
	$galSelect = AutoGal_GallerySelect($galObj->Gallery(), $galObj->Element(), 'admin');
	
	foreach ($objs as $obj)
	{
		if ($obj->IsRoot()) continue;
		$galObj = $obj->GalleryMediaObj();
		
		$moveObjsTable .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".$obj->TypeTitle()."</td>
			<td class='forumheader3' style='text-align:center'>".$galObj->Title()."</td>
			<td class='forumheader3' style='text-align:center'>".$obj->ThumbAndTitleHtml(1)."</td>
		</tr>";
	}
	
	if (!$moveObjsTable)
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		".AUTOGAL_LANG_ADMIN_EDIT_101."<br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
		
		return $text;
	}
	
	$text = "
	<br />
	<div style='text-align:center'>
	<b>".AUTOGAL_LANG_ADMIN_EDIT_38."</b><br />
	<br />
	<select name='ag_moveloc' class='tbox'>
	$galSelect
	</select><br />
	<br />
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_5."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_16."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_6."</th>
	</tr>
	$moveObjsTable
	</table>
	<br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_37."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_15."' onclick='javascript:history.go(-1)'>
	</div>";
	
	return $text;
}

function AutoGal_PageSetGalleryThumb($selObj, $objs)
{
	if ($selObj->IsFile())
	{
		$thumbObj = $selObj;
		$gallObj = $selObj->GalleryMediaObj();
	}
	else
	{
		$gallObj = $selObj;
		
		if (count($objs) != 1)
		{
			return "<div style='text-align:center'>".AUTOGAL_LANG_ADMIN_EDIT_34."</div>";
		}
		
		$thumbObj = $objs[0];
	}
	
	if ($gallObj->IsRoot())
	{
		return "<div style='text-align:center'>".AUTOGAL_LANG_ADMIN_EDIT_36."</div>";
	}
	
	if (($thumbObj->IsGallery())||($thumbObj->FileType() != 'image'))
	{
		return "<div style='text-align:center'>".AUTOGAL_LANG_ADMIN_EDIT_32."</div>";
	}
	
	if ($_POST['ag_doit'])
	{
		$msgs = AutoGal_EditSetGalleryThumbnail($gallObj, $thumbObj);
		
		$cacheGalleries[] = $thumbObj->Gallery();
		$cacheGalleries[] = $gallObj->Element();
		
		$text .= AutoGal_ReturnMsgsHtml($msgs);
		$text .= AutoGal_BackLink($gallObj);
		
		AutoGal_ClearCacheMenu($cacheGalleries, 0);
		
		return $text;
	}
	
	$text = "
	<br />
	<div style='text-align:center'>
	<b>".str_replace("[GALLERY]", $gallObj->TitleLink(), AUTOGAL_LANG_ADMIN_EDIT_110)."</b><br />
	<br />".$thumbObj->ThumbAndTitleHtml()."<br /><br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_29."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_30."' onclick='javascript:history.go(-1)'>";
		
	return $text;
}

function AutoGal_PageCreateGallery($selObj, $objs)
{
	$maxGalleries = 5;
	
	if (!$selObj->IsGallery())
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		<b>".AUTOGAL_LANG_ADMIN_EDIT_28."</b><br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
		
		return $text;
	}
 	
	if ($_POST['ag_doit'])
	{
		foreach ($_POST as $postVar => $postVal)
		{
			if ((preg_match("/^ag_newgallery_(\d+)$/", $postVar))&&($postVal))
			{
				$newGalleryName = stripslashes($postVal);
				$objMsgs = AutoGal_CreateGallery($selObj, $newGalleryName);
				$msgs = array_merge($msgs, $objMsgs);
			}
		}
		
		if ($msgs)
		{
			$text .= AutoGal_ReturnMsgsHtml($msgs);
			$text .= AutoGal_BackLink($selObj);
			
			AutoGal_ClearCacheMenu($selObj->Element(), 0);
			
			return $text;
		}
		else
		{
			$text .= "<div style='text-align:center'>".AUTOGAL_LANG_ADMIN_EDIT_27."<br /></div>";
		}
	}
	
	$text .= "
	<br />
	<div style='text-align:center'>
	<b>".AUTOGAL_LANG_ADMIN_EDIT_25."'".$selObj->Title()."':</b><br />
	<span class='smalltext'>".AUTOGAL_LANG_ADMIN_EDIT_26."</span><br />
	<br />";
	
	for ($textBoxI = 0; $textBoxI < $maxGalleries; $textBoxI ++)
	{
		$text .= ($textBoxI + 1).". <input type='text' class='tbox' size='50' name='ag_newgallery_$textBoxI'><br /><br />";
	}
	
	$text .= "
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_23."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_15."' onclick='javascript:history.go(-1)'>";
	
	return $text;
}

function AutoGal_PageChangeUserClasses($selObj, $objs)
{
	require_once(e_HANDLER."userclass_class.php");
	
	$userClasses = array('view', 'upload', 'admin', 'gcomment', 'mcomment');
	
	$ucTypeTitles['view']     = AUTOGAL_LANG_ADMIN_EDIT_17;
	$ucTypeTitles['upload']   = AUTOGAL_LANG_ADMIN_EDIT_18;
	$ucTypeTitles['admin']    = AUTOGAL_LANG_ADMIN_EDIT_19;
	$ucTypeTitles['gcomment'] = AUTOGAL_LANG_ADMIN_EDIT_20;
	$ucTypeTitles['mcomment'] = AUTOGAL_LANG_ADMIN_EDIT_21;
	
	if ($_POST['ag_doit'])
	{
		foreach ($objs as $mediaObj)
		{
			if ($mediaObj->IsFile()) continue;
			
			$element = $mediaObj->Element();
			
			foreach ($userClasses as $userClass)
			{
				$newClass = AutoGal_GetHtmlVar("ag_uc".$userClass."_".$element);
				if (!$newClass) $newClass = 0;
				
				if ($mediaObj->UserClass($userClass) == $newClass) continue;
				
				$ucType = $ucTypeTitles[$userClass];
				$className = AutoGal_UserClassName($newClass);
				
				$mediaObj->UserClass($userClass, $newClass);
				AutoGal_AdminLog(AUTOGAL_LANG_LOG_L3, $mediaObj->Element(), str_replace("[TYPE]", $ucType, str_replace("[CLASS]", $className, str_replace("[CLASSID]", $newClass, AUTOGAL_LANG_ADMIN_FUNCTIONS_L136))));
				
				if (!$mediaObj->SaveMeta())
				{
					$msgs[] = "*** ".$mediaObj->LastError();
				}
				else
				{
					$msgs[] = str_replace("[GALLERY]", $mediaObj->Title(), str_replace("[TYPE]", $ucType, str_replace("[CLASS]", $className, str_replace("[CLASSID]", $newClass, AUTOGAL_LANG_ADMIN_FUNCTIONS_L133))));
				}
			}
		}
		
		if ($msgs)
		{
			$text .= AutoGal_ReturnMsgsHtml($msgs);
			$text .= AutoGal_BackLink($selObj);
		}
		else
		{
			$text .= "<div style='text-align:center'>".AUTOGAL_LANG_ADMIN_EDIT_22."<br /></div>";
		}
	}
	
	foreach ($objs as $mediaObj)
	{
		if ($mediaObj->IsFile()) continue;
	
		$galleryRows .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".
				$mediaObj->ThumbAndTitleHtml()."
			</td>
			<td class='forumheader3' style='text-align:center'>
				".AutoGal_UserClassSelect("ag_ucview_".AutoGal_HtmlVar($mediaObj->Element()), $mediaObj->UserClass('view'))."
			</td>
			<td class='forumheader3' style='text-align:center'>
				".AutoGal_UserClassSelect("ag_ucupload_".AutoGal_HtmlVar($mediaObj->Element()), $mediaObj->UserClass('upload'))."
			</td>
			<td class='forumheader3' style='text-align:center'>
				".AutoGal_UserClassSelect("ag_ucadmin_".AutoGal_HtmlVar($mediaObj->Element()), $mediaObj->UserClass('admin'))."
			</td>
			<td class='forumheader3' style='text-align:center'>
				".AutoGal_UserClassSelect("ag_ucgcomment_".AutoGal_HtmlVar($mediaObj->Element()), $mediaObj->UserClass('gcomment'))."
			</td>
			<td class='forumheader3' style='text-align:center'>
				".AutoGal_UserClassSelect("ag_ucmcomment_".AutoGal_HtmlVar($mediaObj->Element()), $mediaObj->UserClass('mcomment'))."
			</td>
		</tr>";
	}
	
	if ($galleryRows)
	{
		$text .= "
		<br />
		".AUTOGAL_LANG_ADMIN_EDIT_109."<br />
		<br />
		<ul>
			<li><b>".AUTOGAL_LANG_ADMIN_EDIT_17."</b> - ".AUTOGAL_LANG_ADMIN_EDIT_104."</li>
			<li><b>".AUTOGAL_LANG_ADMIN_EDIT_18."</b> - ".str_replace("[SETTING]", AUTOGAL_LANG_ADMIN_MAIN_L21, str_replace("[VALUE]", AutoGal_UserClassName($pref['autogal_revuploaduc']), AUTOGAL_LANG_ADMIN_EDIT_105))."</li>
			<li><b>".AUTOGAL_LANG_ADMIN_EDIT_19."</b> - ".AUTOGAL_LANG_ADMIN_EDIT_106."</li>
			<li><b>".AUTOGAL_LANG_ADMIN_EDIT_20."</b> - ".AUTOGAL_LANG_ADMIN_EDIT_107."</li>
			<li><b>".AUTOGAL_LANG_ADMIN_EDIT_21."</b> - ".AUTOGAL_LANG_ADMIN_EDIT_108."</li>
		</ul>
		<br />
		<div style='text-align:center'>
		<table class='border' style='width:97%'>
		<tr>
			<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_16."</th>
			<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_17."</th>
			<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_18."</th>
			<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_19."</th>
			<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_20."</th>
			<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_21."</th>
		</tr>
		$galleryRows
		</table>
		<br />
		<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_14."'>
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_15."' onclick='javascript:window.location.href=\"".htmlspecialchars($selObj->BackLink())."\"'>
		</div>";
	}
	else
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		<b>".AUTOGAL_LANG_ADMIN_EDIT_13."</b><br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
	}
	
	return $text;
}

function AutoGal_PageChangeDescription($selObj, $objs)
{
	if ($_POST['ag_doit'])
	{
		foreach ($objs as $mediaObj)
		{
			$newDesc = AutoGal_GetHtmlVar("ag_desc_".$mediaObj->Element());
			$newDesc = preg_replace("/\r/", '', $newDesc);
	
			if ($newDesc != $mediaObj->Description())
			{
				$mediaObj->Description($newDesc);
				if (!$mediaObj->SaveMeta())
				{
					$msgs[] = "*** ".$mediaObj->LastError();
				}
				else
				{
					AutoGal_AdminLog(AUTOGAL_LANG_LOG_L4, $mediaObj->Element(), str_replace("\n", "\\n", $mediaObj->Description()));
					$msgs[] = str_replace("[TITLE]", $mediaObj->Title(), str_replace("[TYPE]", $mediaObj->TypeTitle(), AUTOGAL_LANG_ADMIN_FUNCTIONS_L134));
				}
			}
		}
		
		if ($msgs)
		{
			$text .= AutoGal_ReturnMsgsHtml($msgs);
			$text .= AutoGal_BackLink($selObj);
			return $text;
		}
		else
		{
			$text .= "<div style='text-align:center'>".AUTOGAL_LANG_ADMIN_EDIT_11."<br /></div>";
		}
	}
		
	$text .= "
	<br />
	<div style='text-align:center'>
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_5."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_6."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_10."</th>
	</tr>";
	
	foreach ($objs as $mediaObj)
	{
		$text .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".
				$mediaObj->TypeTitle()."
			</td>
			<td class='forumheader3' style='text-align:center'>".
				$mediaObj->ThumbAndTitleHtml()."
			</td>
			<td class='forumheader3' style='text-align:center'>
				<textarea name=\"".AutoGal_HtmlVar("ag_desc_".$mediaObj->Element())."\" class='tbox' cols='70' rows='5'>".$mediaObj->Description()."</textarea>
			</td>
		</tr>";
	}
	
	$text .= "
	</table>
	<br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_24."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_15."' onclick='javascript:history.go(-1)'>
	</div>";
	
	return $text;
}

function AutoGal_PageWatermark($selObj, $objs)
{
	global $pref;
	
	if (isset($_POST['ag_wmimage']))
	{
		$opts['ag_wmimage'] = $_POST['ag_wmimage'];
		$opts['ag_wmintensity'] = $_POST['ag_wmintensity'];
		$opts['ag_wmxalign'] = $_POST['ag_wmxalign'];
		$opts['ag_wmyalign'] = $_POST['ag_wmyalign'];
		$opts['ag_wmxoffset'] = $_POST['ag_wmxoffset'];
		$opts['ag_wmyoffset'] = $_POST['ag_wmyoffset'];
	}
	else
	{
		$opts['ag_wmimage'] = $pref['autogal_wmarkimage'];
		$opts['ag_wmintensity'] = $pref['autogal_wmarkintensity'];
		$opts['ag_wmxalign'] = $pref['autogal_wmarkxalign'];
		$opts['ag_wmyalign'] = $pref['autogal_wmarkyalign'];
		$opts['ag_wmxoffset'] = $pref['autogal_wmarkxoffset'];
		$opts['ag_wmyoffset'] = $pref['autogal_wmarkyoffset'];
	}
	
	$opts['ag_wmintensity'] = (preg_match("/^[0-9]+$/", $opts['ag_wmintensity']) ? $opts['ag_wmintensity'] : 30);
	$opts['ag_wmxoffset'] = (preg_match("/^[0-9]+$/", $opts['ag_wmxoffset']) ? $opts['ag_wmxoffset'] : 0);
	$opts['ag_wmyoffset'] = (preg_match("/^[0-9]+$/", $opts['ag_wmyoffset']) ? $opts['ag_wmyoffset'] : 0);
		
	foreach ($objs as $mediaObj)
	{
		if ($mediaObj->IsGallery()) continue;
		if ($mediaObj->FileType() != 'image') continue;
		
		$imageTable .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->ThumbImageHtml(0, 1)."</td>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->TitleLink()."</td>
		</tr>";
	}
	
	if (!$imageTable)
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		".AUTOGAL_LANG_ADMIN_EDIT_45."<br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
		
		return $text;
	}
	
	if ($_POST['ag_doit'])
	{
		# DO THE WATERMARKING
		$text = "<div style='text-align:left'>";
	
		require_once(AUTOGAL_IMGMANIPHANDLER);

		$mode = $pref['resize_method'];
		$imPath = $pref['im_path'];
		$imQuality = ($pref['im_quality'] ? $pref['im_quality'] : 99);
		$gdim = new GDIM($mode, $imPath, $imQuality);
		
		$wmOpts = array
		(
			'intensity' => $opts['ag_wmintensity'], 
			'xalign' => $opts['ag_wmxalign'], 
			'yalign' => $opts['ag_wmyalign'], 
			'xoffset' => $opts['ag_wmxoffset'], 
			'yoffset' => $opts['ag_wmyoffset'],
		);
	
		$wmImage = AUTOGAL_WATERMARKDIRABS."/".$opts['ag_wmimage'];
		
		foreach ($objs as $mediaObj)
		{
			if ($mediaObj->IsGallery()) continue;
			if ($mediaObj->FileType() != 'image') continue;
			
			$image = $mediaObj->AbsPath();
			$basename = $mediaObj->BaseName();
			
			if ($gdim->watermark($image, $image, $wmImage, $wmOpts))
			{
				$msgs[] = $basename.': '.AUTOGAL_LANG_ADMIN_EDIT_48;
				AutoGal_AdminLog(AUTOGAL_LANG_LOG_L5, $mediaObj->Element(), basename($wmImage));
			}
			else
			{
				$msgs[] = $basename.": ".AUTOGAL_LANG_ADMIN_EDIT_49." - ".$gdim->lastError();
			}
			
			if ($mediaObj->PreviewImageExists())
			{
				$image = $mediaObj->PreviewImagePath();
				$basename = basename($image);
				
				if ($gdim->watermark($image, $image, $wmImage, $wmOpts))
				{
					$msgs[] = $basename.': '.AUTOGAL_LANG_ADMIN_EDIT_48;
				}
				else
				{
					$msgs[] = $basename.": ".AUTOGAL_LANG_ADMIN_EDIT_49." - ".$gdim->lastError();
				}
			}
		}
		
		if ($msgs)
		{
			$text .= AutoGal_ReturnMsgsHtml($msgs);
			$text .= AutoGal_BackLink($selObj);
			return $text;
		}
		else
		{
			$text .= "<div style='text-align:center'>".AUTOGAL_LANG_ADMIN_EDIT_11."<br /></div>";
		}
		
		return $text;
	}
	
	# GET LIST OF WATERMARKS
	$dh = opendir(AUTOGAL_WATERMARKDIRABS);
	while ($file = readdir($dh))
	{
		if (!AutoGal_IsImage($file)) continue;
		if (!$firstWMark) $firstWMark = $file;
		$wmImageOpts .= "<option value=\"".rawurlencode($file)."\"".($file == $opts['ag_wmimage'] ? " selected='selected'" : '').">$file</option>";
	}
	
	$selWMarkImg = ($opts['ag_wmimage'] ? $opts['ag_wmimage'] : $firstWMark);
	if ($selWMarkImg)
	{
		$selWMarkUrl = AUTOGAL_WATERMARKDIR."/$selWMarkImg";
	}

	$wmSettTable =  "
	<table class='border' width='97%'>
	<tr>
		<td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_3."</b><br /><span class='smalltext'>".str_replace("[DIR]", AUTOGAL_WATERMARKDIRABS, AUTOGAL_LANG_ADMIN_WATERMARK_4)."</span></td>
		<td style='width:50%' class='forumheader3'><select class='tbox' name='ag_wmimage' onchange='javascript:form.submit()'>$wmImageOpts</select>".($selWMarkUrl ? "<br /><br /><img src=\"$selWMarkUrl \" style='border:0' />" : '')."</td>
	</tr>
	<tr>
		<td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_5."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_WATERMARK_6."</span></td>
		<td style='width:50%' class='forumheader3'><input type='text' class='tbox' name='ag_wmintensity' size='3' value=\"".$opts['ag_wmintensity']."\"></td>
	</tr>
	<tr>
		<td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_7."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_WATERMARK_8."</span></td>
		<td style='width:50%' class='forumheader3'>
			<select name='ag_wmyalign' class='tbox'>
			<option value='t'".($opts['ag_wmyalign'] == 't' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_9."</option>
			<option value='m'".($opts['ag_wmyalign'] == 'm' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_10."</option>
			<option value='b'".($opts['ag_wmyalign'] == 'b' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_11."</option>
			</select>
			&nbsp;
			<select name='ag_wmxalign' class='tbox'>
			<option value='l'".($opts['ag_wmxalign'] == 'l' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_12."</option>
			<option value='c'".($opts['ag_wmxalign'] == 'c' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_13."</option>
			<option value='r'".($opts['ag_wmxalign'] == 'r' ? " selected='selected'" : '').">".AUTOGAL_LANG_ADMIN_WATERMARK_14."</option>
			</select>
		</td>
	</tr>
	<tr>
		<td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_WATERMARK_15."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_WATERMARK_16."</span></td>
		<td style='width:50%' class='forumheader3'>
			X: <input type='text' class='tbox' name='ag_wmxoffset' size='3' value=\"".$opts['ag_wmxoffset']."\"> 
			Y: <input type='text' class='tbox' name='ag_wmyoffset' size='3' value=\"".$opts['ag_wmyoffset']."\">
		<td>
	</tr>
	</table>";
	
	$text = "
	<br />
	<div style='text-align:center'>
	<b>".AUTOGAL_LANG_ADMIN_EDIT_42."</b><br />
	<span class='smalltext'>".AUTOGAL_LANG_ADMIN_EDIT_43."</span><br />
	<br />
	$wmSettTable<br />
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_46."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_39."</th>
	</tr>
	$imageTable
	</table>
	<br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_47."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_30."' onclick='javascript:history.go(-1)'>
	</div>";
	
	return $text;
}

function AutoGal_PageRotate($selObj, $objs)
{
	global $pref;
	
	foreach ($objs as $mediaObj)
	{
		if ($mediaObj->IsGallery()) continue;
		if ($mediaObj->FileType() != 'image') continue;
		
		$imageTable .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->ThumbImageHtml(0, 1)."</td>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->TitleLink()."</td>
		</tr>";
	}
	
	if (!$imageTable)
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		".AUTOGAL_LANG_ADMIN_EDIT_45."<br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
		
		return $text;
	}
	
	if ($_POST['ag_doit'])
	{
		# DO THE ROTATING
		require_once(AUTOGAL_IMGMANIPHANDLER);

		$mode = $pref['resize_method'];
		$imPath = $pref['im_path'];
		$imQuality = ($pref['im_quality'] ? $pref['im_quality'] : 99);
		$gdim = new GDIM($mode, $imPath, $imQuality);
		$angle = $_POST['ag_angle'];
		if (($angle != 90)&&($angle != 180)&&($angle != 270)) $angle = 90;
		
		foreach ($objs as $mediaObj)
		{
			if ($mediaObj->IsGallery()) continue;
			if ($mediaObj->FileType() != 'image') continue;
		
			$image = $mediaObj->AbsPath();
			$basename = $mediaObj->BaseName();
			
			if ($gdim->rotate($image, $image, $angle))
			{
				$msgs[] = $basename.': '.AUTOGAL_LANG_ADMIN_EDIT_53;
				AutoGal_AdminLog(AUTOGAL_LANG_LOG_L6, $mediaObj->Element(), $angle);
			}
			else
			{
				$msgs[] = $basename.": ".AUTOGAL_LANG_ADMIN_EDIT_54." - ".$gdim->lastError();
			}
			
			if ($mediaObj->PreviewImageExists())
			{
				$image = $mediaObj->PreviewImagePath();
				$basename = basename($image);
				
				if ($gdim->rotate($image, $image, $angle))
				{
					$msgs[] = $basename.': '.AUTOGAL_LANG_ADMIN_EDIT_53;
				}
				else
				{
					$msgs[] = $basename.": ".AUTOGAL_LANG_ADMIN_EDIT_54." - ".$gdim->lastError();
				}
			}
			
			if ($mediaObj->ThumbImageExists())
			{
				$image = $mediaObj->ThumbImagePath();
				$basename = basename($image);
				
				if ($gdim->rotate($image, $image, $angle))
				{
					$msgs[] = $basename.': '.AUTOGAL_LANG_ADMIN_EDIT_53;
				}
				else
				{
					$msgs[] = $basename.": ".AUTOGAL_LANG_ADMIN_EDIT_54." - ".$gdim->lastError();
				}
			}
		}
		
		if ($msgs)
		{
			$text .= AutoGal_ReturnMsgsHtml($msgs);
			$text .= AutoGal_BackLink($selObj);
			return $text;
		}
	}
	
	$text = "
	<br />
	<div style='text-align:center'>
	<b>".AUTOGAL_LANG_ADMIN_EDIT_51."</b><br />
	<br />
	<select name='ag_angle' class='tbox'>
	<option value='90'>90</option>
	<option value='180'>180</option>
	<option value='270'>270</option>
	</select><br />
	<br />
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_46."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_39."</th>
	</tr>
	$imageTable
	</table>
	<br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_52."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
	</div>";
	
	return $text;
}

function AutoGal_PageSetViewSize($selObj, $objs)
{
	global $pref;
	
	foreach ($objs as $mediaObj)
	{
		if ($mediaObj->IsGallery()) continue;
		
		$imageTable .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->ThumbImageHtml(0, 1)."</td>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->TitleLink()."</td>
		</tr>";
	}
	
	if (!$imageTable)
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		".AUTOGAL_LANG_ADMIN_EDIT_80."<br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
		
		return $text;
	}
	
	if ($_POST['ag_doit'])
	{
		$width = $_POST['ag_viewwidth'];
		$height = $_POST['ag_viewheight'];
		if (!$width) $width = 0;
		if (!$height) $height = 0;
		
		$widthMsg = ($width ? $width : AUTOGAL_LANG_ADMIN_EDIT_87);
		$heightMsg = ($height ? $height : AUTOGAL_LANG_ADMIN_EDIT_87);
		
		if (!preg_match("/^[0-9]*$/", $width))
		{
			$text .= "<div style='text-align:center'><br />".AUTOGAL_LANG_ADMIN_EDIT_84."<br /></div>";
		}
		else if (!preg_match("/^[0-9]*$/", $height))
		{
			$text .= "<div style='text-align:center'><br />".AUTOGAL_LANG_ADMIN_EDIT_85."<br /></div>";
		}
		else
		{
			$text .= "<div style='text-align:left'>";
	
			foreach ($objs as $mediaObj)
			{
				if ($mediaObj->IsGallery()) continue;
				
				$mediaObj->ViewWidth($width);
				$mediaObj->ViewHeight($height);
				
				if (!$mediaObj->SaveMeta())
				{
					$msgs[] = "*** ".$mediaObj->LastError();
				}
				else
				{
					$msgs[] = $mediaObj->TypeTitle()." \"".$mediaObj->Title()."\": ".str_replace("[WIDTH]", $widthMsg, str_replace("[HEIGHT]", $heightMsg, AUTOGAL_LANG_ADMIN_EDIT_86));
					AutoGal_AdminLog(AUTOGAL_LANG_LOG_L7, $mediaObj->Element(), $width.'x'.$height);
				}
			}
		
			if ($msgs)
			{
				$text .= AutoGal_ReturnMsgsHtml($msgs);
				$text .= AutoGal_BackLink($selObj);
				return $text;
			}
		}
	}
	
	$text .= "
	<br />
	<div style='text-align:center'>
	<b>".AUTOGAL_LANG_ADMIN_EDIT_81."</b><br />
	<span class='smalltext'>".AUTOGAL_LANG_ADMIN_EDIT_82."</span><br />
	<br />
	<input type='text' class='tbox' name='ag_viewwidth' size='5'> x <input type='text' class='tbox' name='ag_viewheight' size='5'><br />
	<br />
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_46."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_39."</th>
	</tr>
	$imageTable
	</table>
	<br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_83."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
	</div>";
	
	return $text;
}

function AutoGal_PageClearMeta($selObj, $objs)
{
	global $pref;
	
	foreach ($objs as $mediaObj)
	{
		$eleListTable .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->TypeTitle()."</td>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->ThumbImageHtml(0, 1)."</td>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->TitleLink()."</td>
		</tr>";
	}
	
	if ($_POST['ag_doit'])
	{
		$clearComments  = $_POST['ag_clearcomments'];
		$clearRatings   = $_POST['ag_clearratings'];
		$clearScores    = $_POST['ag_clearscores'];
		$clearViewHits  = $_POST['ag_clearviewhits'];
		$clearEmailHits = $_POST['ag_clearemailhits'];
		$clearDesc      = $_POST['ag_cleardescription'];
		
		$cleared = array();
		if ($clearComments)  array_push($cleared, AUTOGAL_LANG_ADMIN_EDIT_89);
		if ($clearRatings)   array_push($cleared, AUTOGAL_LANG_ADMIN_EDIT_90);
		if ($clearScores)    array_push($cleared, AUTOGAL_LANG_ADMIN_EDIT_91);
		if ($clearDesc)      array_push($cleared, AUTOGAL_LANG_ADMIN_EDIT_93);
		if ($clearViewHits)  array_push($cleared, AUTOGAL_LANG_ADMIN_EDIT_94);
		if ($clearEmailHits) array_push($cleared, AUTOGAL_LANG_ADMIN_EDIT_95);
			
		if (count($cleared) <= 0)
		{
			$text .= "<div style='text-align:center'><br />".AUTOGAL_LANG_ADMIN_EDIT_96."<br /></div>";
		}
		else
		{
			foreach ($objs as $mediaObj)
			{
				$mediaObj->LoadMeta();
				
				if ($clearComments)  $mediaObj->ClearComments();
				if ($clearRatings)   $mediaObj->ClearRatings();
				if ($clearScores)    $mediaObj->ArcadeClearTopScores();
				if ($clearViewHits)  $mediaObj->ViewHits(0);
				if ($clearEmailHits) $mediaObj->EmailHits(0);
				if ($clearDesc)      $mediaObj->Description('');
				
				if (!$mediaObj->SaveMeta())
				{
					$msgs[] = "*** ".$mediaObj->LastError();
				}
				else
				{
					$msgs[] = $mediaObj->TypeTitle()." \"".$mediaObj->Title()."\": ".str_replace("[CLEARED]", implode(", ", $cleared), AUTOGAL_LANG_ADMIN_EDIT_97);
					AutoGal_AdminLog(AUTOGAL_LANG_LOG_L8, $mediaObj->Element(), implode(", ", $cleared));
				}
			}
	
			if ($msgs)
			{
				$text .= AutoGal_ReturnMsgsHtml($msgs);
				$text .= AutoGal_BackLink($selObj);
				return $text;
			}
		}
	}
	
	$text .= "
	<br />
	<div style='text-align:center'>
	<b>".AUTOGAL_LANG_ADMIN_EDIT_88."</b><br />
	<br />
	<table class='border'>
	<tr>
		<td class='forumheader3' style='text-align:left'>".AUTOGAL_LANG_ADMIN_EDIT_89."</td><td class='forumheader3'><input type='checkbox' name='ag_clearcomments'></td>
		<td class='forumheader3' style='text-align:left'>".AUTOGAL_LANG_ADMIN_EDIT_90."</td><td class='forumheader3'><input type='checkbox' name='ag_clearratings'></td>
	</tr>
	<tr>
	<tr>
		<td class='forumheader3' style='text-align:left'>".AUTOGAL_LANG_ADMIN_EDIT_91."</td><td class='forumheader3'><input type='checkbox' name='ag_clearscores'></td>
		<td class='forumheader3' style='text-align:left'>".AUTOGAL_LANG_ADMIN_EDIT_93."</td><td class='forumheader3'><input type='checkbox' name='ag_cleardescription'></td>
	</tr>
	<tr>
		<td class='forumheader3' style='text-align:left'>".AUTOGAL_LANG_ADMIN_EDIT_94."</td><td class='forumheader3'><input type='checkbox' name='ag_clearviewhits'></td>
		<td class='forumheader3' style='text-align:left'>".AUTOGAL_LANG_ADMIN_EDIT_95."</td><td class='forumheader3'><input type='checkbox' name='ag_clearemailhits'></td>
	</tr>
	</table>
	<br />
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_5."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_6."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_39."</th>
	</tr>
	$eleListTable
	</table>
	<br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_92."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
	</div>";
	
	return $text;
}

function AutoGal_PageAutoWatermarking($selObj, $objs)
{
	foreach ($objs as $mediaObj)
	{
		if (!$mediaObj->IsGallery()) continue;
		$element = $mediaObj->Element();
		
		$isOn = 1;
		if (file_exists($mediaObj->AbsPath()."/.htaccess"))
		{
			$isOn = 0;
		}
		
		$wmOn[$element] = $isOn;
		
		$galTable .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".
				$mediaObj->ThumbAndTitleHtml()."
			</td>
			<td class='forumheader3' style='text-align:center'>
				<input type='radio' value='yes' name=\"ag_awm_".str_replace('.', '_', rawurlencode($element))."\"".($isOn ? " checked='checked'" : '').">".AUTOGAL_LANG_ADMIN_EDIT_65." 
				<input type='radio' value='no' name=\"ag_awm_".str_replace('.', '_', rawurlencode($element))."\"".(!$isOn ? " checked='checked'" : '').">".AUTOGAL_LANG_ADMIN_EDIT_66."
			</td>
		</tr>";
	}
	
	if ($_POST['ag_doit'])
	{
		foreach ($objs as $mediaObj)
		{
			if (!$mediaObj->IsGallery()) continue;
			
			$element = $mediaObj->Element();
			$postVar = "ag_awm_".str_replace('.', '_', rawurlencode($element));
			$turnAWMon = (strtolower($_POST[$postVar]) == 'yes' ? 1 : 0);
			$prevSetting = $wmOn[$element];
		
			if ($turnAWMon == $prevSetting) continue;
		
			$htAccess = $mediaObj->AbsPath()."/.htaccess";
			
			if ($turnAWMon)
			{
				if (!unlink($htAccess))
				{
					$msgs[] = "*** ".$mediaObj->Title().': '.str_replace("[FILE]", $htAccess, AUTOGAL_LANG_ADMIN_EDIT_69);
				}
				else
				{
					$msgs[] = $mediaObj->Title().': '.str_replace("[FILE]", $htAccess, AUTOGAL_LANG_ADMIN_EDIT_70);
					AutoGal_AdminLog(AUTOGAL_LANG_LOG_L14, $element, AUTOGAL_LANG_ADMIN_EDIT_65);
				}
			}
			else
			{
				$HTACCESS = fopen($htAccess, "w+");
				if (!$HTACCESS)
				{
					$msgs[] = "*** ".$mediaObj->Title().': '.str_replace("[FILE]", $htAccess, AUTOGAL_LANG_ADMIN_EDIT_71);
				}
				else
				{
					$exts = explode('|', AUTOGAL_IMAGEEXTS);
					foreach ($exts as $ext)
					{
						fwrite($HTACCESS, "RemoveHandler .$ext\n");
					}
					fclose($HTACCESS);
					
					$msgs[] = $mediaObj->Title().': '.str_replace("[FILE]", $htAccess, AUTOGAL_LANG_ADMIN_EDIT_72);
					AutoGal_AdminLog(AUTOGAL_LANG_LOG_L14, $element, AUTOGAL_LANG_ADMIN_EDIT_66);
					
					if (chmod($htAccess, octdec(AUTOGAL_PERMSHTACCESS)))
					{
						$msgs[] = $mediaObj->Title().': '.str_replace("[PERMS]", AUTOGAL_PERMSHTACCESS, str_replace("[FILE]", $htAccess, AUTOGAL_LANG_ADMIN_EDIT_76));
					}
					else
					{
						$msgs[] = "*** ".$mediaObj->Title().': '.str_replace("[PERMS]", AUTOGAL_PERMSHTACCESS, str_replace("[FILE]", $htAccess, AUTOGAL_LANG_ADMIN_EDIT_77));
					}
				}
			}
		}
		
		if ($msgs)
		{
			$text .= AutoGal_ReturnMsgsHtml($msgs);
			$text .= AutoGal_BackLink($selObj);
			return $text;
		}
			
		return $text;
	}
	
	if (!$galTable)
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		".AUTOGAL_LANG_ADMIN_EDIT_64."<br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
		
		return $text;
	}
	
	$text = "
	<br />
	<div style='text-align:center'>
	<b>".AUTOGAL_LANG_ADMIN_EDIT_75."</b><br />
	<span class='smalltext'>".AUTOGAL_LANG_ADMIN_EDIT_74."</span><br />
	<br />
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_16."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_67."</th>
	</tr>
	$galTable
	</table>
	<br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_68."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
	</div>";
	
	return $text;
}

function AutoGal_PageUploadThumb($selObj, $objs)
{
	global $pref;
	
	foreach ($objs as $mediaObj)
	{
		$element = $mediaObj->Element();
		if ($mediaObj->IsRoot()) continue;
		
		$imageTable .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>".$mediaObj->ThumbAndTitleHtml()."</td>
			<td class='forumheader3' style='text-align:center'>
				<input class='tbox' type='file' name=\"ag_file_".str_replace('.', '_', rawurlencode($element))."\">
			</td>
		</tr>";
	}
	
	if ($_POST['ag_doit'])
	{
		foreach ($objs as $mediaObj)
		{
			$element = $mediaObj->Element();
			$postVar = "ag_file_".str_replace('.', '_', rawurlencode($element));
			$uploadFile = $_FILES[$postVar]['name'];
			if (!$uploadFile) continue;
			
			$upBasename = basename($uploadFile);
			
			if (!AutoGal_IsImage($uploadFile))
			{
				$msgs[] = "*** ".$mediaObj->Title().': '.$upBasename.' '.AUTOGAL_LANG_ADMIN_EDIT_59;
				continue;
			}
			
			$pathInfo = pathinfo($uploadFile);
			$thumbPath = AutoGal_GetFileThumb($mediaObj->AbsPath(), $pathInfo['extension']);
			$thBasename = basename($thumbPath);

			# Get uploaded file data
			list($uploadOK, $msg) = AutoGal_UploadFile($postVar, $thumbPath);
			
			if (!$uploadOK)
			{
				$msgs[] = $mediaObj->Title().': '.AUTOGAL_LANG_ADMIN_EDIT_61." - $msg";
				continue;
			}
			
			$msgs[] = $mediaObj->Title().': '.str_replace('[PATH]', $thumbPath, AUTOGAL_LANG_ADMIN_EDIT_60);
			AutoGal_AdminLog(AUTOGAL_LANG_LOG_L15, $mediaObj->Element(), basename($uploadFile));
			
			# Chmod thumbnail
			if (chmod($thumbPath, octdec(AUTOGAL_PERMSGALTHUMBS)))
			{
				$msgs[] = $mediaObj->Title().': '.str_replace("[PERMS]", AUTOGAL_PERMSGALTHUMBS, str_replace("[FILE]", basename($thumbPath), AUTOGAL_LANG_ADMIN_EDIT_76));
			}
			else
			{
				$msgs[] = "*** ".$mediaObj->Title().': '.str_replace("[PERMS]", AUTOGAL_PERMSGALTHUMBS, str_replace("[FILE]", $thumbPath, AUTOGAL_LANG_ADMIN_EDIT_77));
			}
			
			# Resize thumbnail
			if ($pref['autogal_autothumb'])
			{
				$imageStats = getimagesize($thumbPath);
				
				if (($imageStats[0] > $pref['autogal_thumbwidth'])||($imageStats[1] > $pref['autogal_thumbheight']))
				{
					$error = AutoGal_ResizeImage($thumbPath, $thumbPath, $pref['autogal_thumbwidth'], $pref['autogal_thumbheight']);
					
					if ($error)
					{
						$msgs[] = $mediaObj->Title().': '.AUTOGAL_LANG_ADMIN_EDIT_61." - $error";
					}
					else
					{
						$msgs[] = $mediaObj->Title().': '.AUTOGAL_LANG_ADMIN_EDIT_63;
					}
				}
			}
			
			$cacheGalleries[] = $mediaObj->Gallery();
			if ($mediaObj->IsGallery()) $cacheGalleries[] = $element;
		}
		
		
		if ($msgs)
		{
			$text .= AutoGal_ReturnMsgsHtml($msgs);
			$text .= AutoGal_BackLink($selObj);
			AutoGal_ClearCacheMenu($cacheGalleries, 0);
			
			return $text;
		}
	}
	
	if (!$imageTable)
	{
		$text = " 
		<div style='text-align:center'>
		<br />
		".AUTOGAL_LANG_ADMIN_EDIT_55."<br />
		<br />
		<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
		</div>";
		
		return $text;
	}
	
	$text = "
	<br />
	<div style='text-align:center'>
	<table class='border' style='width:97%'>
	<tr>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_56."</th>
		<th class='forumheader'>".AUTOGAL_LANG_ADMIN_EDIT_57."</th>
	</tr>
	$imageTable
	</table>
	<br />
	<input type='submit' class='button' name='ag_doit' value='".AUTOGAL_LANG_ADMIN_EDIT_58."'>
	<input type='button' class='button' value='".AUTOGAL_LANG_ADMIN_EDIT_12."' onclick='javascript:history.go(-1)'>
	</div>";
	
	return $text;
}

function AutoGal_PageClearCache($selObj, $objs, $incSubGals)
{
	global $pref;
	if (!$pref['autogal_enabledbcache']) return AUTOGAL_LANG_ADMIN_CACHE_4;
	
	foreach ($objs as $mediaObj)
	{
		if ($mediaObj->IsGallery())
		{
			$galleryList[] = $mediaObj->Element();
		}
		else
		{
			$galleryList[] = $mediaObj->Gallery();
		}
	}
	
	$msgs = AutoGal_ClearCache($galleryList, $incSubGals, 0);
	for ($msgI = 0; $msgI < count($msgs); $msgI ++)
	{
		$msgs[$msgI] = htmlspecialchars($msgs[$msgI]);
	}
	
	$text = implode("<br />", $msgs);
	
	$text .= AutoGal_BackLink($selObj);
	return $text;
}

function AutoGal_PageRegenCache($selObj, $objs, $incSubGals)
{
	global $pref;
	if (!$pref['autogal_enabledbcache']) return AUTOGAL_LANG_ADMIN_CACHE_4;
	
	foreach ($objs as $mediaObj)
	{
		if ($mediaObj->IsGallery())
		{
			$galleryList[] = $mediaObj->Element();
		}
		else
		{
			$galleryList[] = $mediaObj->Gallery();
		}
	}
	
	AutoGal_GenerateCacheMenu($galleryList, $incSubGals);
	
	$text .= AutoGal_BackLink($selObj);
	return $text;
}

?>