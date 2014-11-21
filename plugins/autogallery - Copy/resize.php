<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        05/01/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def_core.php");
require_once(dirname(__FILE__)."/gdim_class.php");

if (!array_key_exists('img', $_GET)) exit;
$ele = stripslashes(rawurldecode($_GET['img']));
$logID = basename($ele);
AutoGal_AddLog($logID, "START");

if (!AUTOGAL_AUTOTHUMB)
{
	AutoGal_AddLog($logID, "Auto thumbnails turned off.");
	AutoGal_DrawImage(AutoGal_GetUnavailThumb($image)); 
	exit;
}

$image = AutoGal_GetAbsGalPath($ele);
@$thumbImage = AutoGal_GetFileThumb($image);

if (strlen($image) <= 0)
{
	AutoGal_AddLog($logID, "ERROR: Image not supplied.", true);	
	AutoGal_DrawImage(AutoGal_GetUnavailThumb($image)); 
	exit;
}
else if (!AutoGal_IsSupportedFormat($image))
{
	AutoGal_AddLog($logID, "ERROR: Image format is not supported.", true);	
	AutoGal_DrawImage(AutoGal_GetUnavailThumb($image)); 
	exit;
}
else if (!AutoGal_IsImage($image))
{
	AutoGal_AddLog($logID, "ERROR: File is not an image.", true);	
	AutoGal_DrawImage(AutoGal_GetUnavailThumb($image)); 
	exit;
}
else if (!AutoGal_IsEleInGallery($image, 1))
{
	AutoGal_AddLog($logID, "ERROR: Image is not in gallery directory.", true);	
	AutoGal_DrawImage(AutoGal_GetUnavailThumb($image)); 
	exit;
}
else if (AutoGal_IsThumb($image))
{
	AutoGal_AddLog($logID, "ERROR: Image is a thumbnail.", true);	
	AutoGal_DrawImage(AutoGal_GetUnavailThumb($image)); 
	exit;
}
else if (!file_exists($image))
{
	AutoGal_AddLog($logID, "ERROR: Image doesn't exist.", true);	
	AutoGal_DrawImage(AutoGal_GetUnavailThumb($image)); 
	exit;
}
elseif ((file_exists($thumbImage))&&(!AUTOGAL_RESIZEDEBUG))
{
	AutoGal_AddLog($logID, "Image thumb already exists ($thumbImage).");	
	AutoGal_DrawImage($thumbImage); 
	exit;
}

# GET THE TARGET SIZE/OUTFILE BASED ON WHAT WHETHER THIS IS A DEFAULT IMAGE OR NOT
if (AutoGal_IsDefaultImage($image))
{
	AutoGal_AddLog($logID, "Image is a default image.");	
	$resizeWidth = AUTOGAL_GALTHUMBWIDTH;
	$resizeHeight = AUTOGAL_GALTHUMBHEIGHT;
	$thumbImage = $image;
}
else
{
	AutoGal_AddLog($logID, "Image is NOT a default image.");	
	$resizeWidth = AUTOGAL_THUMBWIDTH;
	$resizeHeight = AUTOGAL_THUMBHEIGHT;
}

# GET RESIZE OPTIONS FROM e107 SETTINGS
$mode = ($pref['resize_method'] ? $pref['resize_method'] : "gd");
$imQuality = ($pref['im_quality'] ? $pref['im_quality'] : 99);
$imPath = $pref['im_path'];

# INITIALISE GDIM OBJECT
$gdim = new GDIM($mode, $imPath, $imQuality);

$opts['keepaspect'] = AUTOGAL_KEEPASPECT;
$opts['1stframe'] = AUTOGAL_IMKANIGIF1ST;
$opts['iflarger'] = 1;
$opts['perms'] = AUTOGAL_PERMSGALTHUMBS;

# RESIZE THE IMAGE
if ($gdim->resize($image, $thumbImage, $resizeWidth, $resizeHeight, $opts))
{
	$gdim->disp($thumbImage);
}
else
{
	AutoGal_AddLog($logID, "Resize Error: ".$gdim->lastError());
	AutoGal_DrawImage(AutoGal_GetUnavailThumb($image)); 
}

function AutoGal_AddLog($logID, $message, $writeToError=false)
{
	static $logIDNum;
	
	if (AUTOGAL_RESIZEDEBUG)
	{
		print "<font face='courier new'>";
		if ($writeToError)
		{
			print "[<b>$logID></b> $message]<br />\n";
		}
		else
		{
			print "[$logID> $message]<br />\n";
		}
		print "</font>";
		return;
	}
	
	if (AUTOGAL_GENERATEDEBUGLOG)
	{
		$HANDLE = fopen(AUTOGAL_RESIZELOG, 'a+'); 
		flock($HANDLE, LOCK_EX);
		fwrite($HANDLE, "$logID $logIDNum> $message\n");
		flock($HANDLE, LOCK_UN);
		fclose($HANDLE);
	}
	
	if (($writeToError)&&(AUTOGAL_SHOWERRORLOG))
	{
		$time = date('h:i:s');
		$HANDLE = fopen(AUTOGAL_ERRORLOG, 'a+'); 
		flock($HANDLE, LOCK_EX);
		fwrite($HANDLE, "$time ($logID) $message\n");
		flock($HANDLE, LOCK_UN);
		fclose($HANDLE);
	}
	
	$logIDNum ++;
}

function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

?>
