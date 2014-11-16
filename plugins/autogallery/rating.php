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

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");
require_once(AUTOGAL_RENDERMETA);

$g_element = stripslashes(rawurldecode($_POST['show'] ? $_POST['show'] : $_GET['show']));
$g_mediaObj = new AutoGal_CMediaObj($g_element);

if (!$g_mediaObj->IsValid())
{
	print "Invalid target (".htmlspecialchars($g_element)."): ".$g_mediaObj->LastError();
}
else
{
	print AutoGal_GetNewWindowHeader('');
	
	$text = AutoGal_RatingHTML($g_mediaObj);
	$title = str_replace('[TYPE]', $g_mediaObj->FileTypeTitle(), AUTOGAL_LANG_RATING_L1);
	
	$ns->tablerender($title, $text);
	
	$g_mediaObj->SaveMeta();
	
	print AutoGal_GetNewWindowFooter();
}

?>