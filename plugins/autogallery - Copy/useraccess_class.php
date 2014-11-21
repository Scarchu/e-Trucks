<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org).
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        18/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
//

if (!defined("AUTOGAL_CHARSET"))
{
	if (defined("CHARSET"))
	{
		define("AUTOGAL_CHARSET", CHARSET);
	}
	else
	{
		define("AUTOGAL_CHARSET", "utf-8");
	}
}

class AutoGal_CUserAccess
{
	var $m_lastError;
	var $m_xmlParser;
	var $m_tagTree = array();
	var $m_DEBUG = 0;
	var $m_priv;
	var $m_userClasses;
	var $m_userAccess;
		
	function AutoGal_CUserAccess()
	{
		$priv['element'] = '';
		$priv['userclass'] = '';
		$priv['userid'] = '';
		$priv['deny'] = '';
		$priv['allow'] = '*';
		$this->m_priv[] = $priv;
	}
	
	function UserClasses($userID=USERID)
	{
		if (!isset($this->m_userClasses[$user]))
		{
			$dbc = new db;
			if ($dbc->db_Select("user", "user_class", "WHERE user_id=$userID", "nowhere"))
			{
				$row = $dbc->db_Fetch();
				$classIDs = $row[0];
			}
			
			$this->m_userClasses[$userID] = explode(',', $classIDs);
		}
		
		return $this->m_userClasses[$userID];
	}
	
	function CheckPriv($element, $name='view', $user=USER)
	{
		$char = $this->NameToChar(strtolower($name));
		if (!$char){die("Invalid priv name '$name'!");}
		
		$privCodes = $this->PrivCodes();
		$allCodesStr = implode('', array_keys($privCodes));
		
		$allow = '';
		$deny = '';
		foreach ($this->m_priv as $priv)
		{
			if (($element == $priv['element'])||(strpos($priv['element'], $element.'/') == 0))
			{
				$privCheck[$priv['element']]['allow'] = ($priv['allow'] == '*' ? $allCodesStr : $priv['allow']);
				$privCheck[$priv['element']]['allow'] = ($priv['deny'] == '*' ? $allCodesStr : $priv['deny']);
			}
		}
	}
	
	function Name2Char($name)
	{
		$privCodes = $this->PrivCodes();
		
		foreach ($privCodes as $code => $privName)
		{
			if ($name == $name) return $code;
		}
	}
	
	function PrivCodes()
	{
		$privCodes['v'] = 'view';
		$privCodes['c'] = 'filecomment';
		$privCodes['C'] = 'gallerycomment';
		$privCodes['r'] = 'rating';
		$privCodes['U'] = 'directupload';
		$privCodes['u'] = 'reviewupload';
		$privCodes['a'] = 'admin';
		$privCodes['e'] = 'editgallery';
		$privCodes['E'] = 'editfile';
		$privCodes['m'] = 'editmeta';
		
		return $privCodes;
	}
	
	function CharToName($char)
	{
		$privCodes = $this->PrivCodes();
		return $privCodes[$char];
	}
}

function AutoGal_CmpUserAccess()
{
	
}

?>