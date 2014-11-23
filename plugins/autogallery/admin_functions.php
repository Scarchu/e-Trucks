<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        11/04/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

#//
require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");

if (file_exists(AUTOGAL_LANGDIR."/".e_LANGUAGE."_Admin.php"))
{
	require_once(AUTOGAL_LANGDIR."/".e_LANGUAGE."_Admin.php");
}
else
{
	require_once(AUTOGAL_LANGDIR."/English_Admin.php");
}

function AutoGal_GenerateCacheMenu($galObj, $incSubGals)
{
	global $pref;
	if (!$pref['autogal_enabledbcache']) return;
	global $ns;
	
	if (is_array($gallery))
	{
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
	
	$text = "<iframe src=\"".AUTOGAL_ADMINACTION."?op=regencache".($incSubGals ? 'r' : '')."&ele=".rawurlencode(implode('|', $galleries))."\" width='100%' frameborder='1' scrolling='yes' height='".AUTOGAL_ADMINACTIONBOXHEIGHT."'></iframe>";
	$ns->tablerender(AUTOGAL_LANG_ADMIN_FUNCTIONS_L138, $text);
}

function AutoGal_GenerateCache($galObj, $incSubGals, $printLog=false)
{
	global $pref;
	if (!$pref['autogal_enabledbcache']) return array(AUTOGAL_LANG_ADMIN_CACHE_4);
	require_once(AUTOGAL_MEDIALISTCLASS);
	
	if (!is_object($galObj))
	{
		$galObj = new AutoGal_CMediaObj($galObj);
	}
	
	$msg = str_replace("[GALLERY]", $galObj->PathTitle('/'), AUTOGAL_LANG_ADMIN_FUNCTIONS_L135);
	$msgs[] = $msg;
	if ($printLog) print "$msg\n";
	
	$galObj->ChildMediaObjs();
	
	if ($incSubGals)
	{
		$galList = new AutoGal_CMediaList($galObj->Element(), array('sortorder' => 'name', 'recurse' => $incSubGals, 'usecache' => $pref['autogal_enabledbcache']));

		while ($mediaObj = $galList->NextElement())
		{
			if (($mediaObj->IsGallery())&&($incSubGals))
			{
				$mediaObj->ChildMediaObjs();
				$msg = str_replace("[GALLERY]", $mediaObj->PathTitle('/'), AUTOGAL_LANG_ADMIN_FUNCTIONS_L135);
				$msgs[] = $msg;
				if ($printLog) print "$msg\n";
			}
		}
	}
	
	return $msgs;
}

function AutoGal_RegenLatestCommentsMenu()
{
	global $ns, $pref;
	if (!$pref['autogal_metacomments']) return;
	if (!$pref['autogal_latestcomms']) return;
	$text = "<iframe src=\"".AUTOGAL_ADMINACTION."?op=regenlatestcomms\" width='100%' frameborder='1' scrolling='yes' height='".AUTOGAL_ADMINACTIONBOXHEIGHT."'></iframe>";
	$ns->tablerender(AUTOGAL_LANG_ADMIN_FUNCTIONS_L139, $text);
}

function AutoGal_RegenLatestComments(&$error, $printMsgs=false)
{
	global $pref;
	if (!$pref['autogal_metacomments'])
	{
		$error = "Comments not enabled";
		if ($printMsgs) print "*** $error\n";
		return false;
	}
	
	if (!$pref['autogal_latestcomms'])
	{
		$error = "Latest comments not enabled";
		if ($printMsgs) print "*** $error\n";
		return false;
	}
	
	require_once(AUTOGAL_LTSTCOMSHANDLER);
	require_once(AUTOGAL_MEDIALISTCLASS);
	
	$lComms = new AutoGal_LatestComms($pref['autogal_maxlatestcomms']);
	$galList = new AutoGal_CMediaList('', array('sortorder' => 'name', 'recurse' => 1, 'usecache' => $pref['autogal_enabledbcache']));
	
	while ($mediaObj = $galList->NextElement())
	{
		if ($printMsgs) print str_replace("[ELEMENT]", $mediaObj->Element(), AUTOGAL_LANG_ADMIN_FUNCTIONS_L140)."\n";
		
		$comments = $mediaObj->Comments();
		if (!$comments) continue;
		
		if ($printMsgs) print str_replace("[ELEMENT]", $mediaObj->Element(), str_replace("[NUM]", count($comments), AUTOGAL_LANG_ADMIN_FUNCTIONS_L141))."\n";
		
		foreach ($comments as $comment)
		{
			$comment['element'] = $mediaObj->Element();
			$lComms->AddCommentArray($comment);
		}
	}
	
	if ($printMsgs) print AUTOGAL_LANG_ADMIN_FUNCTIONS_L142."\n";
	if (!$lComms->SaveFile(AUTOGAL_LATESTCOMMSXML, AUTOGAL_PERMSCFGXML))
	{
		$error = $lComms->GetLastError();
		if ($printMsgs) print "*** $error\n";
		return false;
	}
	
	if ($printMsgs) print AUTOGAL_LANG_ADMIN_FUNCTIONS_L143."\n";
	return true;
}

function AutoGal_CheckFileUpdates()
{
	global $ns;
	global $pref;
	
	if ($pref['autogal_needfileupdate'])
	{
		$text = "
		<div style='text-align:center'>
		<b><font color='red'>".AUTOGAL_LANG_ADMIN_FILEUPDATE_26."</font></b>".AUTOGAL_LANG_ADMIN_FILEUPDATE_27."<br />
		<br />
		[<a href=\"".AUTOGAL_FILEUPDATE."\">".AUTOGAL_LANG_ADMIN_FILEUPDATE_28."</a>]
		</div>";
		
		$ns->tablerender(AUTOGAL_LANG_ADMIN_FILEUPDATE_1, $text);
	}
}

function AutoGal_DBTableExists($type)
{
	if (!AutoGal_IsMainAdmin()) return;
	
	$struct = AutoGal_DBTableStructure($type);
	$table = $struct['name'];
	if (!$table) return 0;
	
	$sql = "SELECT * FROM $table LIMIT 0,1";
	$sth = mysql_query($sql);
	
	if ((!$sth)||(mysql_error())) return 0;
	
	return 2;
}

function AutoGal_CheckTableDefs($tableTypes=array('cache'), $checkPref=true)
{
	if (!AutoGal_IsMainAdmin()) return;
	
	global $ns;
	
	if (!is_array($tableTypes))
	{
		$tableTypes = array($tableTypes);
	}
	
	$msgs = '';
	foreach ($tableTypes as $type)
	{
		if ((($type == 'cache')&&(!$pref['autogal_enabledbcache']))&&($checkPref)) continue;
	
		$struct = AutoGal_DBTableStructure($type);
		if (!$struct) continue;
		
		$table = $struct['name'];
		
		$sql = "DESCRIBE $table";
		$sth = mysql_query($sql);
		
		$error = '';
		if (!$sth)
		{
			$error = AUTOGAL_LANG_ADMIN_DB_10;
		}
		else
		{
			while ($row = mysql_fetch_array($sth, MYSQL_ASSOC))
			{
				$row = array_change_key_case($row);
				if (strtolower($row['null']) == 'no') $row['null'] = 0;
				$dbDefs[] = $row;
			}
			
			for ($fieldI = 0; $fieldI < count($struct['fields']); $fieldI ++)
			{
				$authField = $struct['fields'][$fieldI];
				$dbField = $dbDefs[$fieldI];
				$fieldName = $authField['field'];
				
				if (!isset($dbField))
				{
					$error = AUTOGAL_LANG_ADMIN_DB_11;
					$error = str_replace("[FIELD]", $fieldName);
					$error = str_replace("[INDEX]", $fieldI);
					break;
				}
				
				$dbField = $dbDefs[$fieldI];
				
				if ($dbField['field'] != $authField['field'])
				{
					$error = AUTOGAL_LANG_ADMIN_DB_9;
					$error = str_replace("[PARAM]", 'name', $error);
					$error = str_replace("[FIELD]", $fieldName, $error);
					$error = str_replace("[BADPARAMVALUE]", $dbField['field'], $error);
					$error = str_replace("[GOODPARAMVALUE]", $authField['field'], $error);

					break;
				}
				
				if (strtolower($dbField['type']) != strtolower($authField['type']))
				{
					$error = AUTOGAL_LANG_ADMIN_DB_9;
					$error = str_replace("[PARAM]", 'type', $error);
					$error = str_replace("[FIELD]", $fieldName);
					$error = str_replace("[BADPARAMVALUE]", $dbField['type'], $error);
					$error = str_replace("[GOODPARAMVALUE]", $authField['type'], $error);
					break;
				}
				
				if (strtolower($dbField['default']) != strtolower($authField['default']))
				{
					$error = AUTOGAL_LANG_ADMIN_DB_9;
					$error = str_replace("[PARAM]", 'default', $error);
					$error = str_replace("[FIELD]", $fieldName, $error);
					$error = str_replace("[BADPARAMVALUE]", $dbField['default'], $error);
					$error = str_replace("[GOODPARAMVALUE]", $authField['default'], $error);
					break;
				}
				
				if (($dbField['null'])xor($authField['null']))
				{
					$error = AUTOGAL_LANG_ADMIN_DB_9;
					$error = str_replace("[PARAM]", 'null', $error);
					$error = str_replace("[FIELD]", $fieldName, $error);
					$error = str_replace("[BADPARAMVALUE]", ($dbField['null'] ? 1 : 0), $error);
					$error = str_replace("[GOODPARAMVALUE]", ($authField['null'] ? 1 : 0), $error);
					break;
				}
			}
		}
		
		if (!$error) continue;
		
		$msg = AUTOGAL_LANG_ADMIN_DB_2;
		$msg = str_replace("[TABLE]", $table, $msg);
		$msg = str_replace("[ERROR]", $error, $msg);
		$msg = "<b><span style='color:red'>".AUTOGAL_LANG_ADMIN_DB_4."</span></b>$msg";
		$msg .= " [<a href=\"".AUTOGAL_DBUPDATE."?table=$type\">".AUTOGAL_LANG_ADMIN_DB_3."</a>]";
		
		$msgs .= "$msg<br />";
	}
	
	if ($msgs)
	{
		$ns->tablerender(AUTOGAL_LANG_ADMIN_DB_1, $msgs);
		return 0;
	}
	
	return 1;
}

function AutoGal_CreateDBTable($type, $checkPrefs=trye)
{
	global $pref;
	if (!AutoGal_IsMainAdmin()) return;
	if (($checkPrefs)&&($type == 'cache')&&(!$pref['autogal_enabledbcache'])) return;
	
	$struct = AutoGal_DBTableStructure($type);
	$table = $struct['name'];
	
	foreach ($struct['fields'] as $field)
	{
		$sqlFieldDefs[] = $field['field'].
			' '.strtoupper($field['type']).
			(!$field['null'] ? " NOT NULL" : '').
			(isset($field['default']) ? " DEFAULT '".mysql_escape_string($field['default'])."'" : '').
			(isset($field['extra']) ? ' '.$field['extra'] : '');
		
	}
	
	$sql = "CREATE TABLE $table (\n\t".implode(",\n\t", $sqlFieldDefs)."\n)";
	
	if (!mysql_unbuffered_query($sql))
	{
		die("SQL ERROR: $sql<br />".mysql_error());
	}
	
	return str_replace("[TABLE]", $table, AUTOGAL_LANG_ADMIN_DB_6);
}

function AutoGal_DropDBTable($type)
{
	global $pref;
	if (!AutoGal_IsMainAdmin()) return;
	if (($type == 'cache')&&(!$pref['autogal_enabledbcache'])) return;
	
	$struct = AutoGal_DBTableStructure($type);
	if (!$struct) return;
	
	$table = $struct['name'];
	if (!$table) return;
	
	$sql = "DROP TABLE IF EXISTS $table";
	
	if (!mysql_unbuffered_query($sql))
	{
		die("SQL ERROR: $sql<br />".mysql_error());
	}
	
	return str_replace("[TABLE]", $table, AUTOGAL_LANG_ADMIN_DB_5);
}

function AutoGal_DBTableStructure($type)
{
	if ($type == 'cache')
	{
		$struct = array
		(
			'name' => AUTOGAL_DIRCACHETABLE,
			'fields' => array
			(
				array('field' => 'element',    'type' => 'text',         'null' => 0),
				array('field' => 'parent',     'type' => 'text',         'null' => 0),
				array('field' => 'etype',      'type' => 'char(1)',      'null' => 0),
				array('field' => 'extension',  'type' => 'varchar(4)',   'null' => 1),
				array('field' => 'title',      'type' => 'varchar(200)', 'null' => 0),
				array('field' => 'thumbnail',  'type' => 'varchar(200)', 'null' => 1),
				array('field' => 'updated',    'type' => 'datetime',     'null' => 0, 'default' => '0000-00-00 00:00:00'),
				array('field' => 'ctime',      'type' => 'datetime',     'null' => 1),
				array('field' => 'mtime',      'type' => 'datetime',     'null' => 1),
				array('field' => 'ucview',     'type' => 'smallint(6)',  'null' => 1, 'default' => AUTOGAL_DEFAULTVIEWUC),
				array('field' => 'ucupload',   'type' => 'smallint(6)',  'null' => 1, 'default' => AUTOGAL_DEFAULTUPLOADUC),
				array('field' => 'ucadmin',    'type' => 'smallint(6)',  'null' => 1, 'default' => AUTOGAL_DEFAULTADMINUC),
				array('field' => 'ucmcomment', 'type' => 'smallint(6)',  'null' => 1, 'default' => AUTOGAL_DEFAULTMCOMMENTUC),
				array('field' => 'ucgcomment', 'type' => 'smallint(6)',  'null' => 1, 'default' => AUTOGAL_DEFAULTMCOMMENTUC),
			)
		);
	}
	
	return $struct;
}

function AutoGal_ShowAdmin(&$mediaObj, $ns)
{
	global $g_startFile, $pref;
	global $g_startGallery;
	global $g_absPath;
	
	if (!$mediaObj->CheckUserPriv('adminmenu')) return;
	
	$element = $mediaObj->Element();
	
	$filePerms = fileperms($mediaObj->AbsPath());
	$filePermsNum = substr(sprintf('%o', $filePerms), -3);
	$filePermsStr = AutoGal_FormatFilePerms($filePerms) . " ($filePermsNum)";
	
	if (!$pref['autogal_chmodwarnoff'])
	{
		if (($mediaObj->IsGallery())&&($filePermsNum != AUTOGAL_PERMSGALDIR))
		{
			$filePerms .= " <font color='red'>".AUTOGAL_LANG_ADMIN_FUNCTIONS_L1." ".AUTOGAL_PERMSGALDIR."!</font>";
		}
	}
	
	if ($uploadPerms = IsBadUploadDirPerms()) $ns->tablerender(AUTOGAL_LANG_ADMIN_FUNCTIONS_L2, $uploadPerms); 
	
	$viewXML = "[<a href=\"".AUTOGAL_SHOWXML."?file=".rawurlencode($element)."\">".AUTOGAL_LANG_ADMIN_FUNCTIONS_L118."</a>]";
	
	$actions = array();
	
	if ($mediaObj->CheckUserPriv('rename'))          $actions[] = array('id' => 'rename',           'title' => AUTOGAL_LANG_MENU_L7);
	if ($mediaObj->CheckUserPriv('delete'))          $actions[] = array('id' => 'delete',           'title' => AUTOGAL_LANG_MENU_L8);
	if ($mediaObj->CheckUserPriv('move'))            $actions[] = array('id' => 'move',             'title' => AUTOGAL_LANG_MENU_L9);
	if ($mediaObj->CheckUserPriv('watermark'))       $actions[] = array('id' => 'watermark',        'title' => AUTOGAL_LANG_MENU_L17);
	if ($mediaObj->CheckUserPriv('rotate'))          $actions[] = array('id' => 'rotate',           'title' => AUTOGAL_LANG_MENU_L18);
	if ($mediaObj->CheckUserPriv('setfilethumb'))    $actions[] = array('id' => 'uploadthumb',      'title' => AUTOGAL_LANG_MENU_L19);
	if ($mediaObj->CheckUserPriv('setviewsize'))     $actions[] = array('id' => 'setviewsize',      'title' => AUTOGAL_LANG_MENU_L21);
	if ($mediaObj->CheckUserPriv('clearmeta'))       $actions[] = array('id' => 'clearmeta',        'title' => AUTOGAL_LANG_MENU_L22);
	if ($mediaObj->CheckUserPriv('editdescription')) $actions[] = array('id' => 'editdescription',  'title' => AUTOGAL_LANG_MENU_L11);
	
	if (($pref['autogal_wmarkauto'])&&($mediaObj->IsGallery()))
	{
		if ($mediaObj->CheckUserPriv('autowatermark')) $actions[] = array('id' => 'autowatermark', 'title' => AUTOGAL_LANG_MENU_L20);
	}
	
	if (($mediaObj->IsGallery())||($mediaObj->FileType() == 'image'))
	{
		if ($mediaObj->CheckUserPriv('setgallerythumb')) $actions[] = array('id' => 'setgallerythumb', 'title' => AUTOGAL_LANG_MENU_L12);
	}
	
	if ($mediaObj->IsGallery())
	{
		if ($mediaObj->CheckUserPriv('editaccess'))    $actions[] = array('id' => 'editaccess', 'title' => AUTOGAL_LANG_MENU_L10);
		if ($mediaObj->CheckUserPriv('creategallery')) $actions[] = array('id' => 'creategallery',  'title' => AUTOGAL_LANG_MENU_L13);
	}
	
	if ($pref['autogal_enabledbcache'])
	{
		if ($mediaObj->CheckUserPriv('clearcache')) $actions[] = array('id' => 'clearcache',        'title' => AUTOGAL_LANG_MENU_L23);
		if ($mediaObj->CheckUserPriv('clearcache')) $actions[] = array('id' => 'clearcachesubgals', 'title' => AUTOGAL_LANG_MENU_L24);
		if ($mediaObj->CheckUserPriv('regencache')) $actions[] = array('id' => 'regencache',        'title' => AUTOGAL_LANG_MENU_L25);
		if ($mediaObj->CheckUserPriv('regencache')) $actions[] = array('id' => 'regencachesubgals', 'title' => AUTOGAL_LANG_MENU_L26);
	}
	
	usort($actions, "AutoGal_CmpAdminActions");
			
	$text = "
	<div style='text-align:center'>
	<table class='border'>
	<tr>
        <td class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_FUNCTIONS_L3."</b></td>
        <td class='forumheader3' colspan='2'>$filePermsStr</td>         
    </tr>".
	($mediaObj->CheckUserPriv('viewxml') ? "
	<tr>
		<td class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_FUNCTIONS_L36."</b></td>
        <td class='forumheader3' colspan='2'>".
			(!$mediaObj->IsError() ? AUTOGAL_LANG_ADMIN_FUNCTIONS_L34." $viewXML" : ($mediaObj->LastError() ? "<font color='red'>".AUTOGAL_LANG_ADMIN_FUNCTIONS_L37."</font> ".$mediaObj->LastError()." $viewXML" : AUTOGAL_LANG_ADMIN_FUNCTIONS_L38))."
		</td>         
	</tr>" : '').
	($mediaObj->IsGallery() ? "
	<tr>
		<td class='forumheader3'><b>".AUTOGAL_LANG_MENU_L1."</b></td>
		<td class='forumheader3'>
			<select name='ag_adminselection' id='ag_adminselection' class='tbox'>
			<option value='current'>".str_replace('[TYPE]', $mediaObj->TypeTitle(), str_replace('[TITLE]',  $mediaObj->Title(), AUTOGAL_LANG_MENU_L2))."</option>
			<option value='checked'>".AUTOGAL_LANG_MENU_L3."</option>
			<option value='unchecked'>".AUTOGAL_LANG_MENU_L4."</option>
			<option value='all'>".AUTOGAL_LANG_MENU_L5."</option>
			<option value='galleries'>".AUTOGAL_LANG_MENU_L15."</option>
			<option value='files'>".AUTOGAL_LANG_MENU_L16."</option>
			</select>
		</td>
	</tr>" : '')."
	<tr>
		<td class='forumheader3'><b>".AUTOGAL_LANG_MENU_L6."</b></td>
		<td class='forumheader3'>
			<select name='ag_adminaction' id='ag_adminaction' class='tbox'>";
			foreach ($actions as $action)
			{
				$text .= "<option value='".$action['id']."'>".$action['title']."</option>\n";
			}
			$text .= "
			</select>
		</td>
	</tr>
	<tr>
	<td class='forumheader2' colspan='2' style='text-align:center'>
		<input type='submit' class='button' name='ag_admingo' value='".AUTOGAL_LANG_MENU_L14."' /></td>
	</tr>
	".($mediaObj->IsFile() ? "<input type='hidden' name='ag_adminselection' value=\"current\" />" : '')."
	<input type='hidden' name='show' value=\"".AutoGal_HtmlVar($element)."\" />
	<input type='hidden' name='start' value=\"".AutoGal_HtmlVar($g_startFile)."\" />
	<input type='hidden' name='startgal' value=\"".AutoGal_HtmlVar($g_startGallery)."\" />
	</table>
	</div>"; 
	
	$ns -> tablerender(AUTOGAL_LANG_L4, $text);
}

function AutoGal_CmpAdminActions($a, $b)
{
   $cmpA = strtolower($a['title']);
   $cmpB = strtolower($b['title']);
      
   if ($cmpA == $cmpB) return 0;
   return ($cmpA < $cmpB) ? -1 : 1;
}

/*****************************************************************************************************
 * DELETES A DIRECTORY INCLUDING ALL SUB DIRECTORIES AND FILES - RIPPED FROM PHP.NET
 ****************************************************************************************************/
function AutoGal_DelDir($dirName)
{
    if(empty($dirName))
    {
       return;
    }
	
    if(file_exists($dirName))
    {
        $dir = opendir($dirName);
        while($file = readdir($dir))
        {
            if($file != '.' && $file != '..')
            {
                if(is_dir("$dirName/$file"))
                {
                    AutoGal_DelDir("$dirName/$file");
                }
                else
                {
                    if (!unlink("$dirName/$file")) return(str_replace('[FILE]', "$dirName/$file", AUTOGAL_LANG_ADMIN_FUNCTIONS_L113)."<br />");
                }
            }
        }
        closedir($dir);
        if (!rmdir($dirName)) 
        {
            return(AUTOGAL_LANG_ADMIN_FUNCTIONS_L111."rmdir \"$dirName\"<br />");
        }
    }
    else
    {
       return AUTOGAL_LANG_ADMIN_FUNCTIONS_L112."<br />";
    }
	
    return;
}

function AutoGal_WriteHtaccess($file, $writeBits='security,leech,watermark')
{
	global $pref;
	
	$htaccessData = '';
	
	if (preg_match("/security/i", $writeBits))
	{
		if ($pref['autogal_apacheindexignore'])
		{
			$htaccessData .= "IndexIgnore *\n\n";
		}
	
		if ($pref['autogal_apachedenyexts'])
		{
			$htaccessData .= 
			"<FilesMatch \"(\.xml$|\.htaccess$|\.log$)\">\n".
			"\torder deny,allow\n".
			"\tDeny from all\n".
			"</FilesMatch>\n\n";
		}
	}
	
	if (preg_match("/leech/i", $writeBits))
	{
		if ($pref['autogal_apacheleechprotect'])
		{
			$serverName = $_SERVER['HTTP_HOST'];
			$serverName = preg_replace("/^http\:\/\//", "", $serverName);
			$serverName = preg_replace("/^www\./", "", $serverName);
			
			$htaccessData .= 
			"RewriteEngine On\n".
			"RewriteCond %{HTTP_REFERER} !^$\n".
			"RewriteCond %{HTTP_REFERER} !^http://(www.)?".preg_quote($serverName).".*$ [NC]\n";
			
			$allowedSites = explode("\n", $pref['autogal_apacheallowedsites']);
			
			foreach ($allowedSites as $site)
			{
				$site = preg_replace("/^http\:\/\//", "", $site);
				$site = preg_replace("/^www\./", "", $site);
				
				if ($site)
				{
					$htaccessData .= "RewriteCond %{HTTP_REFERER} !^http://(www.)?".preg_quote($site).".*$ [NC]\n";
				}
			}
			
			$leechImg = $pref['autogal_apacheleechimage'];
			if ($leechImg)
			{
				$htaccessData .= "RewriteRule \.(".AUTOGAL_SUPPORTEDEXTS.")$ $leechImg [NC,R]\n";
			}
			else
			{
				$htaccessData .= "RewriteRule \.(".AUTOGAL_SUPPORTEDEXTS.")$ - [NC,F]\n\n";
			}
		}
	}
	
	if (preg_match("/watermark/i", $writeBits))
	{
		if ($pref['autogal_wmarkauto'])
		{
			$imgExts = explode('|', AUTOGAL_IMAGEEXTS);
			
			foreach ($imgExts as $ext)
			{
				$htaccessData .= "AddHandler watermarked .$ext\n";
			}
			
			$htaccessData .= "\n";
			$htaccessData .= "<FilesMatch \"^".preg_quote(AUTOGAL_THUMBPREFIX)."\">\n";
			foreach ($imgExts as $ext)
			{
				$htaccessData .= "RemoveHandler .$ext\n";
			}
			$htaccessData .= "</FilesMatch>\n";
			
			$htaccessData .= "\n";
			$htaccessData .= "<FilesMatch \"^".preg_quote(AUTOGAL_GALLERYTHUMBFILENAMEAUTOGAL_GALLERYTHUMBFILENAME)."\">\n";
			foreach ($imgExts as $ext)
			{
				$htaccessData .= "RemoveHandler .$ext\n";
			}
			$htaccessData .= "</FilesMatch>\n";
			
			$htaccessData .= "\n";
			$watermarkPath = AutoGal_GetAbsHttpPath(AUTOGAL_WATERMARK);
			$htaccessData .= "Action watermarked $watermarkPath \n";
			
		}
	}
	
	$fh = fopen ($file, 'w+');
	if (!$fh) return 0;
	
	flock($H_HTACCESS, LOCK_EX);
	
	if (strlen($htaccessData) != fwrite($fh, $htaccessData))
	{
		flock($fh, LOCK_UN);
		fclose($fh);
		return 0;
	}
	
	flock($H_HTACCESS, LOCK_UN);
	fclose($fh);
	
	return 1;
}

function AutoGal_WriteCustomBasePaths($httpPath, $absPath, $usingHttps, &$text)
{
	$baseFile = dirname(__FILE__)."/def_basedirs.php";
	
	$usingHttps = strtolower($usingHttps);
	if (!preg_match("/^(always|never|detect)$/", $usingHttps))
	{
		$usingHttps = "detect";
	}
	
	$absPath = preg_replace('/[\\/\\\\]$/', "", $absPath);
	$httpPath = preg_replace('/[\\/\\\\]$/', "", $httpPath);
	if ((!preg_match('/^\//', $httpPath))&&($httpPath)) $httpPath = "/$httpPath";
	
	$text = 
	"<?php\n".
	"define('AUTOGAL_CUSTOMHTTPPATH', \"$httpPath\");\n".
	"define('AUTOGAL_CUSTOMABSPATH', \"$absPath\");\n".
	"define('AUTOGAL_USINGHTTPS', \"$usingHttps\"); /* 'detect', 'never' or 'always' */\n".
	"?>";
	
	$BASEFILE = fopen($baseFile, "w+");
	if (!$BASEFILE) return 0;
	if (!fwrite($BASEFILE, $text)) return 0;
	if (!fclose($BASEFILE)) return 0;
	
	return 1;
}
?>
