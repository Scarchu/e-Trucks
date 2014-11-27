<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        27/08/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");
/*
require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");
*/
$type = $_GET['table'];

if (preg_match("/^(cache)$/", $type))
{
	$text = '';
	$text .= htmlspecialchars(AutoGal_DropDBTable($type))."<br />";
	$text .= htmlspecialchars(AutoGal_CreateDBTable($type))."<br />";
	$ns->tablerender(AUTOGAL_LANG_ADMIN_DB_7, $text);
}

if (AutoGal_CheckTableDefs())
{
	$ns->tablerender(AUTOGAL_LANG_ADMIN_DB_7, AUTOGAL_LANG_ADMIN_DB_8);
}

require_once(e_ADMIN."footer.php");
exit;

?>

