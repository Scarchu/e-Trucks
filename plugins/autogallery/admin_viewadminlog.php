<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple image gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        28/11/2005
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");
/* 
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_ADMIN."auth.php");
require_once(e_ADMIN."header.php");
*/
$logFile = file(AUTOGAL_ADMINLOG);
$numEntries = count($logFile);

if ($numEntries <= 0)
{
	$text = AUTOGAL_LANG_ADMIN_ADMINLOG_L1;
}
else
{
	$logFile = array_slice($logFile, -50);
	$logFile = array_reverse($logFile);
	
	$text = "
	<b>".str_replace('[NUMBER]', count($logFile), str_replace('[TOTALNUMBER]', $numEntries, AUTOGAL_LANG_ADMIN_ADMINLOG_L2))."</b><br />
	<br />
	<div style='font-family:courier;white-space:nowrap'>
	<ol>";
	foreach ($logFile as $logEntry)
	{
		$text .= "<li>".htmlspecialchars($logEntry)."</li>";
	}
	
	$text .= "</ol>
	</div>
	<br />
	<a href=\"".AUTOGAL_ADMINLOG."\">".AUTOGAL_LANG_ADMIN_ADMINLOG_L4."</a>\n";
}

$ns -> tablerender(AUTOGAL_LANG_ADMIN_ADMINLOG_L3, $text);
require_once(FOOTERF);
exit;
?>
