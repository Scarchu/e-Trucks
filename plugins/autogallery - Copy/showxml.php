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
 
require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
if (file_exists(AUTOGAL_LANGDIR."/".e_LANGUAGE."_Admin.php")) include_once(AUTOGAL_LANGDIR."/".e_LANGUAGE."_Admin.php");
include_once(AUTOGAL_LANGDIR."/English_Admin.php");
require_once(AUTOGAL_MEDIAOBJCLASS);

$element = $_GET['file'];
$mediaObj = new AutoGal_CMediaObj($element);
if (!$mediaObj->IsValid())
{
	print AUTOGAL_LANG_ADMIN_METADATA_L21.$element;
}
else
{
	if (!$mediaObj->CheckUserPriv('viewxml'))
	{
		print AUTOGAL_LANG_ADMIN_METADATA_L22;
	}
	else
	{
		$xmlFile = $mediaObj->XmlFilePath();
		
		$H_XML = fopen($xmlFile, 'r');
		if (!$H_XML)
		{
			$errorMsg = str_replace('[FILE]', $xmlFile, AUTOGAL_LANG_METACLASS_L3);
			print $errorMsg;
		}
		else
		{
			//header('Content-Type: text/xml');
			$xmlData = fread($H_XML, filesize($xmlFile));
			fclose($H_XML);
			
			$xmlData = htmlspecialchars($xmlData);
			print "<pre>$xmlData</pre>";
		}
	}
}
?>