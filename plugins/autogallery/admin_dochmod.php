<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A very simple image gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        18/08/2007
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
$text = "";

if ($_POST['dochmod'])
{
	// BASE DIRECTORY
	$text .= "<b>".AUTOGAL_LANG_ADMIN_DOCHMOD_L1."</b><br />";
	$absPath = AUTOGAL_BASEABS;
	
	$chmodVal = AUTOGAL_PERMSBSEDIR;
	if (chmod($absPath, octdec($chmodVal)))
	{
		$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $absPath<br />";
	}
	else
	{
		$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $absPath</font><br />";
	}
	
	// .HTACCESS FILE
	$file = realpath(AUTOGAL_BASE.'/.htaccess');
	$file = str_replace("\\", "/", $file);
	if (file_exists($file))
	{
		$text .= "<b>".AUTOGAL_LANG_ADMIN_DOCHMOD_L27."</b><br />";
		$chmodVal = AUTOGAL_PERMSHTACCESS;
		if (chmod($file, octdec($chmodVal)))
		{
			$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $file<br />";
		}
		else
		{
			$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $file</font><br />";
		}
	}
	
	// DO GALLERY FILES
	$text .= "<br /><b>".AUTOGAL_LANG_ADMIN_DOCHMOD_L4."</b><br />";
	$absPath = AutoGal_GetAbsGalPath('');
	$files = AutoGal_ListDirectory($absPath, ".*", true, true, false, true);

	// ROOT GALLERY
	$chmodVal = AUTOGAL_PERMSGALDIR;
	if (chmod($absPath, octdec($chmodVal)))
	{
		$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $absPath<br />";
	}
	else
	{
		$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $absPath</font><br />";
	}
	
	// SUB GALLERIES/MEDIA FILES
	foreach ($files as $file)
	{
		if (is_dir($file))
		{
			$chmodVal = AUTOGAL_PERMSGALDIR;
		}
		elseif (preg_match("/\.xml$/i", $file))
		{
			$chmodVal = AUTOGAL_PERMSGALXML;
		}
		elseif (preg_match("/^".preg_quote(AUTOGAL_THUMBPREFIX)."/i", $file))
		{
			$chmodVal = AUTOGAL_PERMSGALTHUMBS;
		}
		elseif ($file == '.htaccess')
		{
			$chmodVal = AUTOGAL_PERMSHTACCESS;
		}
		else
		{
			$chmodVal = AUTOGAL_PERMSGALMEDIA;
		}
		
		if (chmod($file, octdec($chmodVal)))
		{
			$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $file<br />";
		}
		else
		{
			$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $file</font><br />";
		}
	}
	
	// CONFIGURATION DIRECTORY
	$text .= "<br /><b>".AUTOGAL_LANG_ADMIN_DOCHMOD_L23."</b><br />";
	$absPath = str_replace("\\", "/", realpath(AUTOGAL_CONFIGDIR));
	$files = AutoGal_ListDirectory($absPath, ".*", true, true, false, true);
	
	$chmodVal = AUTOGAL_PERMSLOGDIR;
	if (chmod($absPath, octdec($chmodVal)))
	{
		$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $absPath<br />";
	}
	else
	{
		$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $absPath</font><br />";
	}
	
	foreach ($files as $file)
	{
		if (is_dir($file))
		{
			$chmodVal = AUTOGAL_PERMSCFGDIR;
		}
		elseif (preg_match("/\.xml$/i", $file))
		{
			$chmodVal = AUTOGAL_PERMSCFGXML;
		}
				
		if (chmod($file, octdec($chmodVal)))
		{
			$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $file<br />";
		}
		else
		{
			$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $file</font><br />";
		}
	}
	
	// UPLOAD DIRECTORY
	$text .= "<br /><b>".AUTOGAL_LANG_ADMIN_DOCHMOD_L5."</b><br />";
	$absPath = str_replace("\\", "/", AUTOGAL_UPLOADDIRABS);
	$files = AutoGal_ListDirectory($absPath, ".*", true, true, false, true);
	
	$chmodVal = AUTOGAL_PERMSUPLDIR;
	if (chmod($absPath, octdec($chmodVal)))
	{
		$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $absPath<br />";
	}
	else
	{
		$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $absPath</font><br />";
	}
	
	foreach ($files as $file)
	{
		if (is_dir($file))
		{
			$chmodVal = AUTOGAL_PERMSUPLDIR;
		}
		elseif (preg_match("/\.xml$/i", $file))
		{
			$chmodVal = AUTOGAL_PERMSUPLXML;
		}
		else
		{
			$chmodVal = AUTOGAL_PERMSUPLMEDIA;
		}
		
		if (chmod($file, octdec($chmodVal)))
		{
			$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $file<br />";
		}
		else
		{
			$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $file</font><br />";
		}
	}

	// LOG DIRECTORY
	$text .= "<br /><b>".AUTOGAL_LANG_ADMIN_DOCHMOD_L6."</b><br />";
	$absPath = str_replace("\\", "/", realpath(AUTOGAL_LOGDIR));
	$files = AutoGal_ListDirectory($absPath, ".*", true, false, true, true);
	
	$chmodVal = AUTOGAL_PERMSLOGDIR;
	if (chmod($absPath, octdec($chmodVal)))
	{
		$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $absPath<br />";
	}
	else
	{
		$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $absPath</font><br />";
	}
	
	foreach ($files as $file)
	{
		if (is_dir($file))
		{
			$chmodVal = AUTOGAL_PERMSLOGDIR;
		}
		else
		{
			$chmodVal = AUTOGAL_PERMSLOGFILES;
		}
		
		if (chmod($file, octdec($chmodVal)))
		{
			$text .= "Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L2." $file<br />";
		}
		else
		{
			$text .= "<font color='red'>Chmod $chmodVal ".AUTOGAL_LANG_ADMIN_DOCHMOD_L3." $file</font><br />";
		}
	}
		
	$ns -> tablerender(AUTOGAL_LANG_ADMIN_DOCHMOD_L7, $text);
}

$text = "
<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
".AUTOGAL_LANG_ADMIN_DOCHMOD_L8."<br />
<br />
<table cellpadding='3'>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L28."</td><td>".AUTOGAL_PERMSBSEDIR."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L26."</td><td>".AUTOGAL_PERMSHTACCESS."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L9."</td><td>".AUTOGAL_PERMSGALDIR."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L10."</td><td>".AUTOGAL_PERMSGALMEDIA."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L11."</td><td>".AUTOGAL_PERMSGALTHUMBS."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L12."</td><td>".AUTOGAL_PERMSGALXML."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L13."</td><td>".AUTOGAL_PERMSLOGDIR."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L14."</td><td>".AUTOGAL_PERMSLOGFILES."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L15."</td><td>".AUTOGAL_PERMSUPLDIR."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L16."</td><td>".AUTOGAL_PERMSUPLMEDIA."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L17."</td><td>".AUTOGAL_PERMSUPLXML."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L24."</td><td>".AUTOGAL_PERMSCFGDIR."</td></tr>
<tr><td>".AUTOGAL_LANG_ADMIN_DOCHMOD_L25."</td><td>".AUTOGAL_PERMSCFGXML."</td></tr>
</table>
<br />
".AUTOGAL_LANG_ADMIN_DOCHMOD_L18." <a href=\"http://en.wikipedia.org/wiki/Chmod\"><b>".AUTOGAL_LANG_ADMIN_DOCHMOD_L19."</b></a>.<br />
<br />
".str_replace('[FILE]', 'def_core.php', str_replace('[LINENUMBER]', '98', AUTOGAL_LANG_ADMIN_DOCHMOD_L20))."<br />
<br />
<input type='submit' name='dochmod' value='".AUTOGAL_LANG_ADMIN_DOCHMOD_L21."' class='button'>
</div>
</form>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_DOCHMOD_L22, $text);
require_once(e_ADMIN."footer.php");
exit;

?>
