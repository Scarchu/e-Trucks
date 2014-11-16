<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        11/08/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");
 
//require_once(e_ADMIN."auth.php");

$buttons = array();
$buttons['main']['link'] = AUTOGAL_CONFIG;              $buttons['main']['text'] = AUTOGAL_LANG_ADMIN_MENU_L1;
$buttons['appr']['link'] = AUTOGAL_APPEARANCESETTINGS;  $buttons['appr']['text'] = AUTOGAL_LANG_ADMIN_MENU_L3;
$buttons['thmb']['link'] = AUTOGAL_THUMBNAILSETTINGS;   $buttons['thmb']['text'] = AUTOGAL_LANG_ADMIN_MENU_L2;
$buttons['revu']['link'] = AUTOGAL_REVIEWUPLOADS;       $buttons['revu']['text'] = AUTOGAL_LANG_ADMIN_MENU_L6.(AUTOGAL_SHOWREVIEWCOUNT ? " (".AutoGal_NumUploads().")" : '');
$buttons['usra']['link'] = AUTOGAL_USERACCESS;          $buttons['usra']['text'] = AUTOGAL_LANG_ADMIN_MENU_L17;
$buttons['usrg']['link'] = AUTOGAL_USERGALLERYADMIN;    $buttons['usrg']['text'] = AUTOGAL_LANG_ADMIN_MENU_L18;
$buttons['xmlm']['link'] = AUTOGAL_XMLMETASETTINGS;     $buttons['xmlm']['text'] = AUTOGAL_LANG_ADMIN_MENU_L5;
$buttons['secs']['link'] = AUTOGAL_SECURITYSETTINGS;    $buttons['secs']['text'] = AUTOGAL_LANG_ADMIN_MENU_L11;
$buttons['wmrk']['link'] = AUTOGAL_WATERMARKADMIN;      $buttons['wmrk']['text'] = AUTOGAL_LANG_ADMIN_MENU_L12;
$buttons['chmd']['link'] = AUTOGAL_DOCHMOD;             $buttons['chmd']['text'] = AUTOGAL_LANG_ADMIN_MENU_L9;
$buttons['lang']['link'] = AUTOGAL_LANGADMIN;           $buttons['lang']['text'] = AUTOGAL_LANG_ADMIN_MENU_L13;
$buttons['slde']['link'] = AUTOGAL_SLIDESHOWADMIN;      $buttons['slde']['text'] = AUTOGAL_LANG_ADMIN_MENU_L14;
$buttons['cach']['link'] = AUTOGAL_CACHEADMIN;          $buttons['cach']['text'] = AUTOGAL_LANG_ADMIN_MENU_L16;
$buttons['alog']['link'] = AUTOGAL_VIEWADMINLOG;        $buttons['alog']['text'] = AUTOGAL_LANG_ADMIN_MENU_L7;
$buttons['dlog']['link'] = AUTOGAL_VIEWDEBUGLOG;        $buttons['dlog']['text'] = AUTOGAL_LANG_ADMIN_MENU_L8;
$buttons['bugr']['link'] = AUTOGAL_BUGREPORT;           $buttons['bugr']['text'] = AUTOGAL_LANG_ADMIN_MENU_L15;

$currFile = basename($_SERVER['REQUEST_URI']);
foreach ($buttons as $pageID => $pageInfo)
{
	if (basename($pageInfo['link']) == $currFile)
	{
		$currPageID = $pageID;
		break;
	}
}

show_admin_menu(AUTOGAL_LANG_ADMIN_MENU_L10, $currPageID, $buttons);

?>
