<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org).
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        06/08/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_MEDIAOBJCLASS);
require_once(e_HANDLER."userclass_class.php");

if (AutoGal_IsMainAdmin())
{
	require_once(e_ADMIN."auth.php");
	if(!getperms("P")){ header("location:".e_BASE."index.php"); }
}
else
{
	define("e_PAGETITLE", AUTOGAL_TITLE.AUTOGAL_LANG_ADMIN_REVIEW_L1);
	require_once(HEADERF);
}


if (!AutoGal_IsReviewAllowed())
{
	$ns -> tablerender(AUTOGAL_LANG_ADMIN_REVIEW_L2, "<div style='text-align:center'><b>".AUTOGAL_LANG_ADMIN_REVIEW_L3."</b></div>");
	if (AUTOGAL_SHOW_FOOTER){require_once(FOOTERF);}
	exit;
}

# Get galleries
$galOpts = AutoGal_GallerySelectOpts();

$dh = opendir(AUTOGAL_UPLOADDIRABS);
while ($file = readdir($dh))
{
    if (!AutoGal_IsMediaFile($file)) continue;
	$filePath = AUTOGAL_UPLOADDIRABS."/$file";
	$files[] = $filePath;
}
closedir($dh);

sort($files);
for ($fileID = 0; $fileID < count($files); $fileID ++)
{
	$filePath = $files[$fileID];
	$fileTitle = stripslashes(AutoGal_GetHtmlVar("title_$fileID"));
	$fileDesc = stripslashes(AutoGal_GetHtmlVar("description_$fileID"));
	$fileOp = AutoGal_GetHtmlVar("op_$fileID");
	$gallery = AutoGal_GetHtmlVar("gallery_$fileID");
	
	$mediaObj = new AutoGal_CMediaObj($filePath, true);
		
	# Create a thumbnail if one doesn't exist
	if (($mediaObj->FileType() == 'image')&&(!$mediaObj->ThumbImageExists()))
	{
		$error = AutoGal_ResizeImage($mediaObj->AbsPath(), $mediaObj->ThumbImagePath(), AUTOGAL_THUMBWIDTH, AUTOGAL_THUMBHEIGHT);
		
		if ($error) 
		{
			$text .= "<tr><td colspan='3' class='forumheader3'><font color='red'><b>".AUTOGAL_LANG_UPLOAD_L25."</b></font> $error</td></tr>";
		}
		else
		{
			$mediaObj = new AutoGal_CMediaObj($filePath, true);
		}
	}
	# Resize non-image thumbnail to the correct size
	else if (($mediaObj->FileType() != 'image')&&($mediaObj->ThumbImageExists()))
	{
		$imageStats = getimagesize($mediaObj->ThumbImagePath());
		$thumbWidth = $imageStats[0];
		$thumbHeight = $imageStats[1];
	
		if (($thumbWidth > AUTOGAL_THUMBWIDTH)||($thumbHeight > AUTOGAL_THUMBHEIGHT))
		{
			$error = AutoGal_ResizeImage($mediaObj->ThumbImagePath(), $mediaObj->ThumbImagePath(), AUTOGAL_THUMBWIDTH, AUTOGAL_THUMBHEIGHT);
		
			if ($error) 
			{
				$text .= "<tr><td colspan='3' class='forumheader3'><font color='red'><b>".AUTOGAL_LANG_UPLOAD_L25."</b></font> $error</td></tr>";
			}
		}
	}
	
	# Do moderations
	if (($_POST['moderateimages'])&&($fileOp != 'leave'))
	{
		$text .= "<tr><td colspan='3' class='forumheader3'>";
		if ($fileOp == 'delete')
		{
			$text .= AutoGal_ReviewDelete($mediaObj);
		}
		else
		{
			$text .= AutoGal_ReviewAccept($mediaObj, $gallery, $fileTitle, $fileDesc);
		}
		$text .= "</td></tr>\n";
	}
	else
	{
		$viewLink = ($mediaObj->ThumbImageExists() ? "<a href=\"".$mediaObj->Url()."\" target='_base'>".$mediaObj->ThumbImageHtml()."</a><br />" : '');
		$viewLink .= "[<a href=\"".$mediaObj->Url()."\" target='_base'>".AUTOGAL_LANG_ADMIN_REVIEW_L18."</a>]";

		$submitByUsername = $mediaObj->SubmitByUsername();
		$suggestedGallery = $mediaObj->SuggestedGallery();
		
		$galSelect = AutoGal_GallerySelect($suggestedGallery, NULL, NULL, $galOpts);
		
		$text .= "
		<tr>
			<td class='forumheader3' style='text-align:center'>$viewLink</td>
			<td class='forumheader3'>
				<input size='50' class='tbox' name=\"title_$fileID\" value=\"".$mediaObj->Title()."\">
				<br /><br />".
				(isset($submitByUsername) ? "
				<b>".AUTOGAL_LANG_ADMIN_REVIEW_L19."</b> ".$mediaObj->SubmitByLink()."<br />
				<b>".AUTOGAL_LANG_ADMIN_REVIEW_L20."</b> ".strftime(AUTOGAL_SUBMITTIMEFORMAT, $mediaObj->SubmitDate())."<br />
				<b>".AUTOGAL_LANG_ADMIN_REVIEW_L21."</b><br />" : '')."
				
				<textarea class=\"tbox\" name=\"description_$fileID\" cols=\"45\" rows=\"5\">".
				htmlspecialchars($mediaObj->Description()).
				"</textarea>
			</td>
			<td class='forumheader3' style='text-align:left'>
				<input type='radio' id='typeleave_$fileID'  name='op_$fileID' value='leave'  onClick=\"javascript:document.getElementById('gallery_$fileID').disabled = !document.getElementById('typemove_$fileID').checked\"".(($fileOp == 'leave')||(!$fileOp) ? " checked='checked'" : '')."> <label for='typeleave_$fileID'>".AUTOGAL_LANG_ADMIN_REVIEW_L22."</label><br />
				<input type='radio' id='typedelete_$fileID' name='op_$fileID' value='delete' onClick=\"javascript:document.getElementById('gallery_$fileID').disabled = !document.getElementById('typemove_$fileID').checked\"".($fileOp == 'delete' ? " checked='checked'" : '')."> <label for='typedelete_$fileID'>".AUTOGAL_LANG_ADMIN_REVIEW_L23."</label><br />
				<input type='radio' id='typemove_$fileID'   name='op_$fileID' value='move'   onClick=\"javascript:document.getElementById('gallery_$fileID').disabled = !document.getElementById('typemove_$fileID').checked\"".($fileOp == 'moved' ? " checked='checked'" : '')."> <label for='typemove_$fileID'>".AUTOGAL_LANG_ADMIN_REVIEW_L34."</label>
				<select id='gallery_$fileID' name=\"gallery_$fileID\" class='tbox' id=\"gallery_$fileID\" disabled='1'>
				$galSelect
				</select>
			</td> 
		</tr>";
	}
}

$botLinks = AutoGal_GetBotLinks('', false);

if ($files)
{
    $text = "
    <div style='text-align:center'>
    <form method='post' action='".e_SELF."'>
    <br />
    <table style='width:85%' class='fborder' colspan='3'>
    <tr>
        <th class='forumheader'>".AUTOGAL_LANG_ADMIN_REVIEW_L24."</th>
        <th class='forumheader'>".AUTOGAL_LANG_ADMIN_REVIEW_L25."</th>
        <th class='forumheader'>".AUTOGAL_LANG_ADMIN_REVIEW_L26."</th>
    </tr>
    $text
    <tr style='vertical-align:top'>
        <td colspan='3' style='text-align:center' class='forumheader'>
            <input class='button' type='submit' name='moderateimages' value='".AUTOGAL_LANG_ADMIN_REVIEW_L27."'>
        </td>
    </tr>
    </table>"
    .(count($botLinks) > 0 ? "<br />".implode(' ', $botLinks) : '').
    "</form>
    </div>";
}
else
{
    $text = "<div style='text-align:center'><br /><b>".AUTOGAL_LANG_ADMIN_REVIEW_L28."</b><br />"
    .(count($botLinks) > 0 ? "<br />".implode(' ', $botLinks) : '').
    "<br /></div>";
}

$ns -> tablerender(AUTOGAL_LANG_ADMIN_REVIEW_L29, $text);

if (AutoGal_IsMainAdmin())
{
	require_once(e_ADMIN."footer.php");
}
else
{
	if (AUTOGAL_SHOW_FOOTER){require_once(FOOTERF);}
}

function AutoGal_ReviewDelete(&$mediaObj)
{
	if (unlink($mediaObj->AbsPath()))
	{
		$text .= str_replace('[FILE]', $mediaObj->BaseName(), AUTOGAL_LANG_ADMIN_REVIEW_L4)."\n";
		AutoGal_AdminLog(AUTOGAL_LANG_LOG_L16, $mediaObj->Element());
	}
	else
	{
	   $text .= str_replace('[FILE]', $mediaObj->BaseName(), AUTOGAL_LANG_ADMIN_REVIEW_L5)."\n";
	}
	
	if ($mediaObj->ThumbImageExists())
	{
		if (unlink($mediaObj->ThumbImagePath()))
		{
			$text .= "<br />".str_replace('[FILE]', basename($mediaObj->ThumbImagePath()), AUTOGAL_LANG_ADMIN_REVIEW_L4)."\n";
		}
		else
		{
			$text .= "<br />".str_replace('[FILE]', basename($mediaObj->ThumbImagePath()), AUTOGAL_LANG_ADMIN_REVIEW_L5)."\n";
		}
	}
	
	if ($mediaObj->XmlFileExists())
	{
		if (unlink($mediaObj->XmlFilePath()))
		{
			$text .= "<br />".str_replace('[XMLFILE]', basename($mediaObj->XmlFilePath()), AUTOGAL_LANG_ADMIN_REVIEW_L6)."\n";
		}
		else
		{
			$text .= "<br />".str_replace('[XMLFILE]', basename($mediaObj->XmlFilePath()), AUTOGAL_LANG_ADMIN_REVIEW_L7)."\n";
		}
	}
	
	return $text;
}

function AutoGal_ReviewAccept(&$mediaObj, $gallery, $title, $description)
{
	$galleryObj = new AutoGal_CMediaObj($gallery);
	
	$newFilename = str_replace(' ', '_', $title.'.'.$mediaObj->Extension()); 
	$newFilename = preg_replace("/[\/\\\*\<\>\|\:\"\?]/", "", $newFilename); 
	$newPath = $galleryObj->AbsPath()."/$newFilename";
	
	if (file_exists($newPath))
	{
		$text .= "<br />".str_replace('[PATH]', $newPath, AUTOGAL_LANG_ADMIN_REVIEW_L31);
	}
	else if (rename($mediaObj->AbsPath(), $newPath))
	{
		$oldBasename = $mediaObj->BaseName();
		$text .= str_replace('[FILE]', $mediaObj->BaseName(), AUTOGAL_LANG_ADMIN_REVIEW_L8)."[<a href='".$galleryObj->Link()."'>".$galleryObj->Title()."</a>]";
		
		# Chmod the file
		if (!chmod($newPath, octdec(AUTOGAL_PERMSGALMEDIA)))
		{
			$text .= "<br />".str_replace('[PATH]', $newPath, AUTOGAL_LANG_ADMIN_REVIEW_L9);
		}
		
		# Check if we have a thumbnail
		if ($mediaObj->ThumbImageExists())
		{
			$oldThumbImagePath = $mediaObj->ThumbImagePath();
		}
		
		# Move the XML file
		if ($mediaObj->XmlFileExists())
		{
			$newXmlPath = "$newPath.xml";
			
			if (!rename($mediaObj->XmlFilePath(), "$newPath.xml"))
			{
				$text .= "<br />".str_replace('[FROMPATH]', $mediaObj->XmlFilePath(), str_replace("[TOPATH]", $newXmlPath, AUTOGAL_LANG_ADMIN_REVIEW_L35));
			}
			else
			{
				# Chmod the thumbnail
				if (!chmod($newXmlPath, octdec(AUTOGAL_PERMSGALXML)))
				{
					$text .= "<br />".str_replace('[PATH]', $newXmlPath, AUTOGAL_LANG_ADMIN_REVIEW_L9);
				}
			}
		}
		
		# Reload the object
		$mediaObj = new AutoGal_CMediaObj(($galleryObj->Element() ? $galleryObj->Element()."/" : '').$newFilename);
		AutoGal_AdminLog(AUTOGAL_LANG_LOG_L17, $mediaObj->Element(), str_replace("[FROMFILE]", $oldBasename, AUTOGAL_LANG_ADMIN_FUNCTIONS_L137));
		
		# Set the description
		if (($description)||($mediaObj->XmlFileExists()))
		{
			$mediaObj->Description($description);
			
			if (!$mediaObj->SaveMeta())
			{
				$text .= "<br />".AUTOGAL_LANG_ADMIN_REVIEW_L12.$mediaObj->LastError()."!";
			}
			else
			{
				$text .= "<br />".AUTOGAL_LANG_ADMIN_REVIEW_L11;
			}
		}
		
		# Move the thumbnail
		if ($oldThumbImagePath)
		{
			if (rename($oldThumbImagePath, $mediaObj->ThumbImagePath()))
			{
				$text .= "<br />".AUTOGAL_LANG_ADMIN_REVIEW_L30;
				
				# Chmod the thumbnail
				if (!chmod($mediaObj->ThumbImagePath(), octdec(AUTOGAL_PERMSGALTHUMBS)))
				{
					$text .= "<br />".str_replace('[PATH]', $mediaObj->ThumbImagePath(), AUTOGAL_LANG_ADMIN_REVIEW_L9);
				}
			}
			else
			{
				$text .= "<br />".str_replace('[FROMPATH]', $oldThumbImagePath, str_replace('[TOPATH]', $mediaObj->ThumbImagePath(), AUTOGAL_LANG_ADMIN_REVIEW_L35))."\n";
			}
		}
		
		# Clear the cache
		AutoGal_ClearCacheMenu($galleryObj->Element(), 0);
	}                                                                                                                    
	else
	{
		$text .= "<br />".str_replace('[FROMPATH]', $mediaObj->AbsPath(), str_replace("[TOPATH]", $newPath, AUTOGAL_LANG_ADMIN_REVIEW_L35));
	}
	
	return $text;
}

?>
