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

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_MEDIAOBJCLASS);

/*require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");
*/
###################
# SET PREFERENCES #
###################
$text = '';
if (IsSet($_POST['doupdateops']))
{
	$ops = AutoGal_GetFileUpdateOps();
	
	$allOk = 1;
	if (!$ops)
	{
		$text = "
		<div style='text-align:center'>
		<b>".AUTOGAL_LANG_ADMIN_FILEUPDATE_10."</b><br />
		<br />
		[<a href=\"".AUTOGAL_FILEUPDATE."\">".AUTOGAL_LANG_ADMIN_FILEUPDATE_25."</a>]
		</div>";
	}
	else
	{
		$id = 0;
		foreach ($ops as $op)
		{
			if (!$_POST["approve_$id"]) continue;
			$isOk = AutoGal_DoFileUpdateOp($op, $msg);
			$text .= "<ol>\n";
			
			if (!$isOk)
			{
				$allOk = 0;
				$msg = "<font color='red'>*** ".htmlspecialchars($msg)."</font>";
			}
			else
			{
				$msg = htmlspecialchars($msg);
			}
			
			$msg = "<li>$msg</li>\n";
			$text .= $msg;
			
			$id ++;
		}
		
		$text .= "
		</ol>
		<div style='text-align:center'>
		<br />
		[<a href=\"".AUTOGAL_FILEUPDATE."\">".AUTOGAL_LANG_ADMIN_FILEUPDATE_25."</a>]
		</div>";
	}
	
	if ($allOk)
	{
		unset($pref['autogal_needfileupdate']);
		save_prefs();
	}
}
else if (IsSet($_POST['getupdateops']))
{
	$ops = AutoGal_GetFileUpdateOps();
	
	if (!$ops)
	{
		$text = "
		<div style='text-align:center'>
		<b>".AUTOGAL_LANG_ADMIN_FILEUPDATE_10."</b><br />
		<br />
		[<a href=\"".AUTOGAL_FILEUPDATE."\">".AUTOGAL_LANG_ADMIN_FILEUPDATE_25."</a>]
		</div>";
		
		unset($pref['autogal_needfileupdate']);
		save_prefs();
	}
	else
	{
		$text .= "
		<form method='POST'>
		<div style='text-align:center'>
		<table class='border' width='97%' align='center'>
		<tr>
		<td class='forumheader2' style='text-align:center'>".AUTOGAL_LANG_ADMIN_FILEUPDATE_11."</td>
		<td class='forumheader2' style='text-align:center'>".AUTOGAL_LANG_ADMIN_FILEUPDATE_12."</td>
		<td class='forumheader2' style='text-align:center'>".AUTOGAL_LANG_ADMIN_FILEUPDATE_13."</td>
		<td class='forumheader2' style='text-align:center'>".AUTOGAL_LANG_ADMIN_FILEUPDATE_14."</td>
		</tr>";
		
		$id = 0;
		foreach ($ops as $op)
		{	
			if ($op['type'] == 'delete')
			{
				$opTitle = AUTOGAL_LANG_ADMIN_FILEUPDATE_16;
				$opInfo = $op['file'];
			}
			elseif ($op['type'] == 'move')
			{
				$opTitle = AUTOGAL_LANG_ADMIN_FILEUPDATE_15;
				$opInfo = AUTOGAL_LANG_ADMIN_FILEUPDATE_17;
				$opInfo = str_replace('[FROMFILE]', $op['file'], $opInfo);
				$opInfo = str_replace('[TOFILE]', $op['to'], $opInfo);
			}
			else
			{
				continue;
			}
			
			$text .= "
			<tr>
			<td class='forumheader3' style='text-align:center'><input type='checkbox' name='approve_$id' checked='checked'></td>
			<td class='forumheader3' style='text-align:center'>$opTitle</td>
			<td class='forumheader3' style='text-align:center;white-space:nowrap'>".$op['note']."</td>
			<td class='forumheader3' style='text-align:center'><span class='smalltext'>$opInfo</span></td>
			</tr>";
			
			$id ++;
		}
		
		$text .= "
		</table>
		<br />
		<input type='submit' class='button' name='doupdateops' value='".AUTOGAL_LANG_ADMIN_FILEUPDATE_18."'><br />
		<br />
		[<a href=\"".AUTOGAL_FILEUPDATE."\">".AUTOGAL_LANG_ADMIN_FILEUPDATE_25."</a>]
		</div>
		</form>";
	}
}
else
{
	$text = "
	<form method='POST'>
	<div style='text-align:center'>
	".AUTOGAL_LANG_ADMIN_FILEUPDATE_2."<br />
	<br />
	<input type='submit' class='button' name='getupdateops' value='".AUTOGAL_LANG_ADMIN_FILEUPDATE_3."'>
	</div>
	</form>";
}

$ns -> tablerender(AUTOGAL_LANG_ADMIN_FILEUPDATE_1, $text);

require_once(e_ADMIN."footer.php");
exit;

function AutoGal_GetFileUpdateOps()
{
	# Move old root gallery xml file
	if (file_exists(AUTOGAL_CONFIGDIR."/Gallery.xml"))
	{
		$mediaObj = new AutoGal_CMediaObj('');
		$ops[] = array('type' => 'move', 'file' => AUTOGAL_CONFIGDIR."/Gallery.xml", 'to' => $mediaObj->XmlFilePath(), 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_4);
	}
	
	$dirStack[] = AutoGal_GetAbsGalPath('');
	while ($dirStack)
    {
        $dir = array_pop($dirStack);
        $dh = opendir($dir);
		if (!$dh) continue;
		
		if (AutoGal_IsMediaDir($dir))
		{
			$element = AutoGal_GetElement($dir);
			$mediaObj = new AutoGal_CMediaObj($element);
			
			# Move old gallery xml file
			$oldXmlPath = dirname($dir).'/'.$file.".xml";
			if (file_exists($oldXmlPath))
			{
				$ops[] = array('type' => 'move', 'file' => $oldXmlPath, 'to' => $mediaObj->XmlFilePath(), 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_4);
			}
			
			# Rename old gallery thumbnail image
			$oldThumbStart = $dir."/__default.";
			$thExts = explode("|", AUTOGAL_IMAGEEXTS);
			$found = 0;
			foreach ($thExts as $ext)
			{
				$oldThumbPath = $oldThumbStart.$ext;
				if (file_exists($oldThumbPath))
				{
					if ($mediaObj->IsRoot())
					{
						$ops[] = array('type' => 'delete', 'file' => $oldThumbPath, 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_6);
					}
					else
					{
						if (!$found)
						{
							$ops[] = array('type' => 'move', 'file' => $oldThumbPath, 'to' => AutoGal_GetFileThumb($dir, $ext), 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_5);
							$found = 1;
						}
						else
						{
							$ops[] = array('type' => 'delete', 'file' => $oldThumbPath, 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_8);
						}
					}
				}
				
				if ($mediaObj->IsRoot())
				{
					$oldThumbPath = $dir.'/'.AUTOGAL_GALLERYTHUMBFILENAME.'.'.$ext;
					if (file_exists($oldThumbPath))
					{
						$ops[] = array('type' => 'delete', 'file' => $oldThumbPath, 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_8);
					}
				}
			}
			
			# Delete old cache file (was in a BETA version, noone should really have this on)
			$oldCacheFile = $dir."/__cache.xml";
			if (file_exists($oldCacheFile))
			{
				$ops[] = array('type' => 'delete', 'file' => $oldCacheFile, 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_9);
			}
		}
				
		while ($file = readdir($dh))
        {
			if (($file == '.')||($file == '..')) continue;
			$filePath = "$dir/$file";
		
			if (is_dir($filePath))
			{
				$dirStack[] = $filePath;
			}
			else
			{
				if (AutoGal_IsThumb($filePath))
				{
					$basename = basename($filePath);
					$basename = preg_replace("/^".preg_quote(AUTOGAL_THUMBPREFIX)."/i", "", $basename);
					$parentFile = dirname($filePath)."/".$basename;
				
					if (!file_exists($parentFile))
					{
						$ops[] = array('type' => 'delete', 'file' => $filePath, 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_6);
					}
				}
				else if (AutoGal_IsPreviewImage($filePath))
				{
					$basename = basename($filePath);
					$basename = preg_replace("/^".preg_quote(AUTOGAL_PREVIEWIMGPREFIX)."/i", "", $basename);
					$parentFile = dirname($filePath)."/".$basename;
				
					if (!file_exists($parentFile))
					{
						$ops[] = array('type' => 'delete', 'file' => $filePath, 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_7);
					}
				}
				else if ((AutoGal_IsXmlFile($filePath))&&(!AutoGal_IsGalleryXmlFile($filePath)))
				{
					$basename = basename($filePath);
					$basename = preg_replace("/\.xml$/i", "", $basename);
					$parentFile = dirname($filePath)."/".$basename;
				
					if (!file_exists($parentFile))
					{
						$ops[] = array('type' => 'delete', 'file' => $filePath, 'note' => AUTOGAL_LANG_ADMIN_FILEUPDATE_24);
					}
				}
			}
        }
    }
	
	usort($ops, "AutoGal_CmpFileUpdateOps");
	
	return $ops;
}

function AutoGal_DoFileUpdateOp($op, &$msg)
{
	if ($op['type'] == 'delete')
	{
		if (unlink($op['file']))
		{
			$msg = AUTOGAL_LANG_ADMIN_FILEUPDATE_19;
			$msg = str_replace("[FILE]", $op['file'], $msg);
			return 1;
		}
		else
		{
			$msg = AUTOGAL_LANG_ADMIN_FILEUPDATE_21;
			$msg = str_replace("[FILE]", $op['file'], $msg);
			return 0;
		}
	}
	else if ($op['type'] == 'move')
	{
		if (rename($op['file'], $op['to']))
		{
			$msg = AUTOGAL_LANG_ADMIN_FILEUPDATE_20;
			$msg = str_replace("[FROMFILE]", $op['file'], $msg);
			$msg = str_replace("[TOFILE]", $op['to'], $msg);
			return 1;
		}
		else
		{
			$msg = AUTOGAL_LANG_ADMIN_FILEUPDATE_22;
			$msg = str_replace("[FROMFILE]", $op['file'], $msg);
			$msg = str_replace("[TOFILE]", $op['to'], $msg);
			return 0;
		}
	}
	else
	{
		$msg = str_replace("[TYPE]", $op['type'], AUTOGAL_LANG_ADMIN_FILEUPDATE_23);
		return 0;
	}
}

function AutoGal_CmpFileUpdateOps($a, $b)
{
	if ($a['type'] < $b['type']) return -1;
	if ($a['type'] > $b['type']) return 1;
	
	if ($a['note'] < $b['note']) return -1;
	if ($a['note'] > $b['note']) return 1;
	
	if ($a['file'] < $b['file']) return -1;
	if ($a['file'] > $b['file']) return 1;
	
	return 0;
}
?>

