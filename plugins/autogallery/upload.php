<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        18/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_MEDIAOBJCLASS);
require_once(HEADERF);

if (!AutoGal_IsUploadAllowed())
{
	$botLinks = AutoGal_GetBotLinks(false);
	require_once(e_HANDLER."userclass_class.php");
	$className = AutoGal_UserClassName($pref['autogal_revuploaduc']);
	
	$text = "
	<div style='text-align:center'>
	<br />
	<b>".AUTOGAL_LANG_UPLOAD_L38."</b><br />
	<br />
	".str_replace("[USERCLASS]", $className, AUTOGAL_LANG_UPLOAD_L41)."<br />
	".(count($botLinks) > 0 ? "<br />".implode(' ', $botLinks) : '')."
	</div>";
	
	$ns -> tablerender($pref['autogal_title'] . " - ".AUTOGAL_LANG_UPLOAD_L20, $text);
	if ($pref['autogal_showfooter']){require_once(FOOTERF);}
	exit;
}

$selGallery = stripslashes(rawurldecode($_POST['gallery'] ? $_POST['gallery'] : $_GET['gallery']));
$galleryObj = new AutoGal_CMediaObj($selGallery);

if (!$galleryObj->IsValid())
{
	$text = "Invalid gallery selected: $selGallery!<br />(".$galleryObj->LastError().")";
	$ns -> tablerender($pref['autogal_title'] . " - ".AUTOGAL_LANG_UPLOAD_L20, $text);
	if ($pref['autogal_showfooter']){require_once(FOOTERF);}
	exit;
}

if ($_POST['ag_issubmited']) AutoGal_ProcessUploadedFiles($galleryObj);
AutoGal_UploadFilesForm($galleryObj);

////////////////////////////////////////////////////////
// FUNCTIONS
////////////////////////////////////////////////////////
function AutoGal_UploadFilesForm ($galleryObj)
{
	global $ns, $pref;
	
	$botLinks = AutoGal_GetBotLinks(false);
	
	# BUILD THE LIST OF GALLERIES USER CAN UPLOAD TO
	$galSelect = AutoGal_GallerySelect($galleryObj->Element(), NULL, ($pref['autogal_checkuploadvclass'] ? 'view' : NULL));
	$galSelect = "<select name='gallery' class='tbox'>$galSelect</select>";
	
	$text = AutoGal_UploadJavascript();
	
	$text .= "
	<div style='text-align:center'>
	<form enctype='multipart/form-data' method='post'>
		<b>".AUTOGAL_LANG_UPLOAD_L35."</b><br />
		$galSelect<br />
		<br />
		".AUTOGAL_LANG_UPLOAD_L8.(str_replace('|', ', ', $pref['autogal_uploadexts'])).".<br />
		".str_replace("[SIZE]", AutoGal_FormatBytes($pref['autogal_uploadmaxsize']), AUTOGAL_LANG_UPLOAD_L42)."<br />
		<br />
		<input type='hidden' name='MAX_FILE_SIZE' value='".$pref['autogal_uploadmaxsize']."'>
		<table class='border' width='97%'>
		<tr>
			<td class='forumheader'>".AUTOGAL_LANG_UPLOAD_L15."</td>
			<td class='forumheader'>".AUTOGAL_LANG_UPLOAD_L24."</td>
			<td class='forumheader'>".AUTOGAL_LANG_UPLOAD_L17."</td>    
		</tr>";
		
		for ($fileNum = 0; $fileNum < $pref['autogal_uploadnumber']; $fileNum ++)
		{
			$text .= "
			<tr>
				<td class='forumheader3'>".($fileNum + 1).".</td>
				<td class='forumheader3'>
					".AUTOGAL_LANG_UPLOAD_L16."<br />
					<input type='text' class='tbox' name='ag_filetitle_$fileNum' size='50'><br />
					<br />".
					AUTOGAL_LANG_UPLOAD_L23."<br />
					<textarea name='ag_filedesc_$fileNum' class='tbox' cols='60' rows='6'></textarea>
				</td>
				<td class='forumheader3'>
					<table>
					<tr>
						<td>
							<input class='tbox' name='ag_file_$fileNum' id='ag_file_$fileNum' type='file' onchange='javascript:ag_checkFileType($fileNum);'>
						</td>
					</tr>
					</table>
					<table id='ag_thumbdiv_$fileNum' style='visibility:hidden;position:absolute;text-align:center'>
					<tr>
						<td >
							<br /><br />
							".AUTOGAL_LANG_UPLOAD_L31."<br />
							<input class='tbox' type='file' name='ag_thumb_$fileNum' id='ag_thumb_$fileNum' onchange='javascript:ag_checkThumbType($fileNum)'>
						</td>
					</tr>
					</table>
				</td>    
			</tr>";
		}
		
		$text .= "
		</table>
		<br />
		<input type='hidden' name='ag_issubmited' value='1'>
		<input type='button' class='button' id='ag_submitimages' name='ag_submitimages' value='".AUTOGAL_LANG_UPLOAD_L18."' onclick=\"javascript:var btn = document.getElementById('ag_submitimages'); btn.disabled=true; btn.value='".AUTOGAL_LANG_UPLOAD_L39."'; form.submit()\">
		</form>
		".(count($botLinks) > 0 ? "<br />".implode(' ', $botLinks) : '')."
		</div>";
	
	$ns -> tablerender($pref['autogal_title'] . " - ".AUTOGAL_LANG_UPLOAD_L20, $text);
	if ($pref['autogal_showfooter']){require_once(FOOTERF);}
}

function AutoGal_ProcessUploadedFiles($galleryObj)
{
	global $ns, $pref;
	
	if ($galleryObj->CheckUserPriv('directupload'))
	{
		$uploadType = 'direct';
	}
	else if ($galleryObj->CheckUserPriv('reviewupload'))
	{
		$uploadType = 'reviewed';
	}
	else
	{
		$text = AUTOGAL_LANG_UPLOAD_L1;
		$ns->tablerender($pref['autogal_title'] . " - ".AUTOGAL_LANG_UPLOAD_L20, $text);
		if ($pref['autogal_showfooter']){require_once(FOOTERF);}
		exit;
	}
		
	$msgs = '';
    for ($fileNum = 0; $fileNum < $pref['autogal_uploadnumber']; $fileNum ++)
    {
        $uploadFile = $_FILES["ag_file_$fileNum"]['name'];
		$uploadThumb = $_FILES["ag_thumb_$fileNum"]['name'];
		$description = stripslashes($_POST["ag_filedesc_$fileNum"]);
        
        if (!$uploadFile) continue;
        
        $uploadTitle = $_POST["ag_filetitle_$fileNum"];
        $imgPathInfo = pathinfo($uploadFile);
        
        $uploadFileNoExt = $imgPathInfo['basename'];
        $uploadFileNoExt = preg_replace("/\.(".$imgPathInfo['extension'].")$/i", '', $uploadFileNoExt);
        $uploadTitle = ($uploadTitle ? $uploadTitle : $uploadFileNoExt);
		        
		$uploadTitle = str_replace("\\'", "'", $uploadTitle);
        if (preg_match("/\/\\\*\<\>\|\:\"\?/", $uploadTitle))
        {
            $msgs .= "<li>".AUTOGAL_LANG_UPLOAD_L2."/\\*&lt;&gt;|:\"?</li>";
        }
		
		$uploadTitle = preg_replace("/^".AUTOGAL_THUMBPREFIX."/", "fun thumb rum", $uploadTitle);

        if (!preg_match("/^(".$pref['autogal_uploadexts'].")$/i", $imgPathInfo['extension']))
        {
			$msgs .= "<li><b>$uploadFile</b> - <font color='red'><b>".AUTOGAL_LANG_UPLOAD_L25."</b></font> ".AUTOGAL_LANG_UPLOAD_L8.(str_replace('|', ', ', $pref['autogal_uploadexts']))."</li>";
			continue;
		}
		
		if ($uploadThumb)
		{
			$thumbPathInfo = pathinfo($uploadThumb);
			if (!preg_match("/^(".AUTOGAL_IMAGEEXTS.")$/i", $thumbPathInfo['extension']))
			{
				$msgs .= "<li><b>$uploadFile</b> - <font color='red'><b>".AUTOGAL_LANG_UPLOAD_L25."</b></font> ".AUTOGAL_LANG_UPLOAD_L30.(str_replace('|', ', ', AUTOGAL_IMAGEEXTS))."</li>";
				continue;
			}
		}
		
		# GET THE USERNAME
		if (USER)
		{
			$username = USERNAME;
			$userID = USERID;
		}
		else
		{
			$username = $_SERVER['REMOTE_ADDR'];
			$userID = 0;
		}
			
		if ($uploadType == 'direct')
		{
			$uploadFile = "$uploadTitle.".$imgPathInfo['extension'];
			if ($uploadThumb) $thumbFile = AUTOGAL_THUMBPREFIX.$uploadTitle.'.'.$imgPathInfo['extension'].'.'.strtolower($thumbPathInfo['extension']);
			$uploadDirRel = $galleryObj->AbsPath();
			$chmodVal = AUTOGAL_PERMSGALMEDIA;
			$chmodXmlVal = AUTOGAL_PERMSGALXML;
		}
		else
		{
			$submitTag = '';
			$uploadFile = $uploadTitle.$submitTag.'.'.$imgPathInfo['extension'];
			if ($uploadThumb) $thumbFile = AUTOGAL_THUMBPREFIX.$uploadTitle.$submitTag.'.'.$imgPathInfo['extension'].'.'.strtolower($thumbPathInfo['extension']);
			$uploadDirRel = AUTOGAL_UPLOADDIRABS;
			$chmodVal = AUTOGAL_PERMSUPLMEDIA;
			$chmodXmlVal = AUTOGAL_PERMSGALXML;
		}
		
		$uploadFile = str_replace(' ', '_', $uploadFile);
		$uploadFile = str_replace("\\", "", $uploadFile);
		$thumbFile = str_replace(' ', '_', $thumbFile);
		$thumbFile = str_replace("\\", "", $thumbFile);
			
		$uploadFileRel = "$uploadDirRel/$uploadFile";
		$uploadDir = $uploadDirRel; 
		$uploadPath = $uploadDir."/$uploadFile";
		if ($uploadThumb) $thumbPath = $uploadDir."/$thumbFile";
		
		#######################
		# CHECK UPLOAD FOLDER #
		#######################
		$uploadDirPermWarn = '<br />';
		if ((!$pref['autogal_chmodwarnoff'])&&(AutoGal_IsMainAdmin()))
		{
			if ($uploadDirPermWarn = IsBadUploadDirPerms()) $ns -> tablerender(AUTOGAL_LANG_UPLOAD_L40, $uploadPerms); 
		}
		
		$uploadRet = AutoGal_UploadFile("ag_file_$fileNum", $uploadPath);
		$uploadOK = $uploadRet[0];
		$uploadMsg = $uploadRet[1];
		
		if ($uploadOK)
		{
			chmod ($uploadPath, octdec($chmodVal));
			if ($pref['image_owner']) chown($uploadPath, $pref['image_owner']);
			
			$msgs .= "<li><b>$uploadTitle</b> - " . AUTOGAL_LANG_UPLOAD_L4 . " " .
			($uploadType == 'reviewed' ? AUTOGAL_LANG_UPLOAD_L5 : AUTOGAL_LANG_UPLOAD_L6."<a href=\"".$galleryObj->Link()."\">".$galleryObj->Title()."</a>").'</li>';
			
			// DO XML STUFF
			$mediaObj = new AutoGal_CMediaObj($uploadPath, true);
			if ($uploadType == 'direct') AutoGal_AdminLog(AUTOGAL_LANG_LOG_L1, $mediaObj->Element());
			
			$mediaObj->SubmitByUsername($username);
			$mediaObj->SubmitByUserID($userID);
			$mediaObj->SubmitDate(time());
			$mediaObj->Description($description);
			$mediaObj->SuggestedGallery($galleryObj->Element());
			
			if (!$mediaObj->SaveMeta())
			{
				$msgs .= "<li><b>$uploadTitle</b> - " . AUTOGAL_LANG_XML_L1." (".$mediaObj->LastError().")</li>\n";
			}
			
			if ($uploadThumb)
			{
				$uploadRet = AutoGal_UploadFile("ag_thumb_$fileNum", $thumbPath);
				$uploadOK = $uploadRet[0];
				$uploadMsg = $uploadRet[1];
				
				if ($uploadOK)
				{
					chmod ($uploadPath, octdec($chmodVal));
					if ($pref['image_owner']) chown($uploadPath, $pref['image_owner']);
					
					$msgs .= "<li><b>$thumbFile</b> - " . AUTOGAL_LANG_UPLOAD_L4."</li>";
					if ($uploadType == 'direct') AutoGal_AdminLog(AUTOGAL_LANG_LOG_L2, $mediaObj->Element(), $thumbPath);
					
					if ($pref['autogal_autothumb'])
					{
						$thumbImgStats = getimagesize();
						if (($thumbImgStats[0] != $pref['autogal_thumbwidth'])||($thumbImgStats[0] != $pref['autogal_thumbheight']))
						{
							$error = AutoGal_ResizeImage($thumbPath, $thumbPath, $pref['autogal_thumbwidth'], $pref['autogal_thumbheight']);
							
							if ($error)
							{
								$msgs .= "<li><b>$thumbFile</b> - <font color='red'><b>".AUTOGAL_LANG_UPLOAD_L25."</b></font> $error</li>";
							}
							else
							{
								$msgs .= "<li><b>$uploadTitle</b> - " . str_replace("[WIDTH]", $pref['autogal_thumbwidth'], str_replace("[HEIGHT]", $pref['autogal_thumbheight'], AUTOGAL_LANG_UPLOAD_L37))."</li>\n";
							}
						}
					}
				}
				else
				{
					$msgs .= "<li><b>$thumbFile</b> - <font color='red'><b>".AUTOGAL_LANG_UPLOAD_L25."</b></font> $uploadMsg</li>";
				}
			}
		}
		else
		{
			$msgs .= "<li><b>$uploadFile</b> - <font color='red'><b>".AUTOGAL_LANG_UPLOAD_L25."</b></font> $uploadMsg</li>";
		}
    }
    
    if ($msgs) $ns -> tablerender($pref['autogal_title'], "<div style='text-align: left'>$uploadDirPermWarn<ul>$msgs</ul></div><br /><div style='text-align: center'><b>".AUTOGAL_LANG_UPLOAD_L29."</b></div>");    
	if ($uploadType == 'direct') AutoGal_ClearCacheMenu($galleryObj->Element(), 0);
}

function AutoGal_UploadJavascript()
{
	global $pref;
	return "
	<script type='text/javascript'>
	function ag_checkFileType(fileNum)
	{
		var fileObj;
		var filePath;
		var fileExtI;
		var fileExt;
		var allowedExtRegex;
		var needThumbExtRegex;
		var thumbDivObj;
		
		fileObj = document.getElementById('ag_file_' + fileNum);
		thumbDivObj = document.getElementById('ag_thumbdiv_' + fileNum);
		filePath = fileObj.value;
		
		if (!filePath) return;
		
		fileExtI = filePath.lastIndexOf('.');
		fileExt = filePath.substr(fileExtI + 1);
		allowedExtRegex = /^(".$pref['autogal_uploadexts'].")/i;
		
		needThumbExtRegex = /^(".AUTOGAL_EXTCLASS_MOVIE.'|'.AUTOGAL_EXTCLASS_AUDIO.'|'.AUTOGAL_EXTCLASS_ANIMATION.")/i;
			
		if (!fileExt.match(allowedExtRegex))
		{
			alert('".AUTOGAL_LANG_UPLOAD_L8.(str_replace('|', ', ', $pref['autogal_uploadexts']))."');
			fileObj.value = '';
			return;
		}
		
		if (fileExt.match(needThumbExtRegex))
		{
			thumbDivObj.style.visibility = 'visible';
			thumbDivObj.style.position = 'relative';
			fileObj.style.visibility = 'visible';
			fileObj.style.position = 'relative';
		}
		else
		{
			thumbDivObj.style.visibility = 'hidden';
			thumbDivObj.style.position = 'absolute';
		}
	}
	
	function ag_checkThumbType(fileNum)
	{
		var thumbObj;
		var filePath;
		var fileExtI;
		var fileExt;
		
		thumbObj = document.getElementById('ag_thumb_' + fileNum);
		filePath = thumbObj.value;
		
		if (!filePath) return;
		
		fileExtI = filePath.lastIndexOf('.');
		fileExt = filePath.substr(fileExtI + 1);
		allowedExtRegex = /^(".AUTOGAL_IMAGEEXTS.")/i;
		
		if (!fileExt.match(allowedExtRegex))
		{
			alert('".AUTOGAL_LANG_UPLOAD_L30.(str_replace('|', ', ', AUTOGAL_IMAGEEXTS))."');
			thumbObj.value = '';
			return;
		}
	}
	</script>";
}
?>