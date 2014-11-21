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
$image = stripslashes(rawurldecode($_GET['img']));

if (strlen($image) <= 0)
{
	header("Location: Images/thumbunavailable.png");
	exit;
}

if (!AutoGal_IsEleInGallery($image))
{
	header("Location: Images/thumbunavailable.png");
	exit;
}

if (!AutoGal_IsSupportedFormat($image))
{
	header("Location: Images/thumbunavailable.png");
	exit;
}

$absPath = AutoGal_GetAbsGalPath($image);

if (!$absPath)
{
	header("Location: Images/thumbunavailable.png");
	exit;
}

AutoGal_DrawImage($absPath);
?>
