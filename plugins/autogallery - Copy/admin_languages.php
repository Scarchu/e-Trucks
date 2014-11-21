<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        22/08/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
require_once(dirname(__FILE__)."/language.php");
require_once(dirname(__FILE__)."/def.php");

require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");

define(AUTOGAL_BASELANGUAGE, 'English');

if ($_GET['lang'])
{
	$text = AutoGal_ShowMissingLangDefs($_GET['lang'], $_GET['type']);
}
else
{
	$text = AutoGal_LangAdmin();
}
	
$ns -> tablerender(AUTOGAL_LANG_ADMIN_LANGUAGES_1, $text);
require_once(e_ADMIN."footer.php");
exit;

function AutoGal_GetLangDefs($file)
{
	$file = realpath($file);
	if (!$file) return;
	if (!file_exists($file)) return;
	
	$fileLines = file($file);
	
	foreach ($fileLines as $line)
	{
		if (preg_match("/^\s*\#/", $line)) continue;
		if (preg_match("/^\s*\/\//", $line)) continue;
		
		if (preg_match("/define\s*\(\s*[\\\"\'](.+)[\\\"\']\s*\,\s*[\\\"\'](.*)[\\\"\']/", $line, $bits))
		{
			$defs[$bits[1]] = $bits[2];
		}
	}
	
	return $defs;
}

function AutoGal_ShowMissingLangDefs($lang, $type)
{
	if ($type == 'admin')
	{
		$baseLangDefs = AutoGal_GetLangDefs(AUTOGAL_LANGDIR."/".AUTOGAL_BASELANGUAGE."_Admin.php");
		$langDefs = AutoGal_GetLangDefs(AUTOGAL_LANGDIR."/".$lang."_Admin.php");
		$langLink = "[<a href=\"".AUTOGAL_LANGADMIN."?lang=".urlencode($lang)."&type=regular\">".AUTOGAL_LANG_ADMIN_LANGUAGES_18."</a>]";
		$typeTitle = AUTOGAL_LANG_ADMIN_LANGUAGES_21;
	}
	else
	{
		$baseLangDefs = AutoGal_GetLangDefs(AUTOGAL_LANGDIR."/".AUTOGAL_BASELANGUAGE.".php");
		$langDefs = AutoGal_GetLangDefs(AUTOGAL_LANGDIR."/$lang.php");
		$langLink = "[<a href=\"".AUTOGAL_LANGADMIN."?lang=".urlencode($lang)."&type=admin\">".AUTOGAL_LANG_ADMIN_LANGUAGES_19."</a>]";
		$typeTitle = AUTOGAL_LANG_ADMIN_LANGUAGES_22;
	}
	
	$definedDefs = 0;
	$totalDefs = 0;
	foreach ($baseLangDefs as $defName => $defVal)
	{
		if ($defName == 'AUTOGAL_LANG_AUTHOR') continue;
		if ($defName == 'AUTOGAL_LANG_AUTHORURL') continue;
		if ($defName == 'AUTOGAL_LANG_LASTUPDATE') continue;
		
		if (!isset($langDefs[$defName]))
		{
			$missingDefs[$defName] = $defVal;
		}
		else
		{
			$definedDefs ++;
		}
		
		$totalDefs ++;
	}
	
	ksort($missingDefs);
	
	if (!$missingDefs)
	{
		$text = str_replace('[TYPE]', $typeTitle, str_replace('[LANG]', $lang, AUTOGAL_LANG_ADMIN_LANGUAGES_24));
		$text = "<br /><b>$text</b><br />";
	}
	else
	{
		$msg = AUTOGAL_LANG_ADMIN_LANGUAGES_20;
		$msg = str_replace("[NUMDEF]", $definedDefs, $msg);
		$msg = str_replace("[TOTALDEF]", $totalDefs, $msg);
		$msg = str_replace("[TYPE]", $typeTitle, $msg);
		$msg = str_replace("[LANG]", $lang, $msg);
		$msg = str_replace("[PERCENT]", number_format(($definedDefs / $totalDefs) * 100, 2), $msg);
				
		$text = "
		<b>$msg</b><br />
		<br />
		$langLink<br />
		[<a href=\"".AUTOGAL_LANGADMIN."\">".AUTOGAL_LANG_ADMIN_LANGUAGES_14."</a>]<br />
		<br />
		<table style='width:97%' class='fborder'>
		<tr style='vertical-align:top'>
			<th style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_LANGUAGES_12."</b></th>
			<th style='text-align:center' class='forumheader'><b>".str_replace('[BASELANG]', AUTOGAL_BASELANGUAGE, AUTOGAL_LANG_ADMIN_LANGUAGES_13)."</b></th>
		</tr>";
		
		foreach ($missingDefs as $defName => $defVal)
		{
			$text .= "
			<tr>
				<td style='text-align:left' class='forumheader3'>$defName</td>
				<td style='text-align:left' class='forumheader3'>".htmlspecialchars($defVal)."</td>
			</tr>";
		}
		
		$text .= "</table>";
	}
	
	$text = "
	<div style='text-align:center'>
	$text
	<br />
	$langLink<br />
	[<a href=\"".AUTOGAL_LANGADMIN."\">".AUTOGAL_LANG_ADMIN_LANGUAGES_14."</a>]<br />
	<br />
	</div>";
	
	return $text;
}

function AutoGal_LangAdmin()
{
	$H_LANGDIR = opendir(AUTOGAL_LANGDIR);
	
	while ($langFile = readdir($H_LANGDIR))
	{
		if (!preg_match("/\.php$/i", $langFile)) continue;
		if (preg_match("/\_admin.php$/i", $langFile)) continue;
		
		$path = AUTOGAL_LANGDIR.'/'.$langFile;
		
		if (preg_match("/^(.+).php$/i", $langFile, $bits))
		{
			$language = $bits[1];
			$langs[$language]['file'] = $langFile;
			$langs[$language]['defs'] = AutoGal_GetLangDefs($path);
			
			$adminPath = AUTOGAL_LANGDIR.'/'.$language."_Admin.php";
			if (file_exists($adminPath))
			{
				$langs[$language]['hasadmin'] = 1;
			}
		}
	}
	
	closedir($H_LANGDIR);
	
	$totalDefs = count($langs[AUTOGAL_BASELANGUAGE]['defs']);
	
	foreach ($langs as $lang => $langInfo)
	{
		if ($lang == AUTOGAL_BASELANGUAGE) continue;
		
		foreach ($langs[AUTOGAL_BASELANGUAGE]['defs'] as $defName => $defVal)
		{
			if ($defName == 'AUTOGAL_LANG_AUTHOR') continue;
			if ($defName == 'AUTOGAL_LANG_AUTHORURL') continue;
			if ($defName == 'AUTOGAL_LANG_LASTUPDATE') continue;
			
			$langs[$lang]['iscomplete'] = 0;
			
			if (!isset($langInfo['defs'][$defName]))
			{
				$langs[$lang]['numfails'] ++;
			}
		}
	}
		
	ksort($langs);
	
	################
	# INPUT FIELDS #
	################
	$text = "<div style='text-align:center'>
	".AUTOGAL_LANG_ADMIN_LANGUAGES_15."<br />
	<br />
	<table style='width:97%' class='fborder'>
	<tr style='vertical-align:top'>
		<th style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_LANGUAGES_2."</b></th>
		<th style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_LANGUAGES_3."</b></th>
		<th style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_LANGUAGES_6."</b></th>
		<th style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_LANGUAGES_4."</b></th>
		<th style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_LANGUAGES_7."</b></th>
		<th style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_LANGUAGES_23."</b></th>
	</tr>
	";
	
	foreach ($langs as $lang => $langInfo)
	{
		$authURL = $langInfo['defs']['AUTOGAL_LANG_AUTHORURL'];
		$lastUpdate = $langInfo['defs']['AUTOGAL_LANG_LASTUPDATE'];
		$author = strip_tags($langInfo['defs']['AUTOGAL_LANG_AUTHOR']);
		
		if (!preg_match("/^\d{1,2}\-\w{3}\-\d{4}$/", $lastUpdate)) $lastUpdate = '';
		
		#$isComplete = ($lang == AUTOGAL_BASELANGUAGE ? 1 : $langInfo['iscomplete']);
		
		$numDefs = count($langInfo['defs']);
		
		if ($langInfo['numfails'] <= 0)
		{
			$isComplete = "<b><font color='green'>100%</font></b>";
			$langLink = $lang;
		}
		else
		{
			$pcComplete = number_format(floor((($totalDefs - $langInfo['numfails']) / $totalDefs) * 100), 0);
			$isComplete = "<a href=\"".AUTOGAL_LANGADMIN."?lang=".urlencode($lang)."\"><b><font color='red'>".$pcComplete."%</font></b></a>";
			$langLink = "<a href=\"".AUTOGAL_LANGADMIN."?lang=".urlencode($lang)."\">$lang</a>";
		}
		
		if ($langInfo['hasadmin'])
		{
			$hasAdmin = "<a href=\"".AUTOGAL_LANGADMIN."?lang=".urlencode($lang)."&type=admin\"><font color='green'>".AUTOGAL_LANG_ADMIN_LANGUAGES_8."</font></a>";
		}
		else
		{
			$hasAdmin = "<font color='red'>".AUTOGAL_LANG_ADMIN_LANGUAGES_9."</font>";
		}
		
		$text .= "
		<tr style='vertical-align:top'>
			<td style='text-align:center' class='forumheader3'>$langLink</td>
			<td style='text-align:center' class='forumheader3'>".($author ? $author : AUTOGAL_LANG_ADMIN_LANGUAGES_5)."</td>
			<td style='text-align:center' class='forumheader3'>".($authURL ? "<a href=\"http://$authURL\">$authURL</a>" : AUTOGAL_LANG_ADMIN_LANGUAGES_5)."</td>
			<td style='text-align:center' class='forumheader3'>".($lastUpdate ? $lastUpdate : AUTOGAL_LANG_ADMIN_LANGUAGES_5)."</td>
			<td style='text-align:center' class='forumheader3'>$isComplete</td>
			<td style='text-align:center' class='forumheader3'><b>$hasAdmin</b></td>
		</tr>";
	}
	
	$email = "<a href='mailto:".AUTOGAL_LANGEMAILADDR."'>".AUTOGAL_LANG_ADMIN_LANGUAGES_17."</a>";
	
	$text .= "
	</table>
	<br />
	".str_replace('[EMAIL]', $email, AUTOGAL_LANG_ADMIN_LANGUAGES_16)."<br />
	<br />
	<a href='http://www.cerebralsynergy.com'><img style='border:0' alt='Cerebral Synergy' src='".e_PLUGINS."autogallery/Images/button.png' /></a><br />
	<a href='".AUTOGAL_SUPPORTLINK."'>".AUTOGAL_LANG_ADMIN_MAIN_L46."</a><br />
	</div>";
	
	return $text;
}
?>

