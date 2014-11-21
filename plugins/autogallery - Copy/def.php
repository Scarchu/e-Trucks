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

//
define("e_LANGUAGE", "Bulgarian");
require_once(dirname(__FILE__)."/../../class.php");
require_once(dirname(__FILE__)."/language.php");
require_once(dirname(__FILE__)."/def_core.php");
require_once(AUTOGAL_MEDIAOBJCLASS);

function AutoGal_AdminLog($type, $element, $message)
{
	if (USER){
		$username = USERNAME;
	}else{
		$username = $_SERVER['REMOTE_ADDR'];
	}
	
	if (!$element)
	{
		$element = AUTOGAL_ROOTNAME;
	}
	
	$timeStr = strftime(AUTOGAL_LOGTIMEFORMAT);
	$ADMINLOG = fopen(AUTOGAL_ADMINLOG, 'a+'); 
	
	if ($ADMINLOG)
	{
		flock($ADMINLOG, LOCK_EX);
		
		if (!fwrite($ADMINLOG, "$timeStr> $username [$type] \"$element\": $message\n")) 
		{
			print str_replace('[LOG]', AUTOGAL_ADMINLOG, AUTOGAL_LANG_L42)."<br />\n";
		}
		flock($ADMINLOG, LOCK_UN);
		fclose($ADMINLOG);
	}
	else
	{
		print str_replace('[LOG]', AUTOGAL_ADMINLOG, AUTOGAL_LANG_L43)."<br />\n";
	}
}

function AutoGal_GallerySelectOpts($exclude, $userPrivEnforce) 
{
	$DEBUG = 0;
	
	$rootGalObj = new AutoGal_CMediaObj('');
	$galleries = $rootGalObj->SubGalleries();
	$galleries[] = $rootGalObj;
	
	foreach ($galleries as $gallery)
	{
		$element = $gallery->Element();
		if ($DEBUG) print "[$element] CHECK<br />";
		
		if (isset($exclude))
		{
			if (is_array($exclude))
			{
				if (in_array($element, $exclude)) 
				{
					if ($DEBUG) print "[$element] EXCLUDED<br />";
					continue;
				}
			}
			else
			{
				if ($element == $exclude) 
				{
					if ($DEBUG) print "[$element] EXCLUDED<br />";
					continue;
				}
			}
		}
		
		if (isset($userPrivEnforce))
		{
			if (is_array($exclude))
			{
				foreach ($userPrivEnforce as $userClass)
				{
					if (!$gallery->CheckUserPriv($userClass)) 
					{
						if ($DEBUG) print "[$element] USER CLASS FAIL<br />";
						continue;
					}
				}
			}
			else
			{
				if (!$gallery->CheckUserPriv($userPrivEnforce)) 
				{
					if ($DEBUG) print "[$element] USER CLASS FAIL<br />";
					continue;
				}
			}
		}
		
		$title = $gallery->PathTitle(' -> ');
		
		$option['title'] = $title;
		$option['element'] = $element;
		
		$options[] = $option;
	}
	
	usort($options, "AutoGal_SortGalOpts");
	
	return $options;
}

function AutoGal_SortGalOpts($a, $b)
{
	if ($a['element'] == '') return -1;
	if ($b['element'] == '') return 1;
	
	$cmpA = strtolower($a['title']);
	$cmpB = strtolower($b['title']);
	
	if ($cmpA == $cmpB) return 0;
	return ($cmpA < $cmpB) ? -1 : 1;
}

function AutoGal_GallerySelect($selected, $exclude, $userPrivEnforce, &$options) 
{
	if (!$options)
	{
		$options = AutoGal_GallerySelectOpts($exclude, $userPrivEnforce);
	}
	
	foreach ($options as $opt)
    {
        $select .= "<option value=\"".htmlspecialchars($opt['element'])."\"".(((isset($selected))&&($selected == $opt['element'])) ? ' selected="selected"' : '').">".$opt['title']."</option>\n";
    }
    
    return $select;
}

function AutoGal_GetImageDimensions($image, $isFullPath=false)
{
    if (!$isFullPath) $image = AutoGal_GetAbsGalPath($image);
	$image_stats = getimagesize($image);
    $imageSize['w'] = $image_stats[0];
	$imageSize['h'] = $image_stats[1];
    
    return $imageSize; 
}

function AutoGal_GetImageURL($element)
{
	$element = AutoGal_CleanPath($element);
	
	if ((preg_match('/['.chr(127).'-'.chr(255).']/', $element))&&(!AutoGal_IsFlashVideo($element)))
	{
		return (AUTOGAL_SHOWIMAGE."?img=".rawurlencode($element));
	}
	else
	{
		return AutoGal_CleanPath(AUTOGAL_BASE.str_replace('//', '/', '/'.AUTOGAL_GALLERYDIR.'/'.$element));
	}
}

function AutoGal_IsUploadAllowed()
{
	return AutoGal_CheckUserClass(AUTOGAL_REVUPLOADUC);
}

function AutoGal_IsReviewAllowed()
{
	return AutoGal_CheckUserClass(AUTOGAL_ADMINREVIEWUC);
}

function AutoGal_IsRatingAllowed()
{
	return AutoGal_CheckUserClass(AUTOGAL_RATECLASS);
}

function AutoGal_IsEditAllowed()
{
	return ((AutoGal_IsMainAdmin())||AutoGal_CheckUserClass(AUTOGAL_EDITUSERCLASS));
}

function AutoGal_IsUserGalleryAllowed()
{
	return ((AUTOGAL_USERGALENABLE)&&(AutoGal_CheckUserClass(AUTOGAL_USERGALUSERCLASS)));
}

# So we can detach from e107 in the future...
function AutoGal_CheckUserClass($class)
{
	//require_once(e_HANDLER."userclass_class.php");
	//return check_class($class);
	return true;
}

# So we can detach from e107 in the future...
function AutoGal_UserClassName($class)
{
	require_once(e_HANDLER."userclass_class.php");
	return r_userclass_name($class);
}

# So we can detach from e107 in the future...
function AutoGal_UserClassSelect($name, $selected, $types='public,member,guest,admin,classes,nobody')
{
	require_once(e_HANDLER."userclass_class.php");
	return r_userclass($name, $selected, 'off', $types);
}

function AutoGal_UserGallery($username, &$userGalObj)
{
	if (!AUTOGAL_USERGALENABLE) return;
	if (!isset($username)) $username = USERNAME;
	if ($username == '') return;
	
	if (!$userGalObj) $userGalObj = new AutoGal_CMediaObj(AUTOGAL_USERGALLERYDIR);
	
	$username = AutoGal_RemoveIllegalFileChars($username);
	if (!$username) return;
		
	$subGals = $userGalObj->ChildMediaObjs();
	foreach ($subGals['galleries'] as $gallObj)
	{
		if ($gallObj->UserGalleryOwner() == $username)
		{
			return $gallObj;
		}
	}
}

function AutoGal_NumUploads()
{
    $uploadCount = 0;
    
    $dh = opendir(AUTOGAL_UPLOADDIRABS);
    while ($file = readdir($dh))
    {
		if (!AutoGal_IsMediaFile($file)) continue;
        $uploadCount ++;
    }
    closedir($dh);
    
    return ($uploadCount);
}

function AutoGal_GetReviewLink()
{
	if (AutoGal_IsReviewAllowed())
	{
		return "[<a href=\"".AUTOGAL_REVIEWUPLOADS."\">".AUTOGAL_LANG_L21.(AUTOGAL_SHOWREVIEWCOUNT ? " (".AutoGal_NumUploads().")" : '')."</a>]";
	}
}

function AutoGal_GetUploadLink($element)
{
	global $g_mediaObj;
	
	if (($g_mediaObj)&&($g_mediaObj->Element() == $element))
	{
		$mediaObj = $g_mediaObj;
	}
	else
	{
		$mediaObj = new AutoGal_CMediaObj($element);
	}
	
	if ($mediaObj->IsFile())
	{
		$mediaObj = $mediaObj->GalleryMediaObj();
	}
	
	if ($mediaObj->CheckUserPriv('upload'))
	{
		return "[<a href=\"".AUTOGAL_UPLOAD.($mediaObj->Element() ? "?gallery=".rawurlencode($mediaObj->Element()) : '')."\">".AUTOGAL_LANG_L19."</a>]";
	}
}

function AutoGal_GetStatNewestLink()
{
	return (AUTOGAL_SHOWNEWESTLINK ? "[<a href=\"".AUTOGAL_STATNEWEST."\">".AUTOGAL_LANG_STAT_L12."</a>]" : '');
}

function AutoGal_GetSearchLink($gallery)
{
	return (AUTOGAL_ENABLESEARCH ? "[<a href=\"".AUTOGAL_SEARCH.($gallery != '' ? '?gallery='.rawurlencode($gallery) : '')."\">".AUTOGAL_LANG_SEARCH_L0."</a>]" : '');
}

function AutoGal_GetLatestCommentsLink()
{
	return (AUTOGAL_DOLATESTCOMMS ? "[<a href=\"".AUTOGAL_LATESTCOMMSVIEW."\">".AUTOGAL_LANG_COMMENTS_L14."</a>]" : '');
}

function AutoGal_GetAdminLink()
{
	return (AutoGal_IsMainAdmin() ? "[<a href=\"".AUTOGAL_CONFIG."\">".AUTOGAL_LANG_L23."</a>] " : '');
}

function AutoGal_GetEmailLink($image)
{
	return (AUTOGAL_SHOWEMAILTOFRIEND ? "<b><a href=\"".AUTOGAL_EMAILTOFRIEND."?ele=".rawurlencode($image).(AUTOGAL_SHOWINNEWWINDOW ? "&newwindow=1" : '')."\">".AUTOGAL_LANG_L20."</a></b>" : '');
}

function AutoGal_GetBotLinksStr($imageGallery='', $showReview=true, $showNewest=true, $showMain=true, $showSearch=true, $showLatestComms=true)
{
	$botLinks = AutoGal_GetBotLinks($imageGallery, $showReview, $showNewest, $showMain, $showSearch, $showLatestComms);
	$str = (count($botLinks) > 0 ? implode(' ', $botLinks) : '');
	return $str;
}

function AutoGal_GetBotLinks($imageGallery='', $showReview=true, $showNewest=true, $showMain=true, $showSearch=true, $showLatestComms=true)
{
	$botLinks = array();

	if ($showMain)
	{
		$botLinks[] = "[<a href=\"".AUTOGAL_AUTOGALLERY."\">".AUTOGAL_ROOTNAME.' '.AUTOGAL_LANG_L24."</a>]";
	}
	
	if ($showNewest)
	{
		$link = AutoGal_GetStatNewestLink();
		if ($link) $botLinks[] = $link;
	}
	
	if ($imageGallery !== false)
	{
		$link = AutoGal_GetUploadLink($imageGallery);
		if ($link) $botLinks[] = $link;
	}
	
	if ($showReview)
	{
		$link = AutoGal_GetReviewLink();
		if ($link) $botLinks[] = $link;
	}
	
	if ($showSearch)
	{
		$link = AutoGal_GetSearchLink($imageGallery);
		if ($link) $botLinks[] = $link;
	}
	
	if ($showLatestComms)
	{	
		$link = AutoGal_GetLatestCommentsLink();
		if ($link) $botLinks[] = $link;
	}
	
	$link = AutoGal_GetAdminLink();
	if ($link) $botLinks[] = $link;
	
	return $botLinks;
}

function AutoGal_GetVersion()
{
	$sqlVer = new db;
	$sqlVer->db_Select("plugin", "plugin_version", "plugin_name='Auto Gallery'");
	list($autoGalVer) = $sqlVer->db_Fetch();
	
	return $autoGalVer;
}

function AutoGal_CheckResizeMethod(&$text)
{
	global $pref;
	
	$mode = ($pref['resize_method'] ? $pref['resize_method'] : "gd2");
	
    $renderTop = 0;
    if (preg_match("/^gd\d/", $mode))
    {
        if (!extension_loaded('gd')) 
		{
			$text = "<font color='red'><b>".AUTOGAL_LANG_L44."</b></font> ".str_replace('[MODE]', $mode, AUTOGAL_LANG_L45)." (<a href='".e_ADMIN."image.php'>".AUTOGAL_LANG_L46."</a>)";
			return false;
		}
		else
		{
			if (!function_exists('gd_info')) 
			{
				$text = "<font color='red'><b>".AUTOGAL_LANG_L44."</b></font> ".str_replace('[MODE]', $mode, AUTOGAL_LANG_L47)." (<a href='".e_ADMIN."image.php'>".AUTOGAL_LANG_L46."</a>)";
				return false;
			}
			$gdInfo = gd_info();
			
			$text = "<table colspan='2' class='border' cellpadding='3'><tr><td colspan='2' class='forumheader'><a href='http://www.boutell.com/gd/faq.html'>GD</a> ".AUTOGAL_LANG_L48."</td></tr>\n";
			foreach ($gdInfo as $gdField => $gdValue)
			{
				if (preg_match("/Support$/", $gdField))
				{
					if ($gdValue == 1) 
						$gdValue = 'Yes'; 
					elseif ($gdValue == '') 
						$gdValue = 'No';
				}
				$text .= "<tr><td class='forumheader3'><b>$gdField:</b></td><td class='forumheader3'>$gdValue</td></tr>\n";
			}
			$text .= "</table>";
			
			return true;
		}
    }
    elseif($mode == 'ImageMagick')
    {
        $imPath = $pref['im_path'];
        
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
			$text = "<b>".AUTOGAL_LANG_L44."</b> ".str_replace('[CMD]', "$cmd", str_replace('[MODE]', $mode, AUTOGAL_LANG_L49))." (<a href='".e_ADMIN."image.php'>".AUTOGAL_LANG_L46."</a>)";
			return false;
		}
		else
		{
			$IMVersion = str_replace("\n", "<br />", $IMVersion);
			$IMVersion = preg_replace("/(\<br\>)*Usage\:.+$/mi", "", $IMVersion);
			$IMVersion = preg_replace("/(\<br\>)+$/mi", "", $IMVersion);

			$text = "
			<table class='border' cellpadding='3'><tr><td class='forumheader'><a href='http://imagemagick.org'>ImageMagick</a> ".AUTOGAL_LANG_L48."</td></tr>
			<tr><td class='forumheader3'>$IMVersion</td></tr>
			</table>";
		}
		
		return true;
    }
	else
	{
		$text = "<b>".AUTOGAL_LANG_L44."</b> ".AUTOGAL_LANG_L50." (<a href='".e_ADMIN."image.php'>".AUTOGAL_LANG_L46."</a>)"; 
		return false;
	}
}

function AutoGal_GetLatestFiles($maxFiles=10, $start=0, &$totalFiles)
{
	$DEBUG = 0;
	$vClasses = array();
	
	if ((AUTOGAL_AUTHCACHELATEST)&&(AUTOGAL_ENABLEDBCACHE))
	{
		$sql = "SELECT COUNT(element) FROM ".AUTOGAL_DIRCACHETABLE." WHERE etype='f'";
		$sth = mysql_query($sql);
		list($totalFiles) = mysql_fetch_array($sth, MYSQL_NUM);
		
		if ($totalFiles > 0)
		{
			$sql = "SELECT * FROM ".AUTOGAL_DIRCACHETABLE." WHERE etype='f' ORDER BY ".(AUTOGAL_SORTDATECTIME ? "ctime" : "mtime")." DESC LIMIT $start,$maxFiles";
			$sth = mysql_query($sql);
			
			if (!$sth)
			{
				die("SQL Query Error [$sql]: ".mysql_error());
			}
			
			while ($row = mysql_fetch_array($sth, MYSQL_ASSOC))
			{
				$element = $row['element'];
				$mediaObj = new AutoGal_CMediaObj($element);
								
				if (AUTOGAL_CHECKLATESTVCLASS)
				{
					$gallObj = $mediaObj->GalleryMediaObj();
					$gallEle = $gallObj->Element();
					
					if (!isset($vClasses[$gallEle]))
					{
						$vClasses[$gallEle] = $gallObj->CheckUserPriv('view');
						if ($DEBUG) print "VCLASS $gallEle READ = ".$vClasses[$gallEle]."<br />";
					}
					else
					{
						if ($DEBUG) print "VCLASS $gallEle CACHED = ".$vClasses[$gallEle]."<br />";
					}
					
					if (!$vClasses[$gallEle]) continue;
				}
				
				$mediaObj->SetValsFromCache($row);
				$files[] = $mediaObj;
			}
		}
			
		return $files;
	}
	else
	{
		require_once(AUTOGAL_MEDIALISTCLASS);
		
		$opts = array
		(
			'sortorder' => 'name',
			'recurse' => true,
			'usecache' => AUTOGAL_ENABLEDBCACHE,
		);
		
		$stackSize = $maxFiles + $start;
		$stack = array();
			
		$totalFiles = 0;
		$galList = new AutoGal_CMediaList('', $opts);
		while ($mediaObj = $galList->NextElement())
		{
			if ($mediaObj->IsGallery()) continue;
			
			if (AUTOGAL_CHECKLATESTVCLASS)
			{
				$gallObj = $mediaObj->GalleryMediaObj();
				$gallEle = $gallObj->Element();
				
				if (!isset($vClasses[$gallEle]))
				{
					$vClasses[$gallEle] = $gallObj->CheckUserPriv('view');
					if ($DEBUG) print "VCLASS $gallEle READ = ".$vClasses[$gallEle]."<br />";
				}
				else
				{
					if ($DEBUG) print "VCLASS $gallEle CACHED = ".$vClasses[$gallEle]."<br />";
				}
				
				if (!$vClasses[$gallEle]) continue;
			}
			
			$totalFiles ++;
			$stack[] = $mediaObj;
			
			usort($stack, "AutoGal_CmpMediaObjsByDate");
			if (count($stack) > $stackSize)
			{
				array_pop($stack);
			}
		}
		
		$stack = array_slice($stack, $start, $maxFiles);
		return $stack;
	}
}

function IsBadUploadDirPerms()
{
	if (AUTOGAL_CHMODWARNOFF) return;
	
	if (!file_exists(AUTOGAL_UPLOADDIRABS))
	{
		return "<div style='text-align:center'><font color='red'><b>".AUTOGAL_LANG_L51."</b></font> ".str_replace('[DIR]', AUTOGAL_UPLOADDIRABS, AUTOGAL_LANG_L52)."</div>";
	}
	
	$filePerms = fileperms(AUTOGAL_UPLOADDIRABS);
	$filePermsNum = substr(sprintf('%o', $filePerms), -3);
	$filePermsStr = AutoGal_FormatFilePerms($filePerms) . " ($filePermsNum)";
	if ($filePermsNum <> AUTOGAL_PERMSUPLDIR)
	{
		return "<div style='text-align: center'><font color='red'><b>".AUTOGAL_LANG_UPLOAD_L22." </b></font>".str_replace("[PERMS]", $filePermsStr, str_replace("[DIR]", AUTOGAL_UPLOADDIRABS, AUTOGAL_LANG_UPLOAD_L21))."<br />".AUTOGAL_LANG_L53."$filePermsStr</div>";
	}
	
	return "";
}

function IsBadGalleryDirPerms($gallery='')
{
	if (AUTOGAL_CHMODWARNOFF) return;
	
	$absPath = AutoGal_GetAbsGalPath($gallery);
	
	if (!file_exists($absPath))
	{
		return "<div style='text-align:center'><font color='red'><b>".AUTOGAL_LANG_L51."</b></font>".str_replace('[DIR]', $gallery, AUTOGAL_LANG_L54).(AUTOGAL_GALLERYDIR ? ' ('.AUTOGAL_GALLERYDIR.')' : '')."</div>";
	}
	
	$filePerms = fileperms($absPath);
	$filePermsNum = substr(sprintf('%o', $filePerms), -3);
	$filePermsStr = AutoGal_FormatFilePerms($filePerms) . " ($filePermsNum)";
	if ($filePermsNum <> AUTOGAL_PERMSGALDIR)
	{
		return "<div style='text-align:center'><font color='red'><b>".AUTOGAL_LANG_L51."</b></font>".str_replace('[DIR]', $gallery, str_replace('[PERMS]', AUTOGAL_PERMSGALDIR, AUTOGAL_LANG_L55))."<br />".AUTOGAL_LANG_L53."$filePermsStr</div>";
	}
	
	return "";
}

function IsBadLogDirPerms()
{
	if (AUTOGAL_CHMODWARNOFF) return;
	
	if (!file_exists(realpath(AUTOGAL_LOGDIR)))
	{
		return "<div style='text-align:center'><font color='red'><b>".AUTOGAL_LANG_L51."</b></font>".str_replace('[DIR]', AUTOGAL_LOGDIR, AUTOGAL_LANG_L56)."</div>";
	}
	
	$filePerms = fileperms(realpath(AUTOGAL_LOGDIR));
	$filePermsNum = substr(sprintf('%o', $filePerms), -3);
	$filePermsStr = AutoGal_FormatFilePerms($filePerms) . " ($filePermsNum)";
	if ($filePermsNum <> AUTOGAL_PERMSLOGDIR)
	{
		return "<div style='text-align:center'><font color='red'><b>".AUTOGAL_LANG_L51."</b></font>".str_replace('[DIR]', AUTOGAL_LOGDIR, str_replace('[PERMS]', AUTOGAL_PERMSLOGDIR, AUTOGAL_LANG_L57))."<br />".AUTOGAL_LANG_L53."$filePermsStr</div>";
	}
	
	return "";
}

function AutoGal_GetNewWindowHeader($title, $headExtra, $bodyClass, $bodyStyle)
{
	return 
	"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n".
	"<html xmlns=\"http://www.w3.org/1999/xhtml\">\n".
	"<head>\n".
	($title ? "<title>".$title."</title>" : '').
	"<link rel='stylesheet' href='".e_FILE."e107.css' type='text/css' />\n".
	"<link rel='stylesheet' href='".THEME."style.css' type='text/css' />\n".
	"<meta http-equiv='Content-Type' content='text/html; charset=".AUTOGAL_CHARSET."' />\n".
	"<meta http-equiv='content-style-type' content='text/css' />\n".
	($headExtra ? "$headExtra\n" : '').
	"<script type='text/javascript' src='".e_FILE."e107.js'></script>\n".
	"</head>\n".
	"<body".($bodyClass ? " class='$bodyClass'" : '').($bodyStyle ? " style='$bodyStyle'" : '').">\n";
}

function AutoGal_GetNewWindowFooter()
{
	return "
	</body>
	</html>";
}

function AutoGal_XMLFilePermsError($file, $needPermsXml=AUTOGAL_PERMSCFGXML, $needPermsDir=AUTOGAL_PERMSCFGDIR)
{
	if (!$needPermsXml) $needPermsXml = AUTOGAL_PERMSCFGXML;
	if (!$needPermsDir) $needPermsDir = AUTOGAL_PERMSCFGDIR;
		
	$xmlPerms = fileperms($file);
	$xmlPerms = substr(sprintf('%o', $xmlPerms), -3);
	
	$dirPerms = fileperms(dirname($file));
	$dirPerms = substr(sprintf('%o', $dirPerms), -3);
	
	if ($dirPerms != $needPermsDir)
	{
		$errMsgLang = AUTOGAL_LANG_XML_L2;
		$errMsgLang = str_replace("[DIR]", dirname($file), $errMsgLang);
		$errMsgLang = str_replace("[CURPERMS]", $xmlPerms, $errMsgLang);
		$errMsgLang = str_replace("[NEEDPERMS]", $needPermsXml, $errMsgLang);
	}
	elseif ($xmlPerms != $needPermsXml)
	{
		$errMsgLang = AUTOGAL_LANG_XML_L3;
		$errMsgLang = str_replace("[FILE]", $file, $errMsgLang);
		$errMsgLang = str_replace("[CURPERMS]", $xmlPerms, $errMsgLang);
		$errMsgLang = str_replace("[NEEDPERMS]", $needPermsXml, $errMsgLang);
	}
	
	$errMsg = AUTOGAL_LANG_XML_L1.'<br /><br />'.($errMsgLang ? $errMsgLang : $file);
	
	return $errMsg;
}

// MAY SEEM STUPID NOW, BUT THIS IS SO I CAN SEPERATE FROM e107 EASILY
function AutoGal_DoBBCode($text)
{
	require_once(e_HANDLER."bbcode_handler.php");
	$bbHandler = new e_bbcode;
	$text = htmlspecialchars($text);
	$text = $bbHandler->parseBBCodes($text);
	$text = nl2br($text);
	
	return $text;
}

function AutoGal_GetEleFromUrl($url, &$error)
{
	$server = $_SERVER['SERVER_NAME'];
	$uri = $_SERVER['REQUEST_URI'];
	
	$error = "<font face='courier new'>
	Server Name: ".htmlspecialchars($_SERVER['SERVER_NAME'])."<br />
	Request URI: ".htmlspecialchars($_SERVER['REQUEST_URI'])."<br />";
	
	# GET FROM SHOW VARIABLE
	$url = preg_replace("/^(http|https)\:\/\//", '', $url);
	$url = preg_replace("/^www\./", '', $url);
	$server = preg_replace("/^www\./", '', $server);
	
	$uriDir = dirname($uri);
	if (!preg_match("/^\//", $uriDir)) $uriDir = "/$uriDir";
	if (!preg_match("/\/$/", $uriDir)) $uriDir .= "/";
	
	$testRegex = preg_quote($server.$uriDir.basename(AUTOGAL_AUTOGALLERY), '/');
	if (preg_match("/^$testRegex(.*)$/i", $url, $bits))
	{
		$urlInfo = parse_url($url);
		parse_str($urlInfo['query'], $getVars);
		
		if ($getVars['show'])
		{	
			$error = '';
			$element = $getVars['show'];
			$element = rawurldecode($element);
			return $element;
		}
	}
	
	$error .= "<b>Show Var Test</b><br />Refferer: \"".htmlspecialchars($url)."\"<br />Regex: /^".htmlspecialchars($testRegex)."/i<br />";
	
	# GET FROM REFFERER URL PATH
	$galHttpDir = $_SERVER['SCRIPT_URI'];
	
	if ($galHttpDir)
	{
		$galHttpDir = preg_replace("/^(\w+)\:\/\//", '', $galHttpDir);
		$galHttpDir = preg_replace("/^www\./", '', $galHttpDir);
		$galHttpDir = dirname($galHttpDir);
	}
	else
	{
		$galHttpDir = $server.$uriDir;
	}
	
	if (!preg_match("/\/$/", $galHttpDir)) $galHttpDir .= "/";
	$galHttpDir .= AUTOGAL_GALLERYDIR;
	
	$galHttpDirClean = AutoGal_CleanPath($galHttpDir);
		
	if (preg_match("/^".preg_quote($galHttpDirClean, '/')."(.+)$/i", $url, $bits))
	{
		$element = $bits[1];
		$element = preg_replace("/^[\/]+/", '', $element);
		$element = rawurldecode($element);
		$error = '';
		return $element;
	}
	
	# NUP, NOTHING WORKED
	$error .= "
	<b>Path Test</b><br />
	URL: \"".htmlspecialchars($url)."\"<br />
	Script URI: \"".htmlspecialchars($_SERVER['SCRIPT_URI'])."\"<br />
	Gallery HTTP Dir: \"".htmlspecialchars($galHttpDir)."\"<br />
	Cleaned: \"".htmlspecialchars($galHttpDirClean)."\"<br />
	Gallery Dir: \"".htmlspecialchars(AUTOGAL_GALLERYDIR)."\"<br />".
	"<br />".
	"POST:<br />";
	foreach ($_POST as $var => $val)
	{
		$error .= htmlspecialchars($var)."=\"".htmlspecialchars($val)."\"";
	}
	
	$error .= "
	<br />
	SESSION:<br />";
	foreach ($_SESSION as $var => $val)
	{
		$error .= htmlspecialchars($var)."=\"".htmlspecialchars($val)."\"";
	}
	
	$error .= "
	<br />
	</font>
	No show variable in referrer/invalid refferer!";
	
		
	return '';
}

function AutoGal_ResizeImage($imgPath, $destPath, $width, $height, $keepAspect=null)
{
	global $pref;
	
	require_once(AUTOGAL_IMGMANIPHANDLER);
	
	if ($keepAspect == null) $keepAspect = AUTOGAL_KEEPASPECT;
	
	$mode = $pref['resize_method'];
	$imPath = $pref['im_path'];
	$imQuality = ($pref['im_quality'] ? $pref['im_quality'] : 99);
	
	$gdim = new GDIM($mode, $imPath, $imQuality);
	
	if (!$gdim->resize($imgPath, $destPath, $width, $height, array('keepaspect' => $keepAspect)))
	{
		return $gdim->lastError();
	}
	
	return '';
}

function AutoGal_UploadFile($name, $path)
{
	if (move_uploaded_file($_FILES[$name]['tmp_name'], $path))
	{
		if (!file_exists($path))
		{
			$msgs = str_replace('[DIR]', dirname($path), AUTOGAL_LANG_UPLOAD_L26);
			return array(0, $msgs);
		}
		else
		{
			return array(1, '');
		}
	}
	
	switch($_FILES[$name]['error'])
	{
		case UPLOAD_ERR_INI_SIZE: $msgs = AUTOGAL_LANG_UPLOAD_L9; break;
		case UPLOAD_ERR_FORM_SIZE: $msgs = AUTOGAL_LANG_UPLOAD_L10 . AUTOGAL_UPLOADMAXSIZE . " " . AUTOGAL_LANG_UPLOAD_L27; break;
		case UPLOAD_ERR_NO_TMP_DIR: $msgs = AUTOGAL_LANG_UPLOAD_L11; break;
		case UPLOAD_ERR_NO_FILE: $msgs = AUTOGAL_LANG_UPLOAD_L12; break;
		default: $msgs = "(".$_FILES[$name]['error'].") ".str_replace('[DIR]', dirname($path), AUTOGAL_LANG_UPLOAD_L26);
	}
	
	return array(0, $msgs);
}

function AutoGal_ClearCacheMenu($gallery, $incSubGals, $inIFrame=false)
{
	if (!AUTOGAL_ENABLEDBCACHE) return;
	global $ns;
	
	if (is_array($gallery))
	{
		# De-duplicate galleries
		$galleryList = $gallery;
		foreach ($galleryList as $gallery)
		{
			$galleries[$gallery] = 1;
		}
		
		$galleries = array_keys($galleries);
	}
	else
	{
		$galleries[] = $gallery;
	}
	
	if ($inIFrame)
	{
		$text = "<iframe src=\"".AUTOGAL_ADMINACTION."?op=clearcache".($incSubGals ? 'r' : '')."&ele=".rawurlencode(implode('|', $galleries))."\" width='100%' frameborder='1' scrolling='yes' height='".AUTOGAL_ADMINACTIONBOXHEIGHT."'></iframe>";
	}
	else
	{
		$msgs = AutoGal_ClearCache($gallery, $incSubGals, 0);
		for ($msgI = 0; $msgI < count($msgs); $msgI ++)
		{
			$msgs[$msgI] = htmlspecialchars($msgs[$msgI]);
		}
		
		$text = implode("<br />", $msgs);
	}
	
	$ns->tablerender(AUTOGAL_LANG_CACHE_L4, $text);
}

function AutoGal_ClearCache($gallery, $incSubGals=false, $printMsgs=false)
{
	if (!AUTOGAL_ENABLEDBCACHE) return array(AUTOGAL_LANG_ADMIN_CACHE_4);
	
	if (is_array($gallery))
	{
		$galleryList = $gallery;
		foreach ($galleryList as $gallery)
		{
			$galleries[$gallery] = 1;
			$galleries = array_keys($galleries);
		}
	}
	else
	{
		$galleries[] = $gallery;
	}
	
	foreach ($galleries as $gallery)
	{
		$gallObj = new AutoGal_CMediaObj($gallery);
		
		if (!$gallObj->CheckUserPriv('clearcache'))
		{
			$msg = AUTOGAL_LANG_ADMIN_EDIT_115;
			$msg = str_replace("[OPERATION]", 'clearcache', $msg);
			$msg = str_replace("[USER]", (USERNAME ? USERNAME : "(guest)"), $msg);
			$msg = str_replace("[GALLERY]", $gallObj->Element(), $msg);
			$msgs[] = $msg;
			if ($printMsgs) print "$msg\n";
			continue;
		}
		
		if (($gallery == '')&&($incSubGals))
		{
			$sql = "TRUNCATE ".AUTOGAL_DIRCACHETABLE;
		}
		elseif ($incSubGals)
		{
			$sql = "DELETE FROM ".AUTOGAL_DIRCACHETABLE." WHERE element LIKE '".mysql_escape_string($gallery)."/%'";
		}
		else
		{
			$parent = ($gallery ? $gallery : '*');
			$sql = "DELETE FROM ".AUTOGAL_DIRCACHETABLE." WHERE parent='".mysql_escape_string($parent)."'";
		}
		
		if (!mysql_unbuffered_query($sql))
		{
			$msgs[] = "*** SQL ERROR [$sql]: ".mysql_error();
			if ($printMsgs) print "$msg\n";
		}
		else
		{
			
			$msg = str_replace("[GALLERY]", $gallObj->PathTitle('/'), ($incSubGals ? AUTOGAL_LANG_ADMIN_CACHE_3 : AUTOGAL_LANG_ADMIN_CACHE_2));
			$msgs[] = $msg;
			if ($printMsgs) print "$msg\n";
		}
	}
	
	return $msgs;
}

function AutoGal_NewWindowJS($type, $url, $title)
{
	$consts = get_defined_constants();
		
	$type = strtoupper($type);
	
	$width 			= $consts['AUTOGAL_'.$type.'NWINWIDTH'];
	$height 		= $consts['AUTOGAL_'.$type.'NWINHEIGHT'];
	$toolBar 		= ($consts['AUTOGAL_'.$type.'NWINTOOBAR'] ? "yes" : "no");
	$location 		= ($consts['AUTOGAL_'.$type.'NWINLOCBAR'] ? "yes" : "no");
	$directories 	= ($consts['AUTOGAL_'.$type.'NWINDIRECT'] ? "yes" : "no");
	$status 		= ($consts['AUTOGAL_'.$type.'NWINSTSBAR'] ? "yes" : "no");
	$menuBar 		= ($consts['AUTOGAL_'.$type.'NWINMNUBAR'] ? "yes" : "no");
	$scrollBars 	= ($consts['AUTOGAL_'.$type.'NWINSCRBAR'] ? "yes" : "no");
	$copyHistory 	= ($consts['AUTOGAL_'.$type.'NWINCPHIST'] ? "yes" : "no");
	$resizable 		= ($consts['AUTOGAL_'.$type.'NWINRESIZE'] ? "yes" : "no");
	$extraArgs 		= ($consts['AUTOGAL_'.$type.'NWINEXARGS'] ? "&".$consts['AUTOGAL_'.$type.'NWINEXARG'] : '');
		
	$windowArgs = "width=$width,height=$height,toolbar=$toolBar,location=$location,directories=$directories,status=$status,menubar=$menuBar,scrollbars=$scrollBars,copyhistory=$copyHistory,resizable=$resizable";
	
	# Farkin'
	$winOpenArgs = htmlspecialchars('"'.$url.'","'.$title.'","'.$windowArgs.'"', ENT_QUOTES);
	
	return 'window.open('.$winOpenArgs.')';
}

function AutoGal_AdminModeLink($mediaObj)
{
	global $g_isAdminMode;
	global $g_startFile;
	global $g_startGallery;
	
	$getVarPrefix = '&';
	if ($mediaObj->IsRoot()) $getVarPrefix = '?';
	
	if ($g_isAdminMode)
	{
		$text = "[<a href=\"".$mediaObj->Link().$getVarPrefix."adminmode=0&start=$g_startFile&startgal=$g_startGallery\">".AUTOGAL_LANG_ADMIN_MODE_1."</a>]";
	}
	else
	{
		$text = "[<a href=\"".$mediaObj->Link().$getVarPrefix."adminmode=1&start=$g_startFile&startgal=$g_startGallery\">".AUTOGAL_LANG_ADMIN_MODE_0."</a>]";
	}
	
	return $text;
}

function AutoGal_AddScoreGetUser($points)
{
	if (!AUTOGAL_ARCTOPSCORES) return;
	
	if (AUTOGAL_ARCADEUSEXMLTRACK)
	{
		require_once (AUTOGAL_ARCADEPLAYERS);
		$players = new AutoGal_ArcadePlayers(AUTOGAL_ARCADEPLAYERSXML);
		
		$errMsg .= "<b>XML Player Tracker</b><br />";
		if (file_exists(AUTOGAL_ARCADEPLAYERSXML))
		{
			if (!$players->Open())
			{
				$errMsg .= "XML player tracker file (".AUTOGAL_ARCADEPLAYERSXML.") open error: ".$players->LastError()."<br />";
				print $errMsg;
				exit;
			}
			
			$element = $players->PlayerElement(USERID);
			
			if (!$element)
			{
				$errMsg .= "No element for USERID=".USERID."<br />";
				print $errMsg;
				exit;
			}
		}
		else
		{
			$errMsg .= "Player tracker file \"".AUTOGAL_ARCADEPLAYERSXML."\" does not exist<br />";
			print $errMsg;
			exit;
		}
	}
	else
	{
		$referer = $_SERVER['HTTP_REFERER'];
		$element = AutoGal_GetEleFromUrl($referer, $error);
		
		if (!$element)
		{
			print AUTOGAL_LANG_ARCADE_L7.htmlspecialchars($referer)."<br />$error<br />".AUTOGAL_LANG_ARCADE_L9;
			exit;
		}
	}
	
	if ((!defined('USERID'))||(!defined('USERNAME')))
	{
		print AUTOGAL_LANG_ARCADE_L6."<br />";
		exit;
	}
	
	if (!AutoGal_AddScore($element, USERID, USERNAME, $points, $error))
	{
		$addScorErrMsg = AUTOGAL_LANG_ARCADE_L8;
		$addScorErrMsg = str_replace("[SCORE]", htmlspecialchars($points), $addScorErrMsg);
		$addScorErrMsg = str_replace("[ELEMENT]", htmlspecialchars($element), $addScorErrMsg);
		$errMsg .= "$addScorErrMsg<br /><br />$error<br />";
		print $errMsg;
		exit;
	}
	
	header("location: ".AUTOGAL_AUTOGALLERY."?show=".rawurlencode($element));
	exit;
}

function AutoGal_AddScore($element, $userID, $username, $points, &$error)
{
	if (!AUTOGAL_ARCTOPSCORES)
	{
		$error = AUTOGAL_LANG_ARCADE_L10;
		return 0;
	}
	
	if (!$element) 
	{
		$error = "Blank element '$element'";
		return 0;
	}
	
	if (!preg_match("/^[0-9]+$/", $userID))
	{
		$error = "User ID '$userID' not numeric";
		return 0;
	}
	
	if (!$username)
	{
		$error = "Username blank";
		return 0;
	}
	
	if (!preg_match("/^\-?[\.0-9]+$/", $points)) 
	{
		$error = "Points '$points' not numeric";
		return 0;
	}
	
	$mediaObj = new AutoGal_CMediaObj($element);
	
	if (!$mediaObj->IsValid())
	{
		$error = "Invalid element \"$element\" (".$mediaObj->LastError().")";
		return 0;
	}
	
	if ($mediaObj->FileType() != 'flash') 
	{
		$error = "\"".$mediaObj->Element()."\" is not a flash file!";
		return 0;
	}
	
	if (!$mediaObj->ArcadeAddTopScore($points, $userID, $username))
	{
		$error = "\"".$mediaObj->Element()."\" add score error: ".$mediaObj->LastError()."!";
		return 0;
	}
	
	if (!$mediaObj->SaveMeta())
	{
		$error = "\"".$mediaObj->Element()."\" save meta: ".$mediaObj->LastError()."!";
		return 0;
	}
	
	return 1;
}

function AutoGal_SearchMediaObjs($gallery, $searchStr, $targets, $extensions)
{
	$matches = array();
	$searchGals = in_array('', $extensions);
	$searchRegex = preg_quote($searchStr, '/');
	$searchRegex = str_replace("\*", ".*", $searchRegex);
	$DEBUG = 0;
	
	if ((AUTOGAL_AUTHCACHESEARCH)&&(AUTOGAL_ENABLEDBCACHE))
	{
		$whereConds = array();
		
		if ($gallery)
		{
			$whereConds[] = "element LIKE '".mysql_escape_string($gallery)."/%'";
		}
		
		if (!$searchGals)
		{
			$whereConds[] = "etype='f'";
		}
		
		if ($extensions)
		{
			$extIn = array();
			
			foreach ($extensions as $ext)
			{
				if (!$ext) continue;
				$extIn[] = "'".strtolower(mysql_escape_string($ext))."'";
			}
			
			$whereConds[] = "LCASE(extension) IN (".implode(', ', $extIn).")";
		}
		
		$likeStr = $searchStr;
		if (substr($likeStr, -1) != '*') $likeStr .= "*" ;
		if (substr($likeStr, 0, 1) != '*') $likeStr = "*$likeStr";
		$likeStr = str_replace('_', "\\_", $likeStr);
		$likeStr = str_replace('%', "\\%", $likeStr);
		$likeStr = str_replace('*', '%', $likeStr);
		
		if (in_array('title', $targets))
		{
			$searchConds[] = "title LIKE '".mysql_escape_string($likeStr)."'";
		}
		
		if (in_array('extension', $targets))
		{
			$searchConds[] = "extension LIKE '".mysql_escape_string($likeStr)."'";
		}
		
		$whereConds[] = "(".implode(" OR ", $searchConds).")";
		$whereCondStr = "(".implode(" AND ", $whereConds).")";
	
		$sql = "SELECT element FROM ".AUTOGAL_DIRCACHETABLE." WHERE $whereCondStr ORDER BY title ASC";
		
		$sth = mysql_query($sql);
		
		if (!$sth)
		{
			die("SQL Query Error [$sql]: ".mysql_error());
		}
		
		while ($row = mysql_fetch_array($sth, MYSQL_ASSOC))
		{
			$element = $row['element'];
			$mediaObj = new AutoGal_CMediaObj($element);
			
			$target = 'title';
			if (in_array($target, $targets))
			{
				if (preg_match("/($searchRegex)/i", $mediaObj->Title(), $matchList)) {$mediaObj->SearchMatch($target, $matchList[1]); $matches[] = $mediaObj; continue;}
			}
			
			$target = 'extension';
			if (in_array($target, $targets))
			{
				if (preg_match("/($searchRegex)/i", $mediaObj->Extension(), $matchList)) {$mediaObj->SearchMatch($target, $matchList[1]); $matches[] = $mediaObj; continue;}
			}
		}
	}
	else
	{
		require_once(AUTOGAL_MEDIALISTCLASS);
		$mediaList = new AutoGal_CMediaList($gallery, array('recurse' => 1, 'usecache' => AUTOGAL_ENABLEDBCACHE));
		while ($mediaObj = $mediaList->NextElement())
		{
			if ($mediaObj->IsGallery())
			{
				if (!$searchGals) continue;
			}
			else 
			{
				if (!in_array($mediaObj->Extension(), $extensions)) continue;
			}
			
			$target = 'title';
			if (in_array($target, $targets))
			{
				if (preg_match("/($searchRegex)/i", $mediaObj->Title(), $matchList)) {$mediaObj->SearchMatch($target, $matchList[1]); $matches[] = $mediaObj; continue;}
			}
			
			$target = 'extension';
			if (in_array($target, $targets))
			{
				if (preg_match("/($searchRegex)/i", $mediaObj->Extension(), $matchList)) {$mediaObj->SearchMatch($target, $matchList[1]); $matches[] = $mediaObj; continue;}
			}
		
			$target = 'description';
			if (in_array($target, $targets))
			{
				if (preg_match("/($searchRegex)/im", $mediaObj->DescriptionStripBB(), $matchList)) {$mediaObj->SearchMatch($target, $matchList[1]); $matches[] = $mediaObj; continue;}
			}
			
			$target = 'submitbyusername';
			if (in_array($target, $targets))
			{
				if (preg_match("/($searchRegex)/i", $mediaObj->SubmitByUsername(), $matchList)) {$mediaObj->SearchMatch($target, $matchList[1]); $matches[] = $mediaObj; continue;}
			}
		}
		
		usort($matches, "AutoGal_CmpMediaObjs");
	}
	
	if (AUTOGAL_CHECKSEARCHVCLASS)
	{
		foreach ($matches as $matchI => $match)
		{
			if ($match->IsGallery())
			{
				$gallObj = $match;
			}
			else
			{
				$gallObj = $match->GalleryMediaObj();
			}
			
			$gallEle = $gallObj->Element();
			
			if (!isset($vClasses[$gallEle]))
			{
				$vClasses[$gallEle] = $gallObj->CheckUserPriv('view');
				if ($DEBUG) print "VCLASS $gallEle GET = ".$vClasses[$gallEle]."<br />";
			}
			else
			{
				if ($DEBUG) print "VCLASS $gallEle CACHED = ".$vClasses[$gallEle]."<br />";
			}
			
			if (!$vClasses[$gallEle])
			{
				unset($matches[$matchI]);
			}
		}
	}
	
	return $matches;
}

function AutoGal_LoadUserClassCache()
{
	if (!AUTOGAL_ENABLEDBCACHE) return;
	if (!AUTOGAL_USERCLASSCACHE) return;
	
	$sql = "SELECT element, ucview, ucupload, ucadmin, ucmcomment, ucgcomment FROM ".AUTOGAL_DIRCACHETABLE." WHERE etype='g'";
	$sth = mysql_query($sql);
	
	$userClasses = array();
	
	if (!$sth)
	{
		die ("SQL ERROR [$sql]: ".mysql_error());
	}
	else
	{
		while ($row = mysql_fetch_array($sth, MYSQL_ASSOC))
		{
			$row = array_change_key_case($row);
			$element = $row['element'];
			$userClasses[$element]['view'] = $row['ucview'];
			$userClasses[$element]['upload'] = $row['ucupload'];
			$userClasses[$element]['admin'] = $row['ucadmin'];
			$userClasses[$element]['mcomment'] = $row['ucmcomment'];
			$userClasses[$element]['gccomment'] = $row['ucgcomment'];
		}
	}
	
	return $userClasses;
}

?>
