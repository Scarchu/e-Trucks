<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        11/08/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
error_reporting(E_ERROR);

require_once(dirname(__FILE__)."/def.php");
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

class AutoGal_CMediaList
{
	var $m_lastError;
	var $m_printDebugMsgs;
	var $m_printMemoryMsgs;
	
	var $m_galleries;
	var $m_files;
	var $m_gallery;
	var $m_galleryPath;
	var $m_sortOrder;
	var $m_recurse;
	var $m_isCaching;
	
	var $m_stepDH;
	var $m_stepCacheI;
	var $m_stepDirStack;
	var $m_stepGallery;
	var $m_stepStarted;
	
	#####################################################################
	# CONSTRUCTOR
	#####################################################################
	function AutoGal_CMediaList($gallery, $opts=array())
	{
		global $pref;
		$this->m_gallery = $gallery;
		$this->m_sortOrder = (isset($opts['sortorder']) ?  $opts['sortorder'] : 'name');
		$this->m_recurse = (isset($opts['recurse']) ?  $opts['recurse'] : 0);
		$this->m_isCaching = (isset($opts['usecache']) ?  $opts['usecache'] : $pref['autogal_enabledbcache']);
		
		$this->m_printDebugMsgs = 0;
		$this->m_printMemoryMsgs = 0;
		$this->m_stepStarted = 0;
	}
	
	#####################################################################
	# OPTIONS
	#####################################################################
	function OptSortOrder($order)
	{
		if (isset($order)) $this->m_sortOrder = $order;
		return $this->m_sortOrder;
	}
	
	function OptRecurse($enable)
	{
		if (isset($enable)) $this->m_recurse = $enable;
		return $this->m_recurse;
	}
	
	function OptDoFileStat($enable)
	{
		if (isset($enable)) $this->m_doFileStat = $enable;
		return $this->m_doFileStat;
	}
	
	#####################################################################
	# SET/GET FUNCTIONS
	#####################################################################
	function MediaObjects()
	{
		return array('galleries' => $this->Galleries(), 'files' => $this->Files());
	}
	
	function EnableDebugMsgs($enable)
	{
		if (isset($enable)) $this->m_printDebugMsgs = $enable;
		return $this->m_printDebugMsgs;
	}
	
	function EnableMemoryMsgs($enable)
	{
		if (isset($enable)) $this->m_printMemoryMsgs = $enable;
		return $this->m_printMemoryMsgs;
	}
	
	function MediaObjectsFlat()
	{
		return array_merge($this->Galleries(), $this->Files());
	}
	
	function MediaObjectsMerged()
	{
		$mediaObjs = array_merge($this->Galleries(), $this->Files());
		$mediaObjs = AutoGal_CmpMediaObjs($mediaObjs, $this->m_sortOrder);
		return $mediaObjs;
	}
	
	function Files()
	{
		return $this->m_files;
	}
	
	function Galleries()
	{
		return $this->m_galleries;
	}
	
	#####################################################################
	# ERROR HANDLING FUNCTIONS
	#####################################################################
	function LastError()
	{
		return $this->m_lastError;
	}
	
	function IsError()
	{
		return ($this->m_lastError ? 1 : 0);
	}
	
	function ClearError()
	{
		$this->m_lastError = '';
	}
	
	#####################################################################
	# DIR LISTING FUNCTIONS
	#####################################################################
	function ListGallery() 
	{
		if ($this->m_isCaching)	$this->LoadCache($this->m_gallery, $this->m_recurse);
		$eles = $this->GalleryList($this->m_gallery, $this->m_recurse);
		
		$this->m_files = $eles['files'];
		$this->m_galleries = $eles['galleries'];
		
		$this->m_files = AutoGal_SortMediaObjs($this->m_files, $this->m_sortOrder);
		$this->m_galleries = AutoGal_SortMediaObjs($this->m_galleries, $this->m_sortOrder);
	}
	
	function LoadCache($gallery, $recurse)
	{
		if ($recurse)
		{
			$this->DebugMsg("LOADING CACHE FOR $gallery RECURSIVE");
			$sql = "SELECT element, parent, etype, extension, title, thumbnail, UNIX_TIMESTAMP(updated) AS updated, UNIX_TIMESTAMP(ctime) AS ctime, UNIX_TIMESTAMP(mtime) AS mtime FROM ".AUTOGAL_DIRCACHETABLE." WHERE file LIKE '".mysql_escape_string($gallery)."/%'";
		}
		else
		{
			$this->DebugMsg("LOADING CACHE FOR $gallery FLAT");
			$parent = ($gallery ? $gallery : '*');
			$sql = "SELECT element, parent, etype, extension, title, thumbnail, UNIX_TIMESTAMP(updated) AS updated, UNIX_TIMESTAMP(ctime) AS ctime, UNIX_TIMESTAMP(mtime) AS mtime FROM ".AUTOGAL_DIRCACHETABLE." WHERE parent='".mysql_escape_string($parent)."'";
		}
		
		$this->MemoryMsg("$gallery: Loading cache");
		$sth = mysql_query($sql);
			
		while ($row = mysql_fetch_array($sth, MYSQL_ASSOC))
		{
			$element = $row['element'];
			$parent  = ($row['parent'] == '*' ? '' : $row['parent']);
			
			foreach ($row as $field => $value)
			{
				if ($field == 'parent') continue;
				
				$fileInfo[strtolower($field)] = $value;
			}
			
			$this->m_cache[$parent][] = $fileInfo;
		}
		
		$this->DebugMsg("CACHE RETREIVED FOR $gallery: ".count($this->m_cache[$gallery]));
		$this->MemoryMsg("$gallery: Cache loaded");
	}
	
	function DebugMsg($msg)
	{
		if (!$this->m_printDebugMsgs) return;
		print htmlspecialchars($msg)."<br />";
		
		$this->MemoryMsg();
	}
	
	function MemoryMsg($loc)
	{
		if (!$this->m_printMemoryMsgs) return;
		
		$memUsage = AutoGal_FormatBytes(memory_get_usage(true));
		
		print "<b>MEMORY ".($loc ? "($loc) " : '')."= $memUsage</b><br />";
	}

	function GalleryList($gallery, $recurse) 
	{
		$this->DebugMsg("GALLERY LIST '".($gallery ? $gallery : "<root>")."' [".($recurse ? "RECURSIVE" : "FLAT")."]");
		
		$files = array();
		$galleries = array();
		$galStack[] = $gallery;
		
		while ($galStack)
		{
			$gallery = array_pop($galStack);
			
			if ($this->m_cache[$gallery])
			{
				$this->MemoryMsg("$gallery: Reading cache");
				$this->DebugMsg("GALLERY $gallery CACHED");
				
				foreach ($this->m_cache[$gallery] as $elementI => $cacheInfo)
				{
					$element = $cacheInfo['element'];
					$mediaObj = new AutoGal_CMediaObj($element);
					
					//if ($mediaObj->IsValid()) // Commented cos double checking? Speed!
					//{
						$mediaObj->SetValsFromCache($cacheInfo);
						
						if ($cacheInfo['etype'] == 'f')
						{
							$files[] = $mediaObj;
						}
						else
						{
							if ($recurse) $galStack[] = $mediaObj->Element();
							$galleries[] = $mediaObj;
						}
					//}
				}
				
				$this->MemoryMsg("$gallery: Cache read");
			}
			else
			{
				$this->MemoryMsg("$gallery: Fetching");
				$this->DebugMsg("GALLERY $gallery FETCH");

				$dir = AutoGal_GetAbsGalPath($gallery);
				if (!$dh = opendir($dir))
				{
					$self->m_lastError = "Could not open directory '$dir' for reading!";
					return false;
				}
				
				while ($filename = readdir($dh))
				{
					if (preg_match("/^\./", $filename)) continue;
					$filePath = $dir."/".$filename;
					$mediaObj = NULL;
					
					if (is_file($filePath))
					{
						if (AutoGal_IsMediaFile($filePath))
						{	
							$element = AutoGal_GetElement($filePath);
							$mediaObj = new AutoGal_CMediaObj($element);
							 
							$files[] = $mediaObj;
						}
					}
					elseif (AutoGal_IsMediaDir($filePath))
					{
						$element = AutoGal_GetElement($filePath);
						$mediaObj = new AutoGal_CMediaObj($element);
							
						if ($recurse) $galStack[] = $mediaObj->Element();
						$galleries[] = $mediaObj;
					}
					
					if (($mediaObj)&&($this->m_isCaching))
					{
						$cacheEntry = $mediaObj->CacheEntry();
						$newCache[] = $cacheEntry;
					}
				}
				
				$this->MemoryMsg("$gallery: dir fetched");
				closedir($dh);
			}
		}
		
		if ($newCache) $this->WriteCache($newCache);
		
		return array('files' => $files, 'galleries' => $galleries);
	}
	
	# Function steps through elements in the directory. Used for searching elements recursively, should use 
	# less memory than loading all elements then searching this list.
	function NextElement() 
	{
		# If we haven't started yet, add the gallery to the stack
		if (!$this->m_stepStarted)
		{
			$this->m_stepDirStack[] = $this->m_gallery;
			$this->m_stepStarted = 1;
		}
		
		if (!isset($this->m_stepGallery))
		{
			# New gallery to look under
			$this->DebugMsg("START GALLERY ".$this->m_stepGallery);
			
			if (!$this->m_stepDirStack) return NULL; # Finished
			$this->m_stepGallery = array_pop($this->m_stepDirStack);
			
			if ($this->m_isCaching)	
			{
				$this->ClearCache();
				$this->LoadCache($this->m_stepGallery, 0);
			}
		}
		
		# Check cache
		if ($this->m_cache[$this->m_stepGallery])
		{
			# We are at the start of the cache list
			if (!isset($this->m_stepCacheI))
			{
				$this->DebugMsg("[CACHE] GALLERY CACHED ".$this->m_stepGallery);
				$this->m_stepCacheI = 0;
			}
			
			if ($this->m_stepCacheI >= count($this->m_cache[$this->m_stepGallery]))
			{
				# Reached the end of cache list
				unset($this->m_stepCacheI);
				unset($this->m_stepGallery);
				
				$this->DebugMsg("[CACHE] GALLERY DONE ".$this->m_stepGallery);
				
				return $this->NextElement();
			}
			
			# Grab cached data for current list item
			$cacheInfo = $this->m_cache[$this->m_stepGallery][$this->m_stepCacheI];
			
			$element =  $cacheInfo['element'];
			$mediaObj = new AutoGal_CMediaObj($element);
			
			$this->m_stepCacheI ++;
			
			if ($mediaObj->IsValid())
			{
				# Element is valid, return it
				$mediaObj->SetValsFromCache($cacheInfo);
				
				if (($this->m_recurse)&&($mediaObj->IsGallery()))
				{
					$this->DebugMsg("[CACHE] PUSHING GALLERY ".$mediaObj->Element());
					$this->m_stepDirStack[] = $mediaObj->Element();
				}
				
				return $mediaObj;
			}
			else
			{
				# Invalid element, return the next one
				return $this->NextElement();
			}
		}
		
		# Nothing in cache, search the directory
		$dir = AutoGal_GetAbsGalPath($this->m_stepGallery);
		
		# Create a directory handle
		if (!$this->m_stepDH)
		{
			$this->DebugMsg("[DIR] GALLERY READ ".$this->m_stepGallery);
			
			if (!$this->m_stepDH = opendir($dir))
			{
				$self->m_lastError = "Could not open directory '$dir' for reading!";
				#die("Could not open directory '$dir' for reading!");
				
				unset($this->m_stepDH);
				unset($this->m_stepGallery);
				return $this->NextElement();
			}
		}
		
		# Read from the directory handle
		while ($filename = readdir($this->m_stepDH))
		{
			if (($filename == '.')||($filename == '..')) continue;
			$filePath = $dir."/".$filename;
			$mediaObj = NULL;
			
			if (is_file($filePath))
			{
				if (AutoGal_IsMediaFile($filePath))
				{	
					$element = AutoGal_GetElement($filePath);
					$mediaObj = new AutoGal_CMediaObj($element);
					 
					return $mediaObj;
				}
			}
			elseif (AutoGal_IsMediaDir($filePath))
			{
				$element = AutoGal_GetElement($filePath);
				$mediaObj = new AutoGal_CMediaObj($element);
					
				if ($this->m_recurse) 
				{
					$this->DebugMsg("[DIR] PUSHING GALLERY ".$mediaObj->Element());
					$this->m_stepDirStack[] = $mediaObj->Element();
				}
				
				return $mediaObj;
			}
		}
		
		# Read from directory finished
		$this->DebugMsg("[DIR] CLOSE DIR $dir");
		closedir($this->m_stepDH);
		unset($this->m_stepDH);
		unset($this->m_stepGallery);
		
		return $this->NextElement();
	}
	
	function ClearCache($gallery)
	{
		if (!isset($gallery))
		{
			$this->MemoryMsg("Clearing all cache");
			unset($this->m_cache);
		}
		else
		{
			$this->MemoryMsg("$gallery: Clearing cache");
			unset($this->m_cache[$gallery]);
		}
	}
	
	function WriteCache($fileList)
	{
		$stringFields = array('element', 'parent', 'etype', 'extension', 'title', 'thumbnail');
		$timeFields   = array('updated', 'ctime', 'mtime');
		$numberFields = array('ucview', 'ucupload', 'ucadmin', 'ucmcomment', 'ucgcomment');
		
		$fields = array_merge($stringFields, $numberFields, $timeFields);
		
		$this->MemoryMsg("$gallery: Writting cache");
		$this->DebugMsg("WRITING CACHE");

		foreach ($fileList as $index => $fileInfo)
		{
			$values = array();
			
			foreach ($fields as $field)
			{
				$value = $fileInfo[$field];
				
				if (in_array($field, $timeFields))
				{
					$values[] = "FROM_UNIXTIME($value)";
				}
				elseif (in_array($field, $numberFields))
				{
					$values[] = (is_numeric($value) ? $value : "NULL");
				}
				else
				{
					$values[] = "'".mysql_escape_string($value)."'";
				}
			}
			
			$this->DebugMsg("WRITING CACHE FOR ".$fileInfo['element']);
			$sql = "INSERT INTO ".AUTOGAL_DIRCACHETABLE." (".implode(', ', $fields).") VALUES (".implode(', ', $values).")";
			if (!mysql_unbuffered_query($sql))
			{
				die("SQL ERROR: $sql");
			}
		}
		
		$this->MemoryMsg("$gallery: Cache written");
	}
}

function AutoGal_SortMediaObjs($mediaObjs, $method='name')
{
	if ($method == 'ctime')
	{
		usort($mediaObjs, "AutoGal_CmpMediaObjsByCTime");
	}
	else if ($method == 'mtime')
	{
		usort($mediaObjs, "AutoGal_CmpMediaObjsByMTime");
	}
	elseif (($method == 'datedsc')||($method == 'date'))
	{
		usort($mediaObjs, "AutoGal_CmpMediaObjsByDate");
	}
	elseif ($method == 'dateasc')
	{
		usort($mediaObjs, "AutoGal_CmpMediaObjsByDate");
		$mediaObjs = array_reverse($mediaObjs);
	}
	elseif ($method == 'namedsc')
	{
		usort($mediaObjs, "AutoGal_CmpMediaObjs");
		$mediaObjs = array_reverse($mediaObjs);
	}
	else
	{
		usort($mediaObjs, "AutoGal_CmpMediaObjs");
	}
	
	return $mediaObjs;
}

function AutoGal_CmpMediaObjs($a, $b)
{
   $cmpA = $a->SortField();
   $cmpB = $b->SortField();
    
   if ($cmpA == $cmpB) return 0;
   return ($cmpA < $cmpB) ? -1 : 1;
}

function AutoGal_CmpMediaObjsByCTime($a, $b)
{
   $cmpA = $a->CTime();
   $cmpB = $b->CTime();
      
   if ($cmpA == $cmpB) return 0;
   return ($cmpA > $cmpB) ? -1 : 1;
}

function AutoGal_CmpMediaObjsByMTime($a, $b)
{
   $cmpA = $a->MTime();
   $cmpB = $b->MTime();
     
   if ($cmpA == $cmpB) return 0;
   return (($cmpA > $cmpB) ? -1 : 1);
}

function AutoGal_CmpMediaObjsByDate($a, $b)
{
   $cmpA = $a->UpdateTime();
   $cmpB = $b->UpdateTime();
      
   if ($cmpA == $cmpB) return 0;
   return ($cmpA > $cmpB) ? -1 : 1;
}

?>