<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple image gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        18/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }

if ($_POST['ag_downloaddump'])
{
	$incGalFiles = $_POST['ag_incgalfiles'];
	
	$data = AutoGal_SystemDump("\r\n", $incGalFiles); # \r\n? Because I use windows at home
	
	#header('HTTP/1.1 200 OK');
	header('Date: '.date("D M j G:i:s T Y"));
	header('Last-Modified: '.date("D M j G:i:s T Y"));
	header("Content-Type: application/force-download");
	header("Content-Length: ".strlen($data));
	#header("Content-Transfer-Encoding: Binary");
	header("Content-Disposition: attachment; filename=systemdump.txt");
	
	print $data;
	exit;
}

require_once(e_ADMIN."auth.php");
require_once(e_ADMIN."header.php");
 
$bugURL = "<a href=\"".AUTOGAL_REPORTBUGLINK."\" target=\"_blank\">".AUTOGAL_REPORTBUGLINK."</a>";

$text = "
<form method='post'>
<div style='text-align:center'><b>".AUTOGAL_LANG_ADMIN_BUGREPORT_2."</b></div>
<div style='text-align:left'>
<ol>
<li>".str_replace("[URL]" , $bugURL, AUTOGAL_LANG_ADMIN_BUGREPORT_4)."</li>
<li>".AUTOGAL_LANG_ADMIN_BUGREPORT_5."</li>
<li>".AUTOGAL_LANG_ADMIN_BUGREPORT_6."</li>
<li>".AUTOGAL_LANG_ADMIN_BUGREPORT_7."</li>
<li>".AUTOGAL_LANG_ADMIN_BUGREPORT_8."</li>
<li>".AUTOGAL_LANG_ADMIN_BUGREPORT_9."</li>
</ol>
</div>
<div style='text-align:center'>
<input type='checkbox' name='ag_incgalfiles'> ".AUTOGAL_LANG_ADMIN_BUGREPORT_10."<br />
<br />
<input type='submit' class='button' name='ag_downloaddump' value='".AUTOGAL_LANG_ADMIN_BUGREPORT_3."'>
</div>
</form>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_BUGREPORT_1, $text);
require_once(e_ADMIN."footer.php");

function AutoGal_SystemDump($nl="\n", $incGalFiles=false)
{
	global $pref;
	
	$defs = get_defined_constants();
	ksort($defs);
	$extraE107Defs = array('SITEURL', 'ADMINDIR', 'SITEURLBASE', 'USERLAN', 'THEME', 'THEME_ABS');

	$text .= "---- SERVER INFO ----$nl";
	ksort($_SERVER);
	foreach ($_SERVER as $var => $val)
	{
		if ($var == 'PATH') continue;
		if ($var == 'HTTP_COOKIE') continue;
		if ($var == 'SERVER_SIGNATURE') continue;
		if (preg_match('/^REMOTE/', $var)) continue;
		if (preg_match('/^HTTP\_ACCEPT/', $var)) continue;
		$text .= "$var = [$val]$nl";
	}

	$text .= "$nl---- E107 INFO ----$nl";
	foreach ($defs as $var => $val)
	{
		if ((preg_match("/^(E|e107)\_/i", $var))&&(!preg_match("/^(E\_\d+\_|E_NL)/", $var)))
		{
			$e107Defs[$var] = $val;
		}
	}
	
	foreach ($extraE107Defs as $def)
	{
		$e107Defs[$def] = $defs[$def];
	}
	
	ksort($e107Defs);
	foreach ($e107Defs as $var => $val)
	{
		$text .= "$var = [$val]$nl";
	}
	
	$text .= "$nl---- RESIZING INFO ----$nl";
		
	$mode = ($pref['resize_method'] ? $pref['resize_method'] : "gd2");
	
	$text .= "MODE = [$mode]$nl";
	
	$renderTop = 0;
	if (preg_match("/^gd\d/", $mode))
	{
		$text .= "MODE_VALID = [TRUE]$nl"; 
		
		if (!extension_loaded('gd')) 
		{
			$text .= "EXT_LOADED = [FALSE]$nl";
		}
		else
		{
			$text .= "EXT_LOADED = [TRUE]$nl";
			
			if (!function_exists('gd_info')) 
			{
				$text .= "GD_INFO_EXISTS = [FALSE]$nl";
			}
			
			$text .= "GD_INFO_EXISTS = [TRUE]$nl";
			$gdInfo = gd_info();
			
			foreach ($gdInfo as $gdField => $gdValue)
			{
				if (preg_match("/Support$/", $gdField))
				{
					if ($gdValue == 1) 
						$gdValue = 'Yes'; 
					elseif ($gdValue == '') 
						$gdValue = 'No';
				}
				$text .= "$gdField = [$gdValue]$nl";
			}
		}
	}
	elseif ($mode == 'ImageMagick')
	{
		$text .= "MODE_VALID = [TRUE]$nl"; 
		
		$imPath = $pref['im_path'];
		$text .= "IM_PATH = [$imPath]$nl";
		
		if ($imPath)
		{
			$currDir = getcwd();
			chdir($imPath);
		}
	
		$cmd = "convert -version";
		$IMVersion = shell_exec($cmd);
		
		if ($imPath) chdir($currDir);    
		
		if (!preg_match("/ImageMagick/", $IMVersion))
		{
			$text .= "IM_VERSION_CHECK_OK = [FALSE]$nl";
		}
		else
		{
			$IMVersion = preg_replace("/\n*Usage\:.+$/mi", "", $IMVersion);
			$IMVersion = preg_replace("/\n+$/mi", "", $IMVersion);
			$text .= "IM_VERSION_INFO = [".$IMVersion."]$nl";
		}
	}
	else
	{
		$text .= "MODE_VALID = [FALSE]$nl"; 
	}
		
	$text .= "$nl---- AUTOGALLERY INFO ----$nl";
	$text .= "VERSION = [".AutoGal_GetVersion()."]$nl";
	foreach ($defs as $var => $val)
	{
		if ((preg_match("/^AUTOGAL\_/", $var))&&(!preg_match("/^AUTOGAL\_LANG/", $var)))
		{
			$text .= "$var = [$val]$nl";
		}
	}
	
	$text .= "$nl---- MAIN FILE INFO ----$nl";
	$text .= "PATH = [".AUTOGAL_BASEABS."]$nl";
	$text .= AutoGal_DumpFiles(AUTOGAL_BASEABS, false, $nl);
	
	if ($incGalFiles)
	{
		 $dir = AutoGal_GetAbsGalPath('');
		 $text .= "$nl---- GALLERY FILE INFO ----$nl";
		 $text .= "PATH = [ $dir]$nl";
		 $text .= AutoGal_DumpFiles($dir, true, $nl);
	}
	
	$text .= "---- END ----$nl";
	return $text;
}

function AutoGal_DumpFiles($startDir, $incGalFiles=false, $nl="\n")
{
	$startDir = AutoGal_FixWinAbsPath($startDir);
	$galPath = AutoGal_FixWinAbsPath(AutoGal_GetAbsGalPath(''));
	$defGalPath = AutoGal_FixWinAbsPath(AUTOGAL_BASEABS.'/'.AUTOGAL_DEFGALLERYDIR);
	
	$dirStack[] = $startDir;
	while ($dirStack)
    {
        $dir = AutoGal_FixWinAbsPath(array_pop($dirStack));
		
		if ((!$incGalFiles)&&(($dir == $galPath)||($dir == $defGalPath))) continue;
		
        if ($dh = opendir($dir))
        {
            while ($file = readdir($dh))
            {
                if (($file == '.')||($file == '..')) continue;
				
				$filePath = "$dir/$file";
				
				if (is_dir($filePath))
				{
					$dirStack[] = $filePath;
					$dirs[] = $filePath; 
				}
				else
				{
					$size = filesize($filePath);
					$files[] = $filePath;
				}
			}
        }
		else
		{
			$filePerms = fileperms(AUTOGAL_BASEABS);
			$filePerms = substr(sprintf('%o', $filePerms), -3);
			$dirs[] = "[ERROR] [$filePerms] [$dir]$nl";
		}
    }
	
	sort($dirs);
	foreach ($dirs as $filePath)
	{
		$filePerms = fileperms($filePath);
		$filePerms = substr(sprintf('%o', $filePerms), -3);
		
		$fileRel = $filePath;
		$fileRel = preg_replace("/^".preg_quote($startDir, "/")."\//", '', $fileRel);
		
		$text .= "DIR  = [$filePerms] [$fileRel]$nl";
	}
	
	sort($files);
	foreach ($files as $filePath)
	{
		$filePerms = fileperms($filePath);
		$filePerms = substr(sprintf('%o', $filePerms), -3);
		
		$fileRel = $filePath;
		$fileRel = preg_replace("/^".preg_quote($startDir, "/")."\//", '', $fileRel);
		
		$size = filesize($filePath);
		
		$text .= "FILE = [$filePerms] [{$size}b]".str_repeat(' ', 10 - strlen($size))." [$fileRel]$nl";
	}
	
	return $text;
}

?>
