<?php

/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org).
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        02/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
require_once(dirname(__FILE__)."/def_core.php");
require_once(dirname(__FILE__)."/gdim_class.php");

$image = $_SERVER["PATH_TRANSLATED"];

if (!$image) exit;
if (preg_match("/".preg_quote(basename(__FILE__))."$/", $image)) exit;
if (!AutoGal_IsImage($image)) exit;
if (preg_match("/(php|txt|log|xml)$/", $image)) exit; # Should have got caught by line above, but just in case...
if (!AUTOGAL_WMARKAUTO) exit;

$pref = AutoGal_GetPrefs();

$mode = $pref['resize_method'];
$imPath = $pref['im_path'];
$imQuality = ($pref['im_quality'] ? $pref['im_quality'] : 99);
$gdim = new GDIM($mode, $imPath, $imQuality);

# CHECK IF IMAGE IS IN GALLERY
$absPath = str_replace("\\", "/", realpath($image));
$galAbsPath =  str_replace("\\", "/", AutoGal_GetAbsGalPath(''));

if (!AutoGal_IsEleInGallery($absPath, 1))
{
	# NOT IN GALLERY FOLDER
	$gdim->disp($absPath);
}
else
{
	# CHECK IF FILE EXISTS
	if (!file_exists($image)) 
	{
	   header("404 Not Found");
	   echo "File Not Found."; 
	   die();
	}
	
	$basename = basename($absPath);
	
	if ((AutoGal_IsThumb($absPath))||(AutoGal_IsDefaultImage($absPath)))
	{
		# WE DON'T WANT TO WATERMARK THUMBNAILS AND DEFAULT IMAGES
		$gdim->disp($absPath);
	}
	else
	{
		$opts = array
		(
			'intensity' => AUTOGAL_WMARKINTENSITY, 
			'xalign' => AUTOGAL_WMARKXALIGN, 
			'yalign' => AUTOGAL_WMARKYALIGN, 
			'xoffset' => AUTOGAL_WMARKXOFFSET, 
			'yoffset' => AUTOGAL_WMARKYOFFSET,
			'nosmall' => AUTOGAL_WMARKNOSMALL,
		);
			
		$watermark = AUTOGAL_WATERMARKDIRABS."/".AUTOGAL_WMARKIMAGE;
			
		if (!$watermark)
		{
			$gdim->disp($absPath);
			exit;
		}
		
		if (!$gdim->watermark($absPath, '', $watermark, $opts))
		{
			header("HTTP/1.1 500 Internal Server Error");
			print $gdim->lastError();
		}
	}
}

?>