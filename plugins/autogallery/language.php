<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        03/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/../../class.php");

//$langFile = dirname(__FILE__)."/Languages/".e_LANGUAGE.".php";
$langFile = dirname(__FILE__)."/Languages/Bulgarian.php";
$engLangFile = dirname(__FILE__)."/Languages/English.php";

if (file_exists($langFile)) 
{
	require_once($langFile);
}
else
{
	require_once($engLangFile);
}
require_once 'Languages/Bulgarian_Admin.php';

@$siteLang = $pref['sitelanguage'] ? $pref['sitelanguage'] : "English";
@include_once(e_LANGUAGEDIR.$siteLang."/$siteLang.php");

// Charset/encoding
if (!defined("AUTOGAL_CHARSET"))
{
	if (defined("CHARSET"))
	{
		define("AUTOGAL_CHARSET", CHARSET);
	}
	else
	{
		define("AUTOGAL_CHARSET", "utf-8");
	}
}

?>