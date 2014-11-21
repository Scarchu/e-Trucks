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
require_once(AUTOGAL_MEDIAOBJCLASS);

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

define("AUTOGAL_LATCOMMSRETAINFACTOR", 3);

class AutoGal_LatestComms
{
	var $m_lastError;
	var $m_xmlParser;
	var $m_tagTree = array();
	var $m_DEBUG = 0;
	var $m_comments;
	var $m_currComment;
	var $m_rawXML;
	var $m_readComment = 0;
	var $m_maxComments = 10;
	
	function AutoGal_LatestComms($maxComms)
	{
		if ($maxComms) $this->m_maxComments = $maxComms;
	}
	
	function AddCommentArray($comment)
	{
		$this->m_comments[] = $comment;
		usort($this->m_comments, "AutoGal_CompareCommentsRev");
		$this->m_comments = array_slice($this->m_comments, 0, ($this->m_maxComments * AUTOGAL_LATCOMMSRETAINFACTOR));
	}
	
	function AddComment($element, $authorID, $authorUsername, $date, $text)
	{
		$comment['element'] = $element;
		$comment['authorid'] = $authorID;
		$comment['authorusername'] = $authorUsername;
		$comment['date'] = $date;
		$comment['text'] = $text;
		
		$this->AddCommentArray($comment);
	}
	
	function GetComments()
	{
		$DEBUG = 0;
		
		foreach ($this->m_comments as $comment)
		{
			$element = $comment['element'];
			$mediaObj = new AutoGal_CMediaObj($element);
			if (!$mediaObj->IsValid()) continue;
		
			if ($pref['autogal_checklcommsvclass'])
			{
				if ($mediaObj->IsGallery())
				{
					$gallObj = $mediaObj;
				}
				else
				{
					$gallObj = $mediaObj->GalleryMediaObj();
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
				
				if (!$vClasses[$gallEle]) continue;
			} 
			
			$comments[] = $comment;
			if (count($comments) >= $this->m_maxComments) break;
		}
		
		return $comments;
	}
	
	function GetLastError()
	{
		return $this->m_lastError;
	}
	
	function LoadFile($fileName)
	{
		// CHECK IF XML PARSER EXISTS
		if(!function_exists('xml_parser_create'))
		{
			$this->m_lastError = AUTOGAL_LANG_METACLASS_L1;
			if ($this->m_DEBUG) print AUTOGAL_LANG_METACLASS_L2.$this->m_lastError."<br />\n";
			return false;
		}
		
		// READ FILE IN
		$HANDLE = fopen($fileName, "r");
		
		if (!$HANDLE)
		{
			$this->m_lastError = str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L3);
			if ($this->m_DEBUG) print AUTOGAL_LANG_METACLASS_L2.$this->m_lastError."<br />\n";
			return false;
		}
		
		$this->m_rawXML = fread($HANDLE, filesize($fileName));
		
		if (!$this->m_rawXML)
		{
			$this->m_lastError = str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L4);
			if ($this->m_DEBUG) print AUTOGAL_LANG_METACLASS_L2.$this->m_lastError."<br />\n";
			return false;
		}
		
		fclose($HANDLE);
		
		// PARSE THE FUCKA
		return $this->ParseXML();
	}
	
	function ParseXML($fileName)
	{
		$this->m_xmlParser = xml_parser_create('');
		xml_set_object($this->m_xmlParser, $this);
		xml_set_element_handler($this->m_xmlParser, 'StartElement', 'EndElement');
		xml_set_character_data_handler($this->m_xmlParser, 'CharacterData'); 
		xml_parse($this->m_xmlParser, $this->m_rawXML);
		
		xml_parser_free($this->m_xmlParser);
		usort($this->m_comments, "AutoGal_CompareCommentsRev");
		
		return true;
	}
	
	function StartElement($parser, $element, &$attrs)
	{
		$element = strtolower($element);
		$currTag = $this->m_tagTree[count($this->m_tagTree) - 1];
		
		if ($this->m_DEBUG) print "START $currTag $element<br>";
		
		if ($currTag == 'latestcomments')
		{
			if ($element == 'comment')
			{
				$this->m_currComment = array();
				$this->m_readComment = 1;
			}
		}
		
		$this->m_tagTree[] = $element;
	}
		
	function EndElement($parser, $element)
	{
		$element = strtolower($element);
		$currTag = $this->m_tagTree[count($this->m_tagTree) - 1];
		
		if ($this->m_DEBUG) print "END $currTag $element<br>";
		
		if ($currTag == 'comment')
		{
			if ($element == 'comment')
			{
				$this->m_currComment['date'] = $this->DecodeDate($this->m_currComment['date']);
				
				$this->m_comments[] = $this->m_currComment;
				$this->m_readComment = 0;
			}
		}
		
		array_pop($this->m_tagTree);
	}

	function CharacterData ($parser, $data)
	{
		$currTag = $this->m_tagTree[count($this->m_tagTree) - 1];
		
		$tagPath = implode("->", $this->m_tagTree); 
		
		if ($this->m_DEBUG) print "DATA $tagPath ($data) ".$this->m_readComment."<br />";
		
		if ($this->m_readComment)
		{
			if ($currTag == 'element')
			{
				$this->m_currComment[$currTag] .= $data;
			}
			elseif ($currTag == 'authorid')
			{
				$this->m_currComment[$currTag] .= $data;
			}
			elseif ($currTag == 'authorusername')
			{
				$this->m_currComment[$currTag] .= $data;
			}
			elseif ($currTag == 'date')
			{
				$this->m_currComment[$currTag] .= $data;
			}
			elseif ($currTag == 'text')
			{
				$this->m_currComment[$currTag] .= $data;
			}
			else
			{
				if ($this->m_DEBUG) print "INVALID TAG<br />";
			}
		}
		else
		{
			if ($this->m_DEBUG) print "NO READ<br />";
		}
	}
	
	function SaveFile($fileName, $perms=666)
	{
		// BUILD XML META DATA
		$xmlData .=
		"<?xml version=\"1.0\" encoding=\"".AUTOGAL_CHARSET."\"?>\n\n".
		"<!-- Auto Gallery (www.cerebralsynergy.com) XML File -->\n\n".
		"<latestcomments>\n";
		
		foreach ($this->m_comments as $comment)
		{
			$xmlData .= 
			"\t<comment>\n".
			"\t\t<element>".htmlspecialchars($comment['element'])."</element>\n".
			"\t\t<authorid>".htmlspecialchars($comment['authorid'])."</authorid>\n".
			"\t\t<authorusername>".htmlspecialchars($comment['authorusername'])."</authorusername>\n".
			"\t\t<date>".htmlspecialchars(strftime("%H:%M:%S %d-%b-%Y", $comment['date']))."</date>\n".
			"\t\t<text>".htmlspecialchars($comment['text'])."</text>\n".
			"\t</comment>\n";
		}
		
		$xmlData .= "</latestcomments>\n";
		
		$this->m_rawXML = $xmlData;
			
		// TOUCH AND CHMOD XML FILE IF NEW
		if (!file_exists($fileName))
		{
			touch($fileName);
			chmod($fileName, octdec($perms));
		}
		
		// WRITE XML TO FILE
		$HANDLE = fopen($fileName, "w+");
				
		if (!$HANDLE)
		{
			$this->m_lastError = str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L5);
			if ($this->m_DEBUG) print "ERROR: ".$this->m_lastError."<br />\n";
			flock($HANDLE, LOCK_UN);
			return false;
		}
		
		flock($HANDLE, LOCK_EX);
		if (fwrite($HANDLE, $xmlData) === false)
		{
			$this->m_lastError = str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L6);
			if ($this->m_DEBUG) print "ERROR: ".$this->m_lastError."<br />\n";
			flock($HANDLE, LOCK_UN);
			return false;
		}
		
		flock($HANDLE, LOCK_UN);
		fclose($HANDLE);
		
		if (!file_exists($fileName))
		{
			$this->m_lastError = str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L8);
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

function AutoGal_CompareCommentsRev($a, $b)
{
	if ($a['date'] > $b['date']) return -1;
	if ($a['date'] < $b['date']) return 1;
	return 0;
}

?>