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

header("Content-Type: text/plain");
header("Content-Disposition: inline;filename=ieistehsuck.txt"); # For IE's fucking bullshit

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_EDITFUNCTIONS);
require_once(AUTOGAL_ADMINFUNCTIONS);

$operation = $_GET['op'];
$elements = explode('|', AutoGal_GetHtmlVar('ele'));

if ($elements)
{
	$g_element = $elements[0];
	$g_mediaObj = new AutoGal_CMediaObj($g_element);
	AutoGal_LoadGlobals(true);
}
else
{	
	AutoGal_LoadGlobals(true);
}

if ($operation == 'regenlatestcomms')
{
	if ((AutoGal_IsEditAllowed())||(AutoGal_UserGallery())||())
	{
		AutoGal_RegenLatestComments($error, 1);
	}
	else
	{
		print AUTOGAL_LANG_ADMIN_EDIT_114."\n";
	}
}
else
{
	if (!$elements)
	{
		print "*** ".AUTOGAL_LANG_ADMIN_EDIT_111;
	}
	else if ($operation == 'clearcache')
	{
		AutoGal_ClearCache($elements, 0, 1);
	}
	else if ($operation == 'clearcacher')
	{
		AutoGal_ClearCache($elements, 1, 1);
	}
	else
	{
		if ($g_mediaObj->IsFile())
		{
			$g_mediaObj = $g_mediaObj->GalleryMediaObj();
			$g_element = $g_mediaObj->Element();
		}
		
		if ($operation == 'regencache')
		{
			AutoGal_ClearCache($g_mediaObj->Element(), 0, 1);
			AutoGal_GenerateCache($g_mediaObj->Element(), 0, 1);
		}
		else if ($operation == 'regencacher')
		{
			AutoGal_ClearCache($g_mediaObj->Element(), 1, 1);
			AutoGal_GenerateCache($g_mediaObj, 1, 1);
		}
		else
		{
			print "*** ".str_replace("[OPERATION]", htmlspecialchars($operation), AUTOGAL_LANG_ADMIN_EDIT_113);
		}
	}
}

exit;

?>