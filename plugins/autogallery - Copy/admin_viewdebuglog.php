<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple image gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        30/03/2005
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");
 
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_ADMIN."auth.php");
require_once(e_ADMIN."header.php");

if (!AUTOGAL_GENERATEDEBUGLOG)
{
	$text = AUTOGAL_LANG_ADMIN_DEBUGLOG_L1." (<a href=\"".AUTOGAL_THUMBNAILSETTINGS."\">".AUTOGAL_LANG_ADMIN_DEBUGLOG_L2."</a>)";
}
else
{
	$logFile = file(AUTOGAL_RESIZELOG);
	$numEntries = count($logFile);
	
	if ($numEntries <= 0)
	{
		$text = AUTOGAL_LANG_ADMIN_DEBUGLOG_L3;
	}
	else
	{
		if ($_GET['clear'] == 1)
		{
			$LOGH = fopen(AUTOGAL_RESIZELOG, 'w');
			
			if ($LOGH)
			{
				fclose($LOGH);
				$text = AUTOGAL_LANG_ADMIN_DEBUGLOG_L4;
			}
		}
		else
		{
			$logFile = array_slice($logFile, -50);
			$logFile = array_reverse($logFile);
			
			$text = "
			<b>".str_replace('[NUMBER]', count($logFile), str_replace('[TOTALNUMBER]', $numEntries, AUTOGAL_LANG_ADMIN_DEBUGLOG_L5))."</b><br />
			<br />
			<div style='font-family:courier;white-space:nowrap'>
			<ol>";
			foreach ($logFile as $logEntry)
			{
				$text .= "<li>".htmlspecialchars($logEntry)."</li>";
			}
			
			$text .= "
			</div>
			</ol>";
		}
		
		$text .= "
		<br />
		[<a href=\"".AUTOGAL_RESIZELOG."\">".AUTOGAL_LANG_ADMIN_DEBUGLOG_L6."</a>]<br />
		[<a href=\"".AUTOGAL_VIEWDEBUGLOG."?clear=1\">".AUTOGAL_LANG_ADMIN_DEBUGLOG_L7."</a>]\n";
	}
}

$ns -> tablerender(AUTOGAL_LANG_ADMIN_DEBUGLOG_L8, $text);
require_once(e_ADMIN."footer.php");

?>
