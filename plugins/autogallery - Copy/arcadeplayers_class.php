<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org).
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        27/01/2007
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

class AutoGal_ArcadePlayers
{
	var $m_lastError;
	var $m_xmlParser;
	var $m_tagTree = array();
	var $m_DEBUG = 0;
	var $m_players;
	var $m_currPlayer;
	var $m_rawXML;
	var $m_filename;
	
	function AutoGal_ArcadePlayers($xmlFilename)
	{
		$this->m_filename = $xmlFilename;
	}
	
	function PlayerList()
	{
		return $this->m_players;
	}
	
	function UpdatePlayer($userID, $username, $element)
	{
		$playerIndex = $this->GetPlayerIndex($userID);
		
		$player['userid'] = $userID;
		$player['username'] = $username;
		$player['element'] = $element;
		$player['starttime'] = time();
		
		if ($playerIndex < 0)
		{
			$this->m_players[] = $player;
		}
		else
		{
			$this->m_players[$playerIndex] = $player;
		}
	}
	
	function DeletePlayer($userID)
	{
		$playerIndex = $this->GetPlayerIndex($userID);
		if ($playerIndex < 0) return 0;
		unset($this->m_players[$playerIndex]);
		return 1;
	}
	
	function DeleteOldPlayers($maxHours)
	{
		$currTime = time();
		$newPlayers = array();
		foreach ($this->m_players as $player)
		{
			if (($player['starttime'] + ($maxHours * 60 * 60)) > $currTime)
			{
				$newPlayers[] = $player;
			}
		}
		
		$this->m_players = $newPlayers;
	}
		
	function PlayerElement($userID)
	{
		$playerIndex = $this->GetPlayerIndex($userID);
		if ($playerIndex < 0) return '';
		return $this->m_players[$playerIndex]['element'];
	}
		
	function Player($userID)
	{
		$playerIndex = $this->GetPlayerIndex($userID);
		if ($playerIndex < 0) return;
		return $this->m_players[$playerIndex];
	}
	
	function GetPlayerIndex($userID)
	{
		for ($playerIndex = 0; $playerIndex < count($this->m_players); $playerIndex ++)
		{
			if ($this->m_players[$playerIndex]['userid'] == $userID)
			{
				return $playerIndex;
			}
		}
		
		return -1;
	}
	
	function LastError()
	{
		return $this->m_lastError;
	}
	
	function Open()
	{
		// CHECK IF XML PARSER EXISTS
		if(!function_exists('xml_parser_create'))
		{
			$this->m_lastError = AUTOGAL_LANG_METACLASS_L1;
			if ($this->m_DEBUG) print AUTOGAL_LANG_METACLASS_L2.$this->m_lastError."<br />\n";
			return false;
		}
		
		// READ FILE IN
		$HANDLE = fopen($this->m_filename, "r");
		
		if (!$HANDLE)
		{
			$this->m_lastError = str_replace('[FILE]', $this->m_filename, AUTOGAL_LANG_METACLASS_L3);
			if ($this->m_DEBUG) print AUTOGAL_LANG_METACLASS_L2.$this->m_lastError."<br />\n";
			return false;
		}
		
		$this->m_rawXML = fread($HANDLE, filesize($this->m_filename));
		
		if (!$this->m_rawXML)
		{
			$this->m_lastError = str_replace('[FILE]', $this->m_filename, AUTOGAL_LANG_METACLASS_L4);
			if ($this->m_DEBUG) print AUTOGAL_LANG_METACLASS_L2.$this->m_lastError."<br />\n";
			return false;
		}
		
		fclose($HANDLE);
		
		// PARSE THE FUCKA
		return $this->ParseXML();
	}
	
	function ParseXML()
	{
		$this->m_xmlParser = xml_parser_create('');
		xml_set_object($this->m_xmlParser, $this);
		xml_set_element_handler($this->m_xmlParser, 'StartElement', 'EndElement');
		xml_set_character_data_handler($this->m_xmlParser, 'CharacterData'); 
		xml_parse($this->m_xmlParser, $this->m_rawXML);
		
		xml_parser_free($this->m_xmlParser);
	
		return true;
	}
	
	function StartElement($parser, $element, &$attrs)
	{
		$element = strtolower($element);
		$currTag = $this->m_tagTree[count($this->m_tagTree) - 1];
		
		if ($this->m_DEBUG) print "START $currTag $element<br>";
		
		if ($currTag == 'currentplayers')
		{
			if ($element == 'player')
			{
				if ($this->m_DEBUG) print "START THE PLAYER<br />";
				$this->m_currPlayer = array();
				$this->m_readPlayer = 1;
			}
		}
		
		$this->m_tagTree[] = $element;
	}
		
	function EndElement($parser, $element)
	{
		$element = strtolower($element);
		$currTag = $this->m_tagTree[count($this->m_tagTree) - 1];
		
		if ($this->m_DEBUG) print "END $currTag $element<br>";
		
		if ($currTag == 'player')
		{
			if ($element == 'player')
			{
				$this->m_currPlayer['starttime'] = $this->DecodeDate($this->m_currPlayer['starttime']);
				$this->m_players[] = $this->m_currPlayer;
				$this->m_readPlayer = 0;
			}
		}
		
		array_pop($this->m_tagTree);
	}

	function CharacterData ($parser, $data)
	{
		$currTag = $this->m_tagTree[count($this->m_tagTree) - 1];
		
		$tagPath = implode("->", $this->m_tagTree); 
		
		if ($this->m_DEBUG) print "DATA ".($this->m_readPlayer ? "READ" : "SKIP")." [$tagPath]=\"$data\"<br />";
		
		if ($this->m_readPlayer)
		{
			if ($currTag == 'username')
			{
				$this->m_currPlayer[$currTag] .= $data;
			}
			elseif ($currTag == 'userid')
			{
				$this->m_currPlayer[$currTag] .= $data;
			}
			elseif ($currTag == 'element')
			{
				$this->m_currPlayer[$currTag] .= $data;
			}
			elseif ($currTag == 'starttime')
			{
				$this->m_currPlayer[$currTag] .= $data;
			}
			else
			{
				if ($this->m_DEBUG) print "INVALID TAG $currTag<br />";
			}
		}
		else
		{
			if ($this->m_DEBUG) print "NO READ<br />";
		}
	}
	
	function Save($perms=666)
	{
		// BUILD XML META DATA
		$xmlData .=
		"<?xml version=\"1.0\" encoding=\"".AUTOGAL_CHARSET."\"?>\n\n".
		"<!-- Auto Gallery (www.cerebralsynergy.com) XML File -->\n\n".
		"<currentplayers>\n";
				
		foreach ($this->m_players as $player)
		{
			if (($player['username'])&&($player['userid'])&&($player['element'])&&($player['starttime']))
			{
				$xmlData .= 
				"\t<player>\n".
				"\t\t<username>".htmlspecialchars($player['username'])."</username>\n".
				"\t\t<userid>".htmlspecialchars($player['userid'])."</userid>\n".
				"\t\t<element>".htmlspecialchars($player['element'])."</element>\n".
				"\t\t<starttime>".htmlspecialchars(strftime("%H:%M:%S %d-%b-%Y", $player['starttime']))."</starttime>\n".
				"\t</player>\n";
			}
		}
		
		$xmlData .= "</currentplayers>\n";
		
		$this->m_rawXML = $xmlData;
			
		// TOUCH AND CHMOD XML FILE IF NEW
		if (!file_exists($this->m_filename))
		{
			touch($this->m_filename);
			chmod($this->m_filename, octdec($perms));
		}
		
		// WRITE XML TO FILE
		$HANDLE = fopen($this->m_filename, "w+");
				
		if (!$HANDLE)
		{
			$this->m_lastError = str_replace('[FILE]', $this->m_filename, AUTOGAL_LANG_METACLASS_L5);
			if ($this->m_DEBUG) print "ERROR: ".$this->m_lastError."<br />\n";
			flock($HANDLE, LOCK_UN);
			return false;
		}
		
		flock($HANDLE, LOCK_EX);
		if (fwrite($HANDLE, $xmlData) === false)
		{
			$this->m_lastError = str_replace('[FILE]', $this->m_filename, AUTOGAL_LANG_METACLASS_L6);
			if ($this->m_DEBUG) print "ERROR: ".$this->m_lastError."<br />\n";
			flock($HANDLE, LOCK_UN);
			return false;
		}
		
		flock($HANDLE, LOCK_UN);
		fclose($HANDLE);
		
		if (!file_exists($this->m_filename))
		{
			$this->m_lastError = str_replace('[FILE]', $this->m_filename, AUTOGAL_LANG_METACLASS_L8);
			return false;
		}
		
		return true;
	}
	
	// WORK OUT EPOCH, CAN'T USE strptime 'COS IT'S PHP5 (NOONE SEEMS TO HAVE IT)
	function DecodeDate($dateStr)
	{
		if (preg_match("/^\s*(\d+)\:(\d+)\:(\d+)\s+(\d+)\-(\w+)\-(\d+)\s*$/", $dateStr, $dBits))
		{
			switch (strtolower($dBits[5]))
			{
				case 'jan': $monD = 1; break;
				case 'feb': $monD = 2; break;
				case 'mar': $monD = 3; break;
				case 'apr': $monD = 4; break;
				case 'may': $monD = 5; break;
				case 'jun': $monD = 6; break;
				case 'jul': $monD = 7; break;
				case 'aug': $monD = 8; break;
				case 'sep': $monD = 9; break;
				case 'oct': $monD = 10; break;
				case 'nov': $monD = 11; break;
				case 'dec': $monD = 12; break;
			}
			
			return mktime($dBits[1], $dBits[2], $dBits[3], $monD, $dBits[4], $dBits[6]);
		}
		else
		{
			return -1;
		}
	}
}


?>