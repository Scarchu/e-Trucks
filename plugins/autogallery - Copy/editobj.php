<?php

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_MEDIAOBJCLASS);
require_once(AUTOGAL_ADMINFUNCTIONS);

//

function AutoGal_ReturnMsgsHtml($msgs)
{
	if (!$msgs) return;
	
	$text = "<div style='font-family:courier;text-align:left'><ol>\n";
	foreach ($msgs as $msg)
	{
		$msgLine = htmlspecialchars($msg);
		if (preg_match("/\*\*\*/", $msg)) $msgLine = "<span style='color:red'>$msgLine</span>";
		$text .= "<li>$msgLine</li>\n";
	}
	$text .= "</ol></div>\n";
	
	return $text;
}

function AutoGal_EditRename(&$mediaObj, $newTitle, $newNum)
{
    if ($mediaObj->IsRoot()) 
	{
		$msgs[] = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L124;
		return $msgs;
	}
	
	if (!$mediaObj->IsValid()) 
	{
		$msgs[] = "*** ".str_replace("[ELEMENT]", htmlspecialchars($mediaObj->Element()), AUTOGAL_LANG_ADMIN_FUNCTIONS_L123);
		return $msgs;
	}
	
	$element = $mediaObj->Element();
    $absPath = $mediaObj->AbsPath();
	
	$newFilename = (preg_match("/^\d+$/", $newNum) ? $newNum.'.' : '').($mediaObj->IsFile() ? str_replace(' ', '_', $newTitle).".".$mediaObj->Extension() : $newTitle);
	$newElement = ($mediaObj->IsInRoot() ? '' : $mediaObj->Gallery().'/').$newFilename;
	
	$illegalChars = AutoGal_IsIllegalName($newFilename);
    if ($illegalChars)
    {
		$msg = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L101;
		$msg = str_replace('[TYPE]', $mediaObj->FileTypeTitle(), $msg);
		$msg = str_replace('[FILE]', $newElement, $msg);
		$msg = str_replace('[CHARS]', $illegalChars, $msg);
		
		$msgs[] = $msg;
    }
	else
	{
		$msgs = AutoGal_EditChangeElement($mediaObj, $newElement);
	}
		
	return $msgs;
}

function AutoGal_EditChangeElement(&$mediaObj, $newElement)
{
	$debug = 0;
	
	if ($mediaObj->IsRoot()) 
	{
		$msgs[] = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L124;
		return $msgs;
	}
	
	if (!$mediaObj->IsValid()) 
	{
		$msgs[] = "*** ".str_replace("[ELEMENT]", htmlspecialchars($mediaObj->Element()), AUTOGAL_LANG_ADMIN_FUNCTIONS_L123);
		return $msgs;
	}
	
	$element = $mediaObj->Element();
    $absPath = $mediaObj->AbsPath();
	$newAbsPath = AutoGal_GetAbsGalPath($newElement, 0);
		
	// RENAME FILE
	if (file_exists($newAbsPath))
	{
		$msg = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L120.': '.AUTOGAL_LANG_ADMIN_FUNCTIONS_L125;
		$msg = str_replace('[TYPE]', $mediaObj->TypeTitle(), $msg);
		$msg = str_replace('[FROMFILE]', $absPath, $msg);
		$msg = str_replace('[TOFILE]', $newAbsPath, $msg);
        $msgs[] = $msg;
		
		return $msgs;
	}
	
	if ($debug) print "rename($absPath, $newAbsPath)<br />";
	if (rename($absPath, $newAbsPath))
	{ 
		$msg = AUTOGAL_LANG_ADMIN_FUNCTIONS_L119;
		$msg = str_replace('[TYPE]', $mediaObj->TypeTitle(), $msg);
		$msg = str_replace('[FROMFILE]', $element, $msg);
		$msg = str_replace('[TOFILE]', $newElement, $msg);
        $msgs[] = $msg;
		
		$newMediaObj = new AutoGal_CMediaObj($newElement);
		AutoGal_AdminLog(AUTOGAL_LANG_LOG_L9, $mediaObj->Element, $newMediaObj->Element());
		
		// RENAME XML META FILE
		if (($mediaObj->IsFile())&&($mediaObj->XmlFileExists()))
		{
			if (file_exists($newMediaObj->XmlFilePath())) unlink($newMediaObj->XmlFilePath());
			
			if ($debug) print "rename(".$mediaObj->XmlFilePath().", ".$newMediaObj->XmlFilePath().")<br />";
			if (rename($mediaObj->XmlFilePath(), $newMediaObj->XmlFilePath()))
			{
				$msg = AUTOGAL_LANG_ADMIN_FUNCTIONS_L119; 
				$msg = str_replace('[FROMFILE]', $mediaObj->XmlFileElePath(), $msg);
				$msg = str_replace('[TOFILE]',  $newMediaObj->XmlFileElePath(), $msg);
			}
			else
			{
				$msg = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L120;
				$msg = str_replace('[FROMFILE]', $mediaObj->XmlFilePath(), $msg);
				$msg = str_replace('[TOFILE]', $newMediaObj->XmlFilePath(), $msg);
			}
			
			$msg = str_replace('[TYPE]', AUTOGAL_LANG_ADMIN_FUNCTIONS_L121, $msg);
			$msgs[] = $msg;
		}
        
		// RENAME THUMBNAIL
        if (($mediaObj->IsFile())&&($mediaObj->ThumbImageExists()))
        {
			$thumbPath = $mediaObj->ThumbImagePath();
			$thumbPathInfo = pathinfo($thumbPath);
			
			$newThumbFilename = AUTOGAL_THUMBPREFIX.basename($newAbsPath).($mediaObj->FileType() != 'image' ? '.'.$thumbPathInfo['extension'] : '');
			$newThumbPath = dirname($newMediaObj->AbsPath()).'/'.$newThumbFilename;
		
			if (file_exists($newThumbPath)) unlink($newThumbPath);
			
			if ($debug) print "rename($thumbPath, $newThumbPath)<br />";
			if (rename($thumbPath, $newThumbPath))
			{
				$msg = AUTOGAL_LANG_ADMIN_FUNCTIONS_L119; 
				$msg = str_replace('[FROMFILE]', ($mediaObj->IsInRoot() ? '' : dirname($mediaObj->Element())."/").basename($thumbPath), $msg);
				$msg = str_replace('[TOFILE]', ($newMediaObj->IsInRoot() ? '' : dirname($newMediaObj->Element())."/").basename($newThumbPath), $msg);
			}
			else
			{
				$msg = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L120; 
				$msg = str_replace('[FROMFILE]', $thumbPath, $msg);
				$msg = str_replace('[TOFILE]',  $newThumbPath, $msg);
			}
			
			$msg = str_replace('[TYPE]', AUTOGAL_LANG_ADMIN_FUNCTIONS_L122, $msg);
			$msgs[] = $msg;
        }
		
		// RENAME PREVIEW IMAGE
        if (($mediaObj->IsFile())&&($mediaObj->FileType() == 'image')&&($mediaObj->PreviewImageExists()))
        {
			$pvImagePath = $mediaObj->PreviewImagePath();
			
			$newPvFilename = AUTOGAL_PREVIEWIMGPREFIX.basename($newAbsPath);
			$newPvPath = dirname($newMediaObj->AbsPath()).'/'.$newPvFilename;
		
			if (file_exists($newPvPath)) unlink($newPvPath);
			
			if ($debug) print "rename($pvImagePath, $newPvPath)<br />";
			if (rename($pvImagePath, $newPvPath))
			{
				$msg = AUTOGAL_LANG_ADMIN_FUNCTIONS_L119; 
				$msg = str_replace('[FROMFILE]', ($mediaObj->IsInRoot() ? '' : dirname($mediaObj->Element())."/").basename($mediaObj->PreviewImagePath()), $msg);
				$msg = str_replace('[TOFILE]', ($newMediaObj->IsInRoot() ? '' : dirname($newMediaObj->Element())."/").$newPvFilename, $msg);
			}
			else
			{
				$msg = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L120; 
				$msg = str_replace('[FROMFILE]', $pvImagePath, $msg);
				$msg = str_replace('[TOFILE]',  $newPvPath, $msg);
			}
			
			$msg = str_replace('[TYPE]', AUTOGAL_LANG_ADMIN_FUNCTIONS_L126, $msg);
			$msgs[] = $msg;
		}
		
		$mediaObj = $newMediaObj;
	}
	else
	{
		$msg = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L120;
		$msg = str_replace('[TYPE]', $mediaObj->TypeTitle(), $msg);
		$msg = str_replace('[FROMFILE]', $absPath, $msg);
		$msg = str_replace('[TOFILE]', $newAbsPath, $msg);
        $msgs[] = $msg;
	}
	
	return $msgs;
}

function AutoGal_EditDelete($mediaObj)
{
	if ($mediaObj->IsRoot()) 
	{
		$msgs[] = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L124;
		return $msgs;
	}
	
	if (!$mediaObj->IsValid()) 
	{
		$msgs[] = "*** ".str_replace("[ELEMENT]", htmlspecialchars($mediaObj->Element()), AUTOGAL_LANG_ADMIN_FUNCTIONS_L123);
		return $msgs;
	}
	
	$element = $mediaObj->Element();
    $absPath = $mediaObj->AbsPath();
    
	// DELETE FILE
    if ($mediaObj->IsFile())
    {
        if (unlink($absPath))
        {
			$msgs[] = str_replace('[FILE]', $element, str_replace("[TYPE]", $mediaObj->TypeTitle(), AUTOGAL_LANG_ADMIN_FUNCTIONS_L127)); 
			AutoGal_AdminLog(AUTOGAL_LANG_LOG_L10, $element);
        }
        else
        {
            $msgs[] = "*** ".str_replace('[FILE]', $absPath, str_replace("[TYPE]", $mediaObj->TypeTitle(), AUTOGAL_LANG_ADMIN_FUNCTIONS_L128));
        }
		
		// DELETE THUMBNAIL IMAGE
		if ($mediaObj->ThumbImageExists())
		{
			if (unlink($mediaObj->ThumbImagePath())){
				$msgs[] = str_replace('[FILE]', ($mediaObj->IsInRoot() ? '' : dirname($mediaObj->Element())."/").basename($mediaObj->ThumbImagePath()), str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L122, AUTOGAL_LANG_ADMIN_FUNCTIONS_L127)); 
			}else{
				$msgs[] = "*** ".str_replace('[FILE]', $mediaObj->ThumbImagePath(), str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L122, AUTOGAL_LANG_ADMIN_FUNCTIONS_L128));     
			}
		}
		
		// DELETE PREVIEW IMAGE
        if (($mediaObj->IsFile())&&($mediaObj->FileType() == 'image')&&($mediaObj->PreviewImageExists()))
        {
			if (unlink($mediaObj->PreviewImagePath())){
				$msgs[] = str_replace('[FILE]', ($mediaObj->IsInRoot() ? '' : dirname($mediaObj->Element())."/").basename($mediaObj->PreviewImagePath()), str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L126, AUTOGAL_LANG_ADMIN_FUNCTIONS_L127)); 
			}else{
				$msgs[] = "*** ".str_replace('[FILE]', $mediaObj->PreviewImagePath(), str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L126, AUTOGAL_LANG_ADMIN_FUNCTIONS_L128));     
			}
		}
    }
    else
    {
        if ($delError = AutoGal_DelDir($absPath))
		{
            $msgs[] = "*** ".str_replace('[FILE]', $absPath, str_replace("[TYPE]", $mediaObj->TypeTitle(), AUTOGAL_LANG_ADMIN_FUNCTIONS_L128));   
        }
		else
		{
            $msgs[] = str_replace('[FILE]', $element, str_replace("[TYPE]", $mediaObj->TypeTitle(), AUTOGAL_LANG_ADMIN_FUNCTIONS_L127));    
			AutoGal_AdminLog(AUTOGAL_LANG_LOG_L11, $element);
        }
    }
	
	// DELETE XML META FILE
	if (($mediaObj->IsFile())&&($mediaObj->XmlFileExists()))
	{
		if (unlink($mediaObj->XmlFilePath())){
			$msgs[] = str_replace('[FILE]', ($mediaObj->IsInRoot() ? '' : dirname($mediaObj->Element())."/").basename($mediaObj->XmlFilePath()), str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L121, AUTOGAL_LANG_ADMIN_FUNCTIONS_L127)); 
		}else{
			$msgs[] = "*** ".str_replace('[FILE]', $mediaObj->XmlFilePath(), str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L121, AUTOGAL_LANG_ADMIN_FUNCTIONS_L128));     
		}
	}
    
    return $msgs;
}

function AutoGal_EditMove($mediaObj, $toGalleryObj)
{
	if ($mediaObj->IsRoot()) 
	{
		$msgs[] = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L124;
		return $msgs;
	}
	
	if (!$mediaObj->IsValid()) 
	{
		$msgs[] = "*** ".str_replace("[ELEMENT]", htmlspecialchars($mediaObj->Element()), AUTOGAL_LANG_ADMIN_FUNCTIONS_L123);
		return $msgs;
	}
	
	if (!$toGalleryObj->IsValid()) 
	{
		$msgs[] = "*** ".str_replace("[ELEMENT]", htmlspecialchars($mediaObj->Element()), AUTOGAL_LANG_ADMIN_FUNCTIONS_L123);
		return $msgs;
	}
	
	if (!$toGalleryObj->IsGallery()) 
	{
		$msgs[] = "*** ".str_replace("[ELEMENT]", htmlspecialchars($mediaObj->Element()), AUTOGAL_LANG_ADMIN_FUNCTIONS_L123);
		return $msgs;
	}
	
	$newElement = ($toGalleryObj->Element() ? $toGalleryObj->Element()."/" : '').$mediaObj->BaseName();
	
	if ($newElement == $mediaObj->Element())
	{
		$msgs[] = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L89;
		return $msgs;
	}
	
	return AutoGal_EditChangeElement($mediaObj, $newElement);
}

function AutoGal_EditSetGalleryThumbnail(&$gallObj, $thumbObj)
{
	if ($gallObj->IsRoot()) 
	{
		$msgs[] = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L124;
		return $msgs;
	}
	
	if (!$gallObj->IsValid()) 
	{
		$msgs[] = "*** ".str_replace("[ELEMENT]", htmlspecialchars($gallObj->Element()), AUTOGAL_LANG_ADMIN_FUNCTIONS_L123);
		return $msgs;
	}
	
	if (!$thumbObj->IsValid()) 
	{
		$msgs[] = "*** ".str_replace("[ELEMENT]", htmlspecialchars($thumbObj->Element()), AUTOGAL_LANG_ADMIN_FUNCTIONS_L123);
		return $msgs;
	}
	
	if ($thumbObj->FileType() != 'image') 
	{
		$msgs[] = "*** ".str_replace("[ELEMENT]", htmlspecialchars($thumbObj->Element()), AUTOGAL_LANG_ADMIN_FUNCTIONS_L123);
		return $msgs;
	}
	
	if ($gallObj->ThumbImageExists())
	{
		if (unlink($gallObj->ThumbImagePath()))
		{
			$msg = AUTOGAL_LANG_ADMIN_FUNCTIONS_L127;
			$msg = str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L122, $msg);
			$msg = str_replace("[FILE]", $gallObj->ThumbImageElePath(), $msg);
			$msgs[] = $msg;
		}
		else
		{
			$msg = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L128;
			$msg = str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L122, $msg);
			$msg = str_replace("[FILE]", $gallObj->ThumbImagePath(), $msg);
			$msgs[] = $msg;
		}
	}
	
	$newThumbFile = AUTOGAL_GALLERYTHUMBFILENAME.'.'.strtolower($thumbObj->Extension());
	$newThumbPath = $gallObj->AbsPath().'/'.$newThumbFile;
	
	if (copy($thumbObj->AbsPath(), $newThumbPath))
	{
		$msg = AUTOGAL_LANG_ADMIN_FUNCTIONS_L131;
		$msg = str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L122, $msg);
		$msg = str_replace("[FROMFILE]", $thumbObj->Element(), $msg);
		$msg = str_replace("[TOFILE]", $gallObj->Element()."/".$newThumbFile, $msg);
		$msgs[] = $msg;
		
		AutoGal_AdminLog(AUTOGAL_LANG_LOG_L12, $gallObj->Element(), str_replace("[FILE]", $thumbObj->Element(), AUTOGAL_LANG_ADMIN_EDIT_103));
		
		$imageStats = getimagesize($newThumbPath);
		$width = $imageStats[0];
		$height = $imageStats[1];
		
		if (($width > AUTOGAL_GALTHUMBWIDTH)||($height > AUTOGAL_GALTHUMBHEIGHT))
		{
			if ($error = AutoGal_ResizeImage($newThumbPath, $newThumbPath, AUTOGAL_GALTHUMBWIDTH, AUTOGAL_GALTHUMBHEIGHT, AUTOGAL_KEEPASPECT))
			{
				$msg = AUTOGAL_LANG_ADMIN_FUNCTIONS_L130;
				$msg = str_replace("[FILE]", $newThumbFile, $msg);
				$msg = str_replace("[WIDTH]", AUTOGAL_GALTHUMBWIDTH, $msg);
				$msg = str_replace("[HEIGHT]", AUTOGAL_GALTHUMBHEIGHT, $msg);
				$msg = str_replace("[ERROR]", $error, $msg);
			}
			else
			{
				$msg = AUTOGAL_LANG_ADMIN_FUNCTIONS_L129;
				$msg = str_replace("[FILE]", $gallObj->Element()."/".$newThumbFile, $msg);
				$msg = str_replace("[WIDTH]", AUTOGAL_GALTHUMBWIDTH, $msg);
				$msg = str_replace("[HEIGHT]", AUTOGAL_GALTHUMBHEIGHT, $msg);
			}
			
			$msgs[] = $msg;
		}
	}
	else
	{
		$msg = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L132;
		$msg = str_replace("[TYPE]", AUTOGAL_LANG_ADMIN_FUNCTIONS_L122, $msg);
		$msg = str_replace("[FROMFILE]", $thumbObj->AbsPath(), $msg);
		$msg = str_replace("[TOFILE]", $newThumbPath, $msg);
		$msgs[] = $msg;
	}

	return $msgs;
}

function AutoGal_CreateGallery(&$inGalObj, $newGalleryName, &$newGalObj)
{
	$inGallery = $inGalObj->Element();
	
	if ($illegalChars = AutoGal_IsIllegalName($newGalleryName))
    {
        $msgs[] = "*** ".str_replace('[CHARS]', $illegalChars, AUTOGAL_LANG_ADMIN_FUNCTIONS_L70);
		return $msgs;
	}

	$parGalAbsPath = $inGalObj->AbsPath();
	$newGalleryPath = $parGalAbsPath.'/'.$newGalleryName;
	$newGalleryPath = str_replace('//', '/', $newGalleryPath);
	
	$createOK = 0;
	if (is_dir($newGalleryPath))
	{
		$msgs[] = "*** ".str_replace('[PATH]', $newGalleryPath, AUTOGAL_LANG_ADMIN_FUNCTIONS_L133);
		return $msgs;
	}
	else if (mkdir($newGalleryPath))
	{
		$newElement = AutoGal_GetElement($newGalleryPath);
		$newGalObj = new AutoGal_CMediaObj($newElement);
		$newGalObj->LoadMeta();
		
		if ($newGalObj->IsValid())
		{
			$createOK = 1;
			
			touch("$newGalleryPath/index.html");
			$msgs[] = str_replace('[GALLERY]', $newGalleryName, str_replace('[PARENT]', $inGalObj->Title(), AUTOGAL_LANG_ADMIN_FUNCTIONS_L73));                
			AutoGal_AdminLog(AUTOGAL_LANG_LOG_L13, $newGalObj->Element());
		
			if (chmod($newGalleryPath, octdec(AUTOGAL_PERMSGALDIR))){
				$msgs[] = str_replace('[GALLERY]', $newGalleryName, AUTOGAL_LANG_ADMIN_FUNCTIONS_L75);
			}else{
				$msgs[] = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L48." ".str_replace('[GALLERY]', $newGalleryName, AUTOGAL_LANG_ADMIN_FUNCTIONS_L76);
			}
			
			$newGalObj->InheritFromParent($inGalObj);
			
			if (!$newGalObj->SaveMeta())
			{
				$msgs[] .= "*** ".$newGalObj->LastError();
			}
		}
	}
	
	if (!$createOK)
	{
		$parentGal = ($inGallery ? AUTOGAL_LANG_ADMIN_FUNCTIONS_L71." \"$inGallery\"" : AUTOGAL_LANG_ADMIN_FUNCTIONS_L72);
		$msgs[] = "*** ".AUTOGAL_LANG_ADMIN_FUNCTIONS_L48." ".str_replace('[GALLERY]', $newGalleryPath, str_replace('[PARENT]', $parGalAbsPath, AUTOGAL_LANG_ADMIN_FUNCTIONS_L77));            
	}
	
	return $msgs;
}

?>