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

if (!defined("AUTOGAL_CHARSET"))
{
	if (defined("CHARSET")){
		define("AUTOGAL_CHARSET", CHARSET);
	}else{
		define("AUTOGAL_CHARSET", "utf-8");
	}
}

define("AUTOGAL_XMLALLOWEDTAGS", 'description|viewhits|emailhits|submitbyusername|submitbyuserid|submitdate|viewclass|uploadclass|adminclass|gcommentclass|mcommentclass|suggestedgallery|viewwidth|viewheight');
define("AUTOGAL_XMLALLOWEDGROUPTAGS", 'comments|ratings|arcade');

class AutoGal_CMediaObj
{
	var $m_printDebugMsgs;
	var $m_printSpeedMsgs;
	var $m_info;
	var $m_element;
	var $m_lastError;
	var $m_xmlParser;
	var $m_xmlCurrentTag;
	var $m_xmlCurrGroupData;
	var $m_xmlCurrGroup;
	var $m_xmlLoaded;
	var $m_xmlIsChange;
	
	function AutoGal_CMediaObj($element, $isAbsPath=false)
	{
		$this->m_printDebugMsgs = 0;
		$this->m_printSpeedMsgs = 0;
	
		if ($isAbsPath)
		{
			$element = preg_replace("/[\/]+$/", "", $element);
			$this->m_info['abspath'] = $element;
			
			$absPath = str_replace("\\", '/', $element);
			
			# Check if file is in upload dir
			$pos = strpos($absPath, AUTOGAL_UPLOADDIRABS);
			if (($pos !== false)&&($pos == 0))
			{
				$this->m_element = str_replace(AUTOGAL_UPLOADDIRABS, "", $absPath);
			}
			
			# Check if file is gallery dir
			if (!isset($this->m_element))
			{
				$galPath = AutoGal_GetAbsGalPath('');
				$pos = strpos($absPath, $galPath);
				
				if (($pos !== false)&&($pos == 0))
				{
					$this->m_element = str_replace($galPath, "", $absPath);
				}
			}
			
			# NFI
			if (!isset($this->m_element))
			{
				$this->m_element = "unknown:$absPath";
			}
		}
		else
		{
			$this->m_element = $element;
		}
		
		$this->m_element = str_replace("\\", '/', $this->m_element);
		$this->m_element = preg_replace("/^[\/]+/", "", $this->m_element);
		$this->m_element = preg_replace("/[\/]+$/", "", $this->m_element);
			
		$this->m_xmlLoaded = false;
	}
	
	///////////////////////////////////////////////////////
	// Get/Set Functions
	///////////////////////////////////////////////////////
	function EnableSpeedMsgs($enable)
	{
		if (isset($enable)) $this->m_printSpeedMsgs = $enable;
		return $this->m_printSpeedMsgs;
	}
	
	function EnableDebugMsgs($enable)
	{
		if (isset($enable)) $this->m_printDebugMsgs = $enable;
		return $this->m_printDebugMsgs;
	}
	
	function Element()
	{
		return $this->m_element;
	}
	
	function AbsPath()
	{
		if ($this->m_info['abspath']) return $this->m_info['abspath'];
		
		$this->m_info['abspath'] = AutoGal_GetAbsGalPath($this->Element());
		return $this->m_info['abspath'];
	}
	
	function IsRoot()
	{
		if ($this->Element() == "") return 1;
		return 0;
	}
	
	function IsUpload()
	{
		$pos = strpos($this->AbsPath(), AUTOGAL_UPLOADDIRABS);
		
		if (($pos !== false)&&($pos == 0))
		{
			return 1;
		}
		return 0;
	}
	
	function IsValid()
	{
		if (isset($this->m_info['isvalid'])) return $this->m_info['isvalid'];
		
		$this->m_info['isvalid'] = 0;
		
		$this->SpeedMsg("IsValid: is_readable(".$this->AbsPath().")");
		
		if (is_readable($this->AbsPath()))
		{
			$absPath = $this->AbsPath();
			$rootAbsPath =  str_replace("\\", "/", AutoGal_GetAbsGalPath(''));
			$pos = strpos($absPath, $rootAbsPath);
			
			if (($pos !== false)&&($pos == 0))
			{
				if ($this->IsGallery())
				{
					if (AutoGal_IsMediaDir($this->AbsPath()))
					{
						$this->m_info['isvalid'] = 1;
					}
					else
					{
						$this->LastError("Not a valid gallery directory");
					}
				}
				else
				{
					if (AutoGal_IsMediaFile($this->AbsPath()))
					{
						$this->m_info['isvalid'] = 1;
					}
					else
					{
						$this->LastError("Not a valid media file");
					}
				}
			}
			else
			{
				$this->LastError("Not in gallery dir");
			}
		}
		else
		{
			$this->LastError("Not readible");
		}
		
		return $this->m_info['isvalid'];
	}
	
	function IsGallery()
	{
		global $pref;
		if (isset($this->m_info['isgallery'])) return $this->m_info['isgallery'];
		
		if ($pref['autogal_usequickgaldetect'])
		{
			if (AutoGal_IsMediaFile($this->AbsPath()))
			{
				$this->m_info['isgallery'] = 0;
			}
			else
			{
				$this->m_info['isgallery'] = 1;
			}
		}
		else
		{
			$this->SpeedMsg("IsGallery: is_dir(".$this->AbsPath().")");
			if (is_dir($this->AbsPath()))
			{
				$this->m_info['isgallery'] = 1;
			}
			else
			{
				$this->m_info['isgallery'] = 0;
			}
		}
		
		return $this->m_info['isgallery'];
	}
	
	function IsFile()
	{
		return !$this->IsGallery();
	}
	
	function IsUserGalleryRoot()
	{
		if (!AUTOGAL_USERGALENABLE) return 0;
		if ($this->Element() == AUTOGAL_USERGALLERYDIR) return 1;
		return 0;
	}
	
	function IsUserGallery()
	{
		if (!AUTOGAL_USERGALENABLE) return 0;
		if (substr($this->Element(), 0, strlen(AUTOGAL_USERGALLERYDIR) + 1) == AUTOGAL_USERGALLERYDIR.'/') return 1;
		return 0;
	}
	
	function UserGalleryOwner()
	{
		if ($this->m_info['owner']) return $this->m_info['owner'];
		if (!$this->IsUserGallery()) return;
		$pathBits = explode('/', $this->Element());
		
		$this->m_info['owner'] = $pathBits[1];
		return $this->m_info['owner'];
	}
	
	function IsUserGalleryOwner()
	{
		if (!$this->IsUserGallery()) return 0;
		
		if ($this->UserGalleryOwner() == USERNAME) return 1;
		return 0;
	}
	
	function TitleLink($part)
	{
		return $this->AHref().">".$this->Title()."</a>";
	}
		
	function Title($part)
	{
		global $pref;
		if ($this->IsRoot())  
		{
			$title = $pref['autogal_rootname'];
		}
		elseif ($this->IsUserGalleryRoot())  
		{
			$title = AUTOGAL_USERGALNAME;
		}
		else
		{
			$title = $this->BaseName();
			$title = preg_replace("/\.[\w]{3,4}$/", '', $title);
			$title = str_replace('_', ' ', $title);
			$title = preg_replace("/^\d+\./", "", $title);
		}
		
		$fullTitle = $title;
		
		if (!$this->IsUserGallery())
		{
			// SUBTITLE
			if (preg_match("/^(.+)\[(.*)\]$/", $title, $bits))
			{
				$title = $bits[1];
				$subTitle = $bits[2];
				$subTitle = htmlspecialchars($subTitle);
			}
			else
			{
				$subTitle = '';
			}
		
			// UPPERCASE FIRST LETTERS
			if ($pref['autogal_ucasetitles'])
			{
				$smallWordsStr = strtolower(str_replace(' ', '', $pref['autogal_smallwords']));
				$smallWords = explode(',', $smallWordsStr);
			
				$title = ucwords($title);
				$fullTitle = ucwords($fullTitle);
				$subTitle = ucwords($subTitle);
				
				foreach ($smallWords as $word)
				{
					$title = str_replace(' '.ucfirst($word).' ', " $word ", $title);
					$title = preg_replace("/ ".ucfirst($word)."$/", " $word", $title);
					$fullTitle = str_replace(' '.ucfirst($word).' ', " $word ", $fullTitle);
					$fullTitle = preg_replace("/ ".ucfirst($word)."$/", " $word", $fullTitle);
					$subTitle = str_replace(' '.ucfirst($word).' ', " $word ", $subTitle);
					$subTitle = preg_replace("/ ".ucfirst($word)."$/", " $word", $subTitle);
				}
			}
		}
	
		// FOR &'s ETC.
		$title = htmlspecialchars($title);
		$fullTitle = htmlspecialchars($fullTitle);
		$subTitle = htmlspecialchars($subTitle);
			
		// THIS IS TO KEEP BACKWARDS COMPATABILITY
		if ($part == 'title')
			return $title;
		elseif (($part == 'subtitle')||($part == 'sub'))
			return $subTitle;
		elseif (($part == 'fulltitle')||($part == 'full'))
			return $fullTitle;
		elseif ($part == 'all')
			return (array($title, $subTitle, $fullTitle));
		else
			return $title;
	}
	
	function SubTitle()
	{
		return $this->Title('subtitle');
	}
	
	function FullTitle()
	{
		return $this->Title('fulltitle');
	}
	
	function TypeTitle()
	{
		if ($this->IsGallery())
		{
			return AUTOGAL_LANG_FTYPE_GALL;
		}
		else
		{
			return $this->FileTypeTitle();
		}
	}
	
	function FileType()
	{
		if (isset($this->m_info['filetype'])) return $this->m_info['filetype'];
		
		// FLASH/MOVIES
		$ext = $this->Extension();
		
		if (preg_match("/^(".AUTOGAL_FLASHEXTS.")$/i", $ext))
		{
			$this->m_info['filetype'] = "flash";
		}
		elseif (preg_match("/^(".AUTOGAL_FLVEXTS.")$/i", $ext))
		{
			$this->m_info['filetype'] = "flv";
		}
		elseif (preg_match("/^(".AUTOGAL_QUICKTIMEEXTS.")$/i", $ext))
		{
			$this->m_info['filetype'] = "qt";
		}
		elseif (preg_match("/^(".AUTOGAL_WINMEDIAEXTS.")$/i", $ext))
		{
			$this->m_info['filetype'] = "wmv";
		}
		elseif (preg_match("/^(".AUTOGAL_WINMEDIAEXTS_A.")$/i", $ext))
		{
			$this->m_info['filetype'] = "wma";
		}
		elseif (preg_match("/^(".AUTOGAL_REALMEDIAEXTS.")$/i", $ext))
		{
			$this->m_info['filetype'] = "rm";
		}
		elseif (preg_match("/^(".AUTOGAL_IMAGEEXTS.")$/i", $ext))
		{
			$this->m_info['filetype'] = "image";
		}
		else
		{
			$this->m_info['filetype'] = '';
		}
		
		return $this->m_info['filetype'];
	}
	
	function FileTypeTitle()
	{
		$fileType = $this->FileType();
		
		switch ($fileType)
		{
			case 'flash': return AUTOGAL_LANG_FTYPE_FLASH;
			case 'flv':   return AUTOGAL_LANG_FTYPE_FLV;
			case 'qt':    return AUTOGAL_LANG_FTYPE_QTIME;
			case 'wmv':   return AUTOGAL_LANG_FTYPE_WMV;
			case 'wma':   return AUTOGAL_LANG_FTYPE_AUDIO;
			case 'rm':    return AUTOGAL_LANG_FTYPE_RM;
			case 'image': return AUTOGAL_LANG_FTYPE_IMAGE;
		}
	}
	
	function ThumbImageNoneUrl()
	{
		global $pref;
		if ($this->IsGallery())
		{
			return ($pref['autogal_defthumbgallery'] ? AUTOGAL_UNAVAILTHUMB_GALLERY : '');
		}
		else
		{
			$fileType = $this->FileType();
			switch ($fileType)
			{
				case 'flash': return ($pref['autogal_defthumbanimation'] ?  AUTOGAL_UNAVAILTHUMB_ANIMATION : '');
				case 'flv':   return ($pref['autogal_defthumbmovie'] ? AUTOGAL_UNAVAILTHUMB_MOVIE : '');
				case 'qt':    return ($pref['autogal_defthumbmovie'] ? AUTOGAL_UNAVAILTHUMB_MOVIE : '');
				case 'wmv':   return ($pref['autogal_defthumbmovie'] ? AUTOGAL_UNAVAILTHUMB_MOVIE : '');
				case 'wma':   return ($pref['autogal_defthumbaudio'] ? AUTOGAL_UNAVAILTHUMB_AUDIO : '');
				case 'rm':    return ($pref['autogal_defthumbmovie'] ? AUTOGAL_UNAVAILTHUMB_MOVIE : '');
				case 'image': return ($pref['autogal_defthumbimage'] ? AUTOGAL_UNAVAILTHUMB_IMAGE : '');
			}
		}
		
		return AUTOGAL_UNAVAILTHUMB;
	}
	
	function ThumbImageFromCache($thumbBasename)
	{
		global $pref;
		if (!$pref['autogal_usethumbnailcache']) return;
		
		if ($thumbBasename == '*') $thumbBasename = '';
		$this->m_info['thumbbasename'] = $thumbBasename;
		$this->m_info['thumbcacheset'] = 1;
	}
	
	function ThumbImageInfo()
	{
		global $pref;
		if (isset($this->m_info['thumbimage'])) return $this->m_info['thumbimage'];
		
		$thumb['ok'] = false;
		$thumb['false'] = true;
		$thumb['abspath']  = '';
		$thumb['url'] = '';
		
		$thumbBasename = $this->m_info['thumbbasename'];
		
		if (($this->m_info['thumbcacheset'])&&(!$thumbBasename))
		{
			$this->m_info['thumbimage'] = $thumb;
			return $this->m_info['thumbimage'];
		}
		
		$gallery = $this->Gallery();
		$imageExts = explode('|', AUTOGAL_THUMBIMAGEEXTS);
		
		if (($this->IsGallery())&&(!$this->IsRoot()))
		{
			$galleryAbsPath = $this->AbsPath();
			$defImageName = AUTOGAL_GALLERYTHUMBFILENAME;
			
			if ($thumbBasename)
			{
				$thumb['ok'] = true;
				$thumb['exists'] = true;
				$thumb['abspath'] = "$galleryAbsPath/$thumbBasename";
				$thumb['url'] = AutoGal_GetImageURL($this->Element()."/$thumbBasename");
				
				#$this->SpeedMsg("ThumbImageInfo: cached(".$thumb['url'].")");
			}
			else
			{
				foreach ($imageExts as $ext)
				{
					$thumbPath = "$galleryAbsPath/$defImageName.$ext";
					
					$this->SpeedMsg("ThumbImageInfo: is_readable($thumbPath)");
					if (is_readable($thumbPath))
					{
						$imgEle = $this->Element()."/$defImageName.$ext";
						
						$thumb['ok'] = true;
						$thumb['exists'] = true;
						$thumb['abspath'] = $thumbPath;
						$thumb['url'] = AutoGal_GetImageURL($imgEle);
					}
				}
				
				if (($pref['autogal_autothumb'])&&($pref['autogal_autosizegalthumbs'])&&($thumb['ok']))
				{
					$this->SpeedMsg("ThumbImageInfo: getimagesize(".$this->m_info['thumbpath'].")");
					$image_stats = getimagesize($this->m_info['thumbpath']);
					$imageWidth = $image_stats[0];
					$imageHeight = $image_stats[1];
					
					if (($imageWidth > $pref['autogal_galthumbwidth'])||($imageHeight > $pref['autogal_galthumbheight']))
					{
						$thumb['url'] = AUTOGAL_RESIZE."?img=".rawurlencode($this->Element());
					}
				}
			}
		}
		else if ($this->FileType() == 'image')
		{
			if ($this->IsUpload())
			{
				$thumbPath = AUTOGAL_UPLOADDIRABS.'/'.AUTOGAL_THUMBPREFIX.$this->BaseName(); 
	
				$this->SpeedMsg("ThumbImageInfo: is_readable($thumbPath)");
				if (is_readable($thumbPath))
				{
					$thumb['ok'] = true;
					$thumb['exists'] = true;
				}
				
				$thumb['abspath'] = $thumbPath;
				$thumb['url'] = AUTOGAL_UPLOADDIR.'/'.basename($thumbPath);
			}
			else
			{
				if ($thumbBasename)
				{
					$thumb['abspath'] = $this->DirName().'/'.$thumbBasename;
					$thumb['ok'] = true;
					$thumb['exists'] = true;
					$thumb['url'] = AutoGal_GetImageURL("$gallery/$thumbBasename");
					#$this->SpeedMsg("ThumbImageInfo: cached(".$thumb['url'].")");
				}
				else
				{
					$thumbPath = $this->DirName().'/'.AUTOGAL_THUMBPREFIX.$this->BaseName(); 
					$thumb['abspath'] = $thumbPath;
					$this->SpeedMsg("ThumbImageInfo: is_readable($thumbPath)");
				
					if (is_readable($thumbPath))
					{
						$thumb['ok'] = true;
						$thumb['exists'] = true;
						$thumb['url'] = AutoGal_GetImageURL("$gallery/".basename($thumbPath));
					}
					else if ($pref['autogal_autothumb'])
					{
						$thumb['url'] = AUTOGAL_RESIZE."?img=".rawurlencode($this->Element());
						$thumb['ok'] = true;
						$thumb['exists'] = false;
					}
				}
			}
		}
		else
		{
			if (($thumbBasename)&&(!$this->IsUpload()))
			{
				$thumb['ok'] = true;
				$thumb['exists'] = true;
				$thumb['abspath'] = $this->DirName().'/'.$thumbBasename;
				$thumb['url'] = AutoGal_GetImageURL("$gallery/$thumbBasename");
				
				#$this->SpeedMsg("ThumbImageInfo: cached(".$thumb['url'].")");
			}
			else
			{
				$possibleExts = explode('|', AUTOGAL_THUMBIMAGEEXTS);
				$thExt = '';
				
				foreach ($possibleExts as $testExt)
				{
					$thumbPath = $this->DirName().'/'.AUTOGAL_THUMBPREFIX.$this->BaseName().'.'.$testExt;
					$this->SpeedMsg("ThumbImageInfo: is_readable($thumbPath)");
					if (is_readable($thumbPath))
					{
						$thumb['ok'] = true;
						$thumb['exists'] = true;
						$thumb['abspath'] = $thumbPath;
						
						if ($this->IsUpload())
						{
							$thumb['url'] = AUTOGAL_UPLOADDIR.'/'.basename($thumbPath);
						}
						else
						{
							$thumb['url'] = AutoGal_GetImageURL("$gallery/".basename($thumbPath));
						}
					}
				}
			}
		}
		
		$this->m_info['thumbimage'] = $thumb;
		return $this->m_info['thumbimage'];
	}
	
	function ThumbAndTitleHtml($showSubTitle, $showUnavail=1)
	{
		$text =
		((($this->ThumbImageOK())||($showUnavail)) ? $this->ThumbImageHtml(1, 1) : '').
		$this->TitleLink().
		(($showSubTitle)&&($this->SubTitle())  ? "<br /><span class='smalltext'>(".$this->SubTitle().")</span>" : '');
		
		return $text;
	}
	
	function ThumbImagePath()
	{
		$thumbInfo = $this->ThumbImageInfo();
		return $thumbInfo['abspath'];
	}
	
	function ThumbImageElePath()
	{
		if ($this->IsRoot())
		{
			return "";
		}
		else
		{
			return ($this->IsInRoot() ? '' : dirname($this->Element())."/").basename($this->ThumbImagePath());
		}
	}
	
	function ThumbImageExists()
	{
		$thumbInfo = $this->ThumbImageInfo();
		return $thumbInfo['exists'];
	}
	
	function ThumbImageOK()
	{
		$thumbInfo = $this->ThumbImageInfo();
		return $thumbInfo['ok'];
	}
	
	function ThumbImageUrl()
	{
		$thumbInfo = $this->ThumbImageInfo();
		return $thumbInfo['url'];
	}
	
	function ThumbImageHtml($withBr, $withLink)
	{
		if ($this->ThumbImageOK())
		{
			$text = "<img src=\"".$this->ThumbImageUrl()."\" alt=\"".$this->Title()."\" style='border:none' />"; 
		}
		else if ($this->ThumbImageNoneUrl())
		{
			$text = "<img src=\"".$this->ThumbImageNoneUrl()."\" alt=\"".$this->Title()."\" style='border:none' />"; 
		}
		
		if (!$text) return;
		
		if ($withLink) $text = $this->AHref().">".$text."</a>";
		if ($withBr) $text .= "<br />";
		
		return $text;
	}
	
	function PreviewImageInfo()
	{
		if (isset($this->m_info['previewimage'])) return $this->m_info['previewimage'];
		
		$pvImage['exists'] = false;
		$pvImage['abspath']  = '';
		$pvImage['url']  = '';
		
		$pvImage['abspath'] = $this->DirName().'/'.AUTOGAL_PREVIEWIMGPREFIX.$this->BaseName();
		$pvImage['url'] = AutoGal_GetImageURL($this->Gallery()."/".basename($pvImage['abspath']));
		
		$this->SpeedMsg("PreviewImageInfo: is_readable(".$pvImage['abspath'].")");
		if (is_readable($pvImage['abspath']))
		{
			$pvImage['exists'] = true;
		}
		
		$this->m_info['previewimage'] = $pvImage; 
		return $this->m_info['previewimage'];
	}
	
	function PreviewImagePath()
	{
		$pvImage = $this->PreviewImageInfo();
		return $pvImage['abspath'];
	}
	
	function PreviewImageUrl()
	{
		$pvImage = $this->PreviewImageInfo();
		return $pvImage['url'];
	}
	
	function PreviewImageExists()
	{
		$pvImage = $this->PreviewImageInfo();
		return $pvImage['exists'];
	}
		
	function BaseName()
	{
		return basename($this->AbsPath());
	}
	
	function DirName()
	{
		return dirname($this->AbsPath());
	}
	
	function Extension()
	{
		$pathInfo = pathinfo($this->AbsPath()); 
		return $pathInfo['extension'];
	}
	
	function Url()
	{
		global $pref;
		if ($this->IsRoot())
		{
			return $pref['autogal_gallerydir'];
		}
		elseif ($this->IsUpload())
		{
			return AUTOGAL_UPLOADDIR."/".$this->BaseName();
		}
		else
		{
			return AutoGal_GetImageURL($this->Element());
		}
	}
	
	function LinkArgs($extraArgs)
	{
		if ($this->IsRoot())
		{
			return ($extraArgs ? "?$extraArgs" : "");
		}
		else
		{
			return "?show=".rawurlencode($this->Element()).($extraArgs ? "&$extraArgs" : "");
		}
	}
	
	function Link($extraArgs)
	{
		return AUTOGAL_AUTOGALLERY.$this->LinkArgs($extraArgs);
	}
	
	function AHref()
	{
		global $pref;
		if ($pref['autogal_showinnewwindow'])
		{
			$windowLink = $this->Link().($this->IsRoot() ? '' : '&')."newwindow=1";
			$windowLink = str_replace("'", "\\'", $windowLink);
			
			$statusTitle = str_replace("[TITLE]", $this->Title(), AUTOGAL_LANG_L36);
			$statusTitle = str_replace("'", "\\'", $statusTitle);
			
			$openWindowJS = AutoGal_NewWindowJS('show', $windowLink, 'viewimage');
					
			return "<a href=\"javascript: void(0);\" onmouseover=\"window.status='$statusTitle'; return true;\" onmouseout=\"window.status=''; return true;\" onClick=\"javascript:".$openWindowJS."\" ";
		}
		else
		{
			return "<a href=\"".$this->BackLink()."\" title=\"".htmlspecialchars($this->TypeTitle().': '.$this->Title())."\" ";
		}
	}
		
	function BackLink($vars)
	{
		global $g_startFile, $pref;
		global $g_startGallery;
		global $g_sortOrder;
		global $g_isNewWindow;
		
		if (!isset($vars['start']))     $vars['start']     = $g_startFile;
		if (!isset($vars['startgal']))  $vars['startgal']  = $g_startGallery;
		if (!isset($vars['order']))     $vars['order']     = $g_sortOrder;
		if (!isset($vars['newwindow'])) $vars['newwindow'] = $g_isNewWindow;
		
		if (!$vars['start']) unset($vars['start']);
		if (!$vars['startgal']) unset($vars['startgal']);
		if ($vars['order'] == $pref['autogal_defaultdisporder']) unset($vars['order']);
		if (!$vars['order']) unset($vars['order']);
		if (!$vars['newwindow']) unset($vars['newwindow']);
		
		foreach ($vars as $var => $value)
		{
			$args[] = "$var=$value";
		}
		
		return $this->Link(implode("&amp;", $args));
	}
	
	function FileStats()
	{
		if (isset($this->m_info['filestats'])) return $this->m_info['filestats'];
		
		$this->SpeedMsg("FileStats: stat(".$this->AbsPath().")");
		$this->m_info['filestats'] = stat($this->AbsPath());
		
	}
	
	function UpdateTime()
	{
		global $pref;
		if ($pref['autogal_sortdatectime'])
		{
			return $this->CTime();
		}
		else
		{
			return $this->MTime();
		}
	}
	
	function CTime($time)
	{
		if (isset($time))
		{
			if (!is_numeric($time)) $time = $this->DecodeMySqlDate($time);
			$this->m_info['filestats']['ctime'] = $time;
		}
		
		if (isset($this->m_info['filestats']['ctime'])) 
		{
			return $this->m_info['filestats']['ctime'];
		}
		
		$stats = $this->FileStats();
		return $this->m_info['filestats']['ctime'];
	}
	
	function MTime($time)
	{
		if (isset($time))
		{
			if (!is_numeric($time)) $time = $this->DecodeMySqlDate($time);
			$this->m_info['filestats']['mtime'] = $time;
		}
		
		if (isset($this->m_info['filestats']['mtime'])) 
		{
			return $this->m_info['filestats']['mtime'];
		}
		
		$stats = $this->FileStats();
		return $this->m_info['filestats']['mtime'];
	}
	
	function Size($bytes)
	{
		if (isset($time))
		{
			$this->m_info['filestats']['size'] = $bytes;
		}
		
		if (isset($this->m_info['filestats']['size'])) 
		{
			return $this->m_info['filestats']['size'];
		}
		
		$stats = $this->FileStats();
		return $this->m_info['filestats']['size'];
	}
	
	function SizeFormatted()
	{
		$fileSize = $this->Size();
		
		return AutoGal_FormatBytes($fileSize);
	}

	function CacheEntry()
	{
		$cacheEntry['element'] = $this->Element();
		$cacheEntry['parent'] = ($this->Gallery() ? $this->Gallery() : '*');
		$cacheEntry['etype'] = ($this->IsFile() ? 'f' : 'g');
		$cacheEntry['extension'] = $this->Extension();
		$cacheEntry['title'] = $this->Title();
		$cacheEntry['ctime'] = $this->CTime();
		$cacheEntry['mtime'] = $this->MTime();
		$cacheEntry['thumbnail'] = ($this->ThumbImageOK() ? basename($this->ThumbImagePath()) : '*');
		$cacheEntry['updated'] = time();
		
		if (($this->IsGallery())&&(AUTOGAL_USERCLASSCACHE))
		{
			$cacheEntry['ucview'] = $this->UserClass('view');
			$cacheEntry['ucupload'] = $this->UserClass('upload');
			$cacheEntry['ucadmin'] = $this->UserClass('admin');
			$cacheEntry['ucmcomment'] = $this->UserClass('mcomment');
			$cacheEntry['ucgcomment'] = $this->UserClass('gcomment');
		}
		
		return $cacheEntry;
	}
		
	function CacheTimeUpdated($time)
	{
		if (isset($time)) $this->m_info['cachetimeupdated'] = $time;
		return $this->m_info['cachetimeupdated'];
	}
	
	function OrderNumber()
	{
		if (preg_match("/^(\d+)\..*/", $this->BaseName(), $IDBits))
		{
			return $IDBits[1];
		}
		
		return -1;
	}
	
	function PathTitle($seperator=" / ")
	{
		if ($this->IsRoot()) return $this->Title();
		$element = $this->Element();
		
		$pathBits = explode('/', $element);
		foreach ($pathBits as $bit)
		{
			$currEle .= ($currEle ? "/" : '').$bit;
			
			$mediaObj = new AutoGal_CMediaObj($currEle);
			$titles[] = $mediaObj->Title();
		}

		return implode($seperator, $titles);
	}
	
	function Gallery()
	{
		$element = $this->Element();
		
		if (strpos($element, '/') === false)
		{
			return '';
		}
		
		$gallery = dirname($element);
		$gallery = ($gallery == '.' ? '' : $gallery);
	   
		return $gallery;
	}
	
	function GalleryMediaObj()
	{
		return new AutoGal_CMediaObj($this->Gallery());
	}
	
	function IsInRoot()
	{
		$gallObj = $this->GalleryMediaObj();
		
		#print "ASDASD<Br/ >";
		#AutoGal_Dump($gallObj);
		
		return $gallObj->IsRoot();
	}
	
	function GalleryAbsPath()
	{
		if (isset($this->m_info['galleryabspath'])) return $this->m_info['galleryabspath'];
		
		$this->SpeedMsg("GalleryAbsPath: AutoGal_GetAbsGalPath(".$this->Gallery().")");
		$this->m_info['galleryabspath'] = AutoGal_GetAbsGalPath($this->Gallery());
		return $this->m_info['galleryabspath'];
	}
	
	function SortField()
	{
		$orderNum = $this->OrderNumber();
		if ((is_numeric($orderNum))&&($orderNum >= 0)){
			return $this->OrderNumber();
		}else{
			return $this->Title();
		}
	}
	
	function NavLinks()
	{
		global $pref;
		$galleries = explode ('/', $this->Element());
		
		$rootGal = new AutoGal_CMediaObj('');
				
		if (!$this->IsRoot())
		{
			$navLinks[] = "<a href=\"".$rootGal->BackLink()."\">".$pref['autogal_rootname']."</a>";
		}
	
		for ($galIndex = 0; $galIndex < count($galleries) - 1; $galIndex ++)
		{
			$gallery = $galleries[$galIndex];
			$galleryEle .= ($galleryEle ? '/' : '').$gallery;
			$galleryObj = new AutoGal_CMediaObj($galleryEle);
				
			$navLinks[] = "<a href=\"".$galleryObj->BackLink()."\">".$galleryObj->Title()."</a>";
		}
		
		$navLinks[] = "<a href=\"".$this->BackLink()."\"><b>".$this->Title()."</b></a>";
		
		return implode($pref['autogal_navseperator'], $navLinks);
	}
	
	function EmailLink()
	{
		global $pref;
		if ($pref['autogal_emailtofriend'])
		{
			return "<b><a href=\"".AUTOGAL_EMAILTOFRIEND."?ele=".rawurlencode($this->Element()).($pref['autogal_showinnewwindow'] ? "&newwindow=1" : '')."\">".AUTOGAL_LANG_L20."</a></b>";
		}
		else
		{
			return '';
		}
	}
	
	function SetValsFromCache($cacheInfo)
	{
		$this->CTime($cacheInfo['ctime']);
		$this->MTime($cacheInfo['mtime']);
		$this->ThumbImageFromCache($cacheInfo['thumbnail']);
		$this->CacheTimeUpdated($cacheInfo['updated']);
	}
	
	function GalleryPosition()
	{
		if (isset($this->m_info['galleryposition'])) return $this->m_info['galleryposition'];
		
		$mediaObjs = $this->GalleryMediaObjs();
		
		if ($this->IsGallery())	{
			$key = 'galleries';
		}else{	
			$key = 'files';
		}
		
		$this->m_info['galleryposition'] = -1;
		
		for ($position = 0; $position < count($mediaObjs[$key]); $position ++)
		{
			#$p[] = $mediaObjs[$key][$position]->Element();
			if ($mediaObjs[$key][$position]->Element() == $this->Element())
			{
				$this->m_info['galleryposition'] = $position;
				break;
			}
		}
		
		#AutoGal_Dump($p);
		return $this->m_info['galleryposition'];
	}
	
	function FirstMediaObj()
	{
		if ($this->IsGallery()){
			$key = 'galleries';
		}else{	
			$key = 'files';
		}
		
		$mediaObjs = $this->GalleryMediaObjs();
		$numObjs = count($mediaObjs[$key]);
		
		if ($numObjs > 0)
		{
			return $mediaObjs[$key][0];
		}
		else
		{
			return $this;
		}
	}
	
	function PrevMediaObj()
	{
		$position = $this->GalleryPosition();
		
		if ($this->IsGallery()){
			$key = 'galleries';
		}else{	
			$key = 'files';
		}
		
		$mediaObjs = $this->GalleryMediaObjs();
		$numObjs = count($mediaObjs[$key]);
		
		if ($position > 0)
		{
			return $mediaObjs[$key][$position - 1];
		}
		else if ($numObjs > 1)
		{
			return $mediaObjs[$key][$numObjs - 1];
		}
		else
		{
			return $this;
		}
	}
	
	function NextMediaObj()
	{
		$position = $this->GalleryPosition();
		
		if ($this->IsGallery()){
			$key = 'galleries';
		}else{	
			$key = 'files';
		}
		
		$mediaObjs = $this->GalleryMediaObjs();
		$numObjs = count($mediaObjs[$key]);
		
		if ($position >= ($numObjs - 1))
		{
			return $mediaObjs[$key][0];
		}
		else
		{
			return $mediaObjs[$key][$position + 1];
		}
	}
	
	function LastMediaObj()
	{
		if ($this->IsGallery()){
			$key = 'galleries';
		}else{	
			$key = 'files';
		}
		
		$mediaObjs = $this->GalleryMediaObjs();
		$numObjs = count($mediaObjs[$key]);
		
		if ($numObjs > 0)
		{
			return $mediaObjs[$key][$numObjs - 1];
		}
		else
		{
			return $this;
		}
	}
	
	function GalleryMediaObjs($sortOrder)
	{
		global $g_sortOrder, $pref;
		
		if (isset($this->m_info['galleryobjs'])) return $this->m_info['galleryobjs'];
		
		$this->SpeedMsg("GalleryMediaObjs: List Objects (".$this->Gallery().")");
		require_once(AUTOGAL_MEDIALISTCLASS);
		$galList = new AutoGal_CMediaList($this->Gallery(), array('sortorder' => ($sortOrder ? $sortOrder : $g_sortOrder), 'recurse' => 0, 'usecache' => $pref['autogal_enabledbcache']));
		$galList->ListGallery();
		
		$this->m_info['galleryobjs'] = $galList->MediaObjects();
		return $this->m_info['galleryobjs'];
	}
	
	function ChildMediaObjs($sortOrder)
	{
		global $g_sortOrder, $pref;
		
		if (isset($this->m_info['childobjs'])) return $this->m_info['childobjs'];
		if (!$this->IsGallery()) return;
		
		$this->SpeedMsg("ChildMediaObjs: List Objects");
		require_once(AUTOGAL_MEDIALISTCLASS);
		$galList = new AutoGal_CMediaList($this->Element(), array('sortorder' => ($sortOrder ? $sortOrder : $g_sortOrder), 'recurse' => 0, 'usecache' => $pref['autogal_enabledbcache']));
		$galList->ListGallery();
		$this->m_info['childobjs'] = $galList->MediaObjects();
		
		#AutoGal_Dump($this->m_info['childobjs']);
		
		return $this->m_info['childobjs'];
	}
	
	function SubGalleries($sortOrder)
	{
		global $pref;
		if (isset($this->m_info['subgalleries'])) return $this->m_info['subgalleries'];
		if (!$this->IsGallery()) return;
		
		$this->SpeedMsg("SubGalleries: List Objects");
		
		require_once(AUTOGAL_MEDIALISTCLASS);
		
		$galList = new AutoGal_CMediaList($this->Element(), array('sortorder' => 'name', 'recurse' => 1, 'usecache' => $pref['autogal_enabledbcache']));
		#$galList->EnableDebugMsgs(1);
		#$galList->EnableMemoryMsgs(1);

		while ($mediaObj = $galList->NextElement())
		{
			if ($mediaObj->IsFile()) continue;
			$this->m_info['subgalleries'][] = $mediaObj;
		}

		return $this->m_info['subgalleries'];
	}
	
	function NavButtons($showSlideShow, $showClose)
	{
		global $pref;
		$element = $this->Element();
		$gallery = $this->Gallery();
		
		$firstObj = $this->FirstMediaObj();
		$prevObj = $this->PrevMediaObj();
		$nextObj = $this->NextMediaObj();
		$lastObj = $this->LastMediaObj();
		$galObj = $this->GalleryMediaObj();
			
		$firstFile =   "<input type='button' title=\"".$firstObj->Title()."\" class='button' value='".AUTOGAL_LANG_L7."' onclick='javascript:window.location.href=\"".$firstObj->BackLink()."\"' />";
		$prevFile =    "<input type='button' title=\"".$prevObj->Title()."\" class='button' value='".AUTOGAL_LANG_L8."' onclick='javascript:window.location.href=\"".$prevObj->BackLink()."\"' />";
		$nextFile =    "<input type='button' title=\"".$nextObj->Title()."\" class='button' value='".AUTOGAL_LANG_L9."' onclick='javascript:window.location.href=\"".$nextObj->BackLink()."\"' />";
		$lastFile =    "<input type='button' title=\"".$lastObj->Title()."\" class='button' value='".AUTOGAL_LANG_L10."' onclick='javascript:window.location.href=\"".$lastObj->BackLink()."\"' />";
		$galleryLink = "<input type='button' title=\"".AUTOGAL_LANG_L13.$galObj->Title()."\" class='button' value='".AUTOGAL_LANG_L11."' onclick='javascript:window.location.href=\"".$galObj->BackLink()."\"' />";
		$closeWindow = "<input type='button' class='button' value='".AUTOGAL_LANG_L25."' onClick='javascript:window.close()'>";
		
		if ($pref['autogal_slidesenable'])
		{
			if ($pref['autogal_slidesnewwindow'])
			{
				$slideShowURL = AUTOGAL_SLIDESHOW."?first=".rawurlencode($this->Element());
				$slideOnClickJS = AutoGal_NewWindowJS('slide', $slideShowURL, 'slideshow');
			}
			else
			{
				$slideShowURL = AUTOGAL_SLIDESHOW."?first=".htmlspecialchars($this->Element());
				$slideOnClickJS = "window.location.href=\"$slideShowURL\"";
			}
			
			$slideShow = "<input type='button' class='button' title=\"".AUTOGAL_LANG_SLIDESHOW_L2."\" value='".AUTOGAL_LANG_SLIDESHOW_L1."' onclick=\"javascript:$slideOnClickJS\" />";
		}
		
		$text = "$firstFile&#160;$prevFile&#160;".($showClose ? $closeWindow : $galleryLink).($showSlideShow ? "&#160;$slideShow" : '')."&#160;$nextFile&#160;$lastFile";
		return $text;
	}
		
	///////////////////////////////////////////////////////
	// Meta Get/Set Functions
	///////////////////////////////////////////////////////
	function MetaValue($name, $newVal)
	{
		$this->LoadMeta();
		
		$name = strtolower($name);
		
		if ((isset($newVal))&&($this->m_info[$name] != $newVal))
		{
			$this->IsXmlChange(true);
			$this->m_info[$name] = $newVal;
		}
		
		if (array_key_exists($name, $this->m_info))
		{
			return $this->m_info[$name];
		}
	}
	
	function Description($newVal)
	{
		return $this->MetaValue('description', $newVal);
	}
	
	function DescriptionBBCode()
	{
		return AutoGal_DoBBCode($this->Description());
	}
	
	function DescriptionStripBB()
	{
		$desc = preg_replace("/\[[\/\w\=\s]+\]/", '', $this->Description());
		return $desc;
	}
			
	function ViewHits($newVal)
	{
		return $this->MetaValue('viewhits', $newVal);
	}
	
	function ViewHitsInc()
	{
		return $this->ViewHits($this->ViewHits() + 1);
	}
	
	function EmailHits($hits)
	{
		return $this->MetaValue('emailhits', $newVal);
	}
	
	function EmailHitsInc()
	{
		return $this->EmailHits($this->EmailHits() + 1);
	}
	
	function SubmitByUsername($newVal)
	{
		return $this->MetaValue('submitbyusername', $newVal);
	}
	
	function SubmitByUserID($newVal)
	{
		return $this->MetaValue('submitbyuserid', $newVal);
	}
	
	function SubmitByUrl()
	{
		return e_BASE."user.php?id.".$this->SubmitByUserID();
	}
	
	function SubmitByLink()
	{
		return "<a href=\"".$this->SubmitByUrl()."\">".$this->SubmitByUsername()."</a>";
	}
	
	function SubmitDate($newVal)
	{
		return $this->MetaValue('submitdate', $newVal);
	}
	
	function SuggestedGallery($newVal)
	{
		return $this->MetaValue('suggestedgallery', $newVal);
	}
	
	function ViewWidth($newVal)
	{
		return $this->MetaValue('viewwidth', $newVal);
	}
	
	function ViewHeight($newVal)
	{
		return $this->MetaValue('viewheight', $newVal);
	}
	
	function SearchMatch($target, $matchStr)
	{
		if (isset($target)) $this->m_info['searchmatch'] = $target;
		if (isset($matchStr)) $this->m_info['searchmatchstr'] = $matchStr;
		
		return $this->m_info['searchmatch'];
	}
	
	function SearchMatchStr($string)
	{
		if (isset($string)) $this->m_info['searchmatchstr'] = $string;
		return $this->m_info['searchmatchstr'];
	}
	
	function UserClass($type, $newVal)
	{
		if ($this->IsFile())
		{
			$galObj = $this->GalleryMediaObj();
			return $galObj->UserClass($type, $newVal);
		}
		
		$isLoaded = false;
		if (AUTOGAL_USERCLASSCACHE)
		{
			global $g_agUserClasses;
			$element = $this->Element();
			
			if (!isset($g_agUserClasses))
			{
				$this->SpeedMsg("[$element] Loading user class cache...");
				$g_agUserClasses = AutoGal_LoadUserClassCache();
				
			}
			
			if (isset($g_agUserClasses[$element]))
			{
				$this->SpeedMsg("[$element] User clases are cached");
				foreach ($g_agUserClasses[$element] as $classType => $classValue)
				{
					
					if (is_numeric($classValue))
					{
						$this->SpeedMsg("[$element] User class $classType = $classValue");
						$isLoaded = 1;
						$this->m_info[$classType.'class'] = $classValue;
					}
				}
			}
		}
		
		if (!$isLoaded)
		{
			if (AUTOGAL_USERCLASSCACHE) $this->SpeedMsg("[$element] User clases NOT cached");
			$this->LoadMeta();
		}
	
		if (isset($newVal))
		{
			if ($this->m_info[$type.'class'] != $newVal)
			{
				$this->IsXmlChange(true);
				$this->m_info[$type.'class'] = $newVal;
			}
		}
		
		if (is_numeric($this->m_info[$type.'class']))
		{
			return $this->m_info[$type.'class'];
		}
			
		switch ($type)
		{
			case 'view':     return AUTOGAL_DEFAULTVIEWUC; break;
			case 'upload':   return AUTOGAL_DEFAULTUPLOADUC; break;
			case 'admin':    return AUTOGAL_DEFAULTADMINUC; break;
			case 'mcomment': return AUTOGAL_DEFAULTMCOMMENTUC; break;
			case 'gcomment': return AUTOGAL_DEFAULTGCOMMENTUC; break;
		}
	}
	
	function InheritFromParent($parentObj)
	{
		$userClasses = array('view', 'upload', 'admin', 'mcomment', 'gcomment');
		
		foreach ($userClasses as $userClass)
		{
			$this->UserClass($userClass, $parentObj->UserClass($userClass));
		}
	}
	
	function CheckUserClass($type)
	{
		return AutoGal_CheckUserClass($this->UserClass($type));
	}
	
	function CheckUserPriv($privName)
	{
		global $pref;
		if (AutoGal_IsMainAdmin()) return 1;
		
		switch ($privName)
		{
			case 'view':            $privOK = (($this->IsUserGalleryOwner())||($this->CheckUserClass('view'))); break;
			case 'filecomment':     $privOK = (($this->IsUserGalleryOwner())||($this->CheckUserClass('mcomment'))); break;
			case 'gallerycomment':  $privOK = (($this->IsUserGalleryOwner())||($this->CheckUserClass('gcomment'))); break;
			case 'comment':         $privOK = (($this->IsGallery() ? $this->CheckUserPriv('gallerycomment') : $this->CheckUserPriv('filecomment'))); break;
			case 'rename':          $privOK = $this->UserCanEdit(); break;
			case 'editdescription': $privOK = $this->UserCanEdit(); break;
			case 'editaccess':      $privOK = AutoGal_IsMainAdmin(); break;
			case 'clearcache':      $privOK = $this->UserCanEdit(); break;
			case 'regencache':      $privOK = AutoGal_IsMainAdmin(); break;
			case 'clearmeta':       $privOK = $this->UserCanEdit(); break;
			case 'creategallery':   $privOK = $this->UserCanEdit(); break;
			case 'delete':          $privOK = $this->UserCanEdit(); break;
			case 'rotate':          $privOK = $this->UserCanEdit(); break;
			case 'watermark':       $privOK = $this->UserCanEdit(); break;
			case 'autowatermark':   $privOK = AutoGal_IsMainAdmin(); break;
			case 'move':            $privOK = $this->UserCanEdit(); break;
			case 'setgallerythumb': $privOK = $this->UserCanEdit(); break;
			case 'setfilethumb':    $privOK = $this->UserCanEdit(); break;
			case 'setviewsize':     $privOK = $this->UserCanEdit(); break;
			case 'directupload':    $privOK = (($this->IsUserGalleryOwner())||($this->CheckUserClass('upload'))); break;
			case 'reviewupload':    $privOK = check_class($pref['autogal_revuploaduc']); break;
			case 'upload':          $privOK = (($this->CheckUserPriv('reviewupload'))||($this->CheckUserPriv('directupload'))); break;
			case 'review':          $privOK = check_class($pref['autogal_adminreviewuc']); break;
			case 'rating':          $privOK = check_class($pref['autogal_rateclass']); break;
			case 'addfile':         $privOK = $this->UserCanEdit(); break;
			case 'adminmenu':       $privOK = $this->UserCanEdit(); break;
			case 'commentadmin':    $privOK = $this->UserCanEdit(); break;
			case 'viewxml':         $privOK = $this->UserCanEdit(); break;
			default: die("Invalid priv '$privName'");
		}
		
		$privOK = ($privOK ? 1 : 0);
		
		#print "[".$this->Element()."] CHECK PRIV ($privName) = $privOK<br />";
		
		return $privOK;
	}
	
	function UserCanEdit()
	{
		if (AutoGal_IsMainAdmin()) return 1;
		return ($this->IsUserGalleryOwner()||(AutoGal_IsEditAllowed()));
	}
	
	function Comments()
	{
		return $this->MetaValue('comments');
	}
	
	function ClearComments()
	{
		if ($this->NumComments() > 0)
		{
			$this->m_info['comments'] = array();
			$this->IsXmlChange(true);
		}
	}
	
	function NumComments()
	{
		return count($this->Comments());
	}
	
	function LastCommentText()
	{
		if (!$this->NumComments()) return;
		$comments = $this->Comments();
		return $comments[$this->NumComments() - 1]['text'];
	}
		
	function AddComment($commentInfoOrText, $username, $userID, $time)
	{
		global $pref;
		if (is_array($commentInfoOrText))
		{
			$commentInfo = $commentInfoOrText;
		}
		else
		{
			if (!$time) $time = time();
			
			$commentInfo['text'] = $commentInfoOrText;
			$commentInfo['authorusername'] = $username;
			$commentInfo['authorid'] = $userID;
			$commentInfo['date'] = $time;
		}
		
		$this->m_info['comments'][] = $commentInfo;
	
		if ($pref['autogal_latestcomms'])
		{
			# SAVE TO THE LATEST COMMENT XML FILE
			require_once(AUTOGAL_LTSTCOMSHANDLER);
			$lComms = new AutoGal_LatestComms($pref['autogal_maxlatestcomms']);
			$lComms->LoadFile(AUTOGAL_LATESTCOMMSXML);
			
			$addComment = $commentInfo;
			$addComment['element'] = $this->Element();
			
			$lComms->AddCommentArray($addComment);
			
			if (!$lComms->SaveFile(AUTOGAL_LATESTCOMMSXML, AUTOGAL_PERMSCFGXML))
			{
				$errMsg = $lComms->GetLastError()."<br />".AutoGal_XMLFilePermsError(AUTOGAL_LATESTCOMMSXML);
				$ns->tablerender(AUTOGAL_LANG_XML_L4, "<div style='text-align:center'>$errMsg</div>");
			}
		}
		
		$this->IsXmlChange(true);
		usort($this->m_info['comments'], "AutoGal_CompareComments");
	}
	
	function DeleteComment($index)
	{
		if (($index < 0)||($index >= $this->NumComments())) return;
		unset($this->m_info['comments'][$index]);
		$this->IsXmlChange(true);
	}
	
	function Ratings()
	{
		return $this->MetaValue('ratings');
	}
	
	function ClearRatings()
	{
		if ($this->NumRatings() > 0)
		{
			$this->m_info['ratings'] = array();
			$this->IsXmlChange(true);
		}
	}
	
	function NumRatings()
	{
		return count($this->Ratings());
	}
	
	function AvgRating()
	{
		$ratings = $this->Ratings();
		
		foreach ($ratings as $rating)
		{
			$total += $rating['mark'];
		}
		
		return number_format($total / $this->NumRatings(), 2);
	}
	
	function RatingImages()
	{
		for ($starCnt = 1; $starCnt <= AUTOGAL_MAXRATE; $starCnt ++)
		{
			if ($starCnt <= floor($this->AvgRating()))
			{
				$text .= "<img src='".AUTOGAL_RATEIMAGEFULL."' style='border:0px;'>&nbsp;";
			}
			else if ($ratingVal >= ($starCnt - 0.5))
			{
				$text .= "<img src='".AUTOGAL_RATEIMAGEHALF."' style='border:0px;'>&nbsp;";
			}
			else
			{
				$text .= "<img src='".AUTOGAL_RATEIMAGEBLANK."' style='border:0px;'>&nbsp;";
			}
		}
		
		return $text;
	}
	
	function UserHasRated($userID, $username)
	{
		$ratings = $this->Ratings();
		
		foreach ($ratings as $rating)
		{
			if ($userID == 0)
			{
				if ($username == $rating['username']) return true;
			}
			else
			{
				if ($userID == $rating['userid']) return true;
			}
		}
		
		return false;
	}
	
	function AddRating($ratingInfoOrMark, $username, $userID, $time)
	{
		if (is_array($ratingInfoOrMark))
		{
			$ratingInfo = $ratingInfoOrMark;
		}
		else
		{
			if (!$time) $time = time();
			
			$ratingInfo['mark'] = $ratingInfoOrMark;
			$ratingInfo['username'] = $username;
			$ratingInfo['userid'] = $userID;
			$ratingInfo['date'] = $time;
		}
		
		$this->m_info['ratings'][] = $ratingInfo;
		$this->IsXmlChange(true);
	}
	
	function ArcadeTopScores()
	{
		global $pref;
		if ($this->FileType() != 'flash')
		{
			$this->LastError("ArcadeTopScores: Not a flash file!");
			return;
		}
		
		$this->LoadMeta();
		$scores = $this->m_info['arcade']['topscores'];
		
		usort($scores, "AutoGal_CmpArcadeScores");
		$scores = array_slice($scores, 0, $pref['autogal_arcmaxtopscores']);
		
		return $scores;
	}
	
	function ArcadeClearTopScores()
	{
		if ($this->ArcadeNumTopScores() > 0)
		{
			$this->m_info['arcade']['topscores'] = array();
			$this->IsXmlChange(true);
		}
	}
		
	function ArcadeNumTopScores()
	{
		return count($this->ArcadeTopScores());
	}
	
	function ArcadeAddTopScore($points, $userID, $username, $scoreTime)
	{
		global $pref;
		if ($this->FileType() != 'flash')
		{
			$this->LastError("ArcadeAddTopScore: Not a flash file!");
			return 0;
		}
		
		if (!$userID) $userID = USERID;
		if (!$username) $username = USERNAME;
		if (!$scoreTime) $scoreTime = time();
		
		$scores = $this->ArcadeTopScores();
		$numScores = count($scores);
		
		# Try and detect repeat score
		$lastUserScoreDate = 0;
		$lastUserScorePoints = 0;
		foreach ($scores as $score)
		{
			if (($score['username'] == $username)&&($score['date'] > $lastUserScoreDate))
			{
				$lastUserScoreDate = $score['date'];
				$lastUserScorePoints = $score['points'];
			}
		}
		
		if ($lastUserScorePoints == $points)
		{
			$this->LastError("ArcadeAddTopScore: Score seems to be repeated ($lastUserScorePoints == $points)!");
			return 0;
		}
		
		if (($numScores < $pref['autogal_arcmaxtopscores'])||($scores[$numScores - 1]['points'] < $points))
		{
			$scoreRec = array();
			$scoreRec['userid'] = $userID;
			$scoreRec['username'] = $username;
			$scoreRec['date'] = $scoreTime;
			$scoreRec['points'] = $points;
			$scores[] = $scoreRec;
			
			usort($scores, "AutoGal_CmpArcadeScores");
			$scores = array_slice($scores, 0, $pref['autogal_arcmaxtopscores']);
			$this->m_info['arcade']['topscores'] = $scores;
			
			$this->IsXmlChange(true);
		}
		
		return 1;
	}
	
    ///////////////////////////////////////////////////////
	// Error Functions
	///////////////////////////////////////////////////////
	function LastError($errorStr)
	{
		if (isset($errorStr))
		{
			$this->m_lastError = $errorStr;
			$this->DebugMsg("ERROR: $errorStr");
		}
		
		return $this->m_lastError;
	}
	
	function IsError()
	{
		return ($this->m_lastError ? true : false);
	}
	
	function ClearError()
	{
		$this->m_lastError = '';
	}
	
	function SpeedMsg($msg)
	{
		if (!$this->m_printSpeedMsgs) return;
		print "$msg<br />";
	}
	
	function DebugMsg($msg)
	{
		if (!$this->m_printDebugMsgs) return;
		print "$msg<br />";
	}
	
	///////////////////////////////////////////////////////
	// XML Functions
	///////////////////////////////////////////////////////
	function LoadMeta()
	{
		if ($this->IsXmlLoaded()) 
		{
			$this->SpeedMsg("LoadMeta: Already loaded");
			return true;
		}
			
		if (!$this->XmlFileExists())
		{
			$this->m_xmlLoaded = true;
			$this->SpeedMsg("LoadMeta: File does not exist");
			return false;
		}
		
		$file = $this->XmlFilePath();
		$this->m_xmlLoaded = true;
		return $this->XmlLoadFile($file);
	}
	
	function SaveMeta()
	{
		if (!$this->IsXmlLoaded()) 
		{
			$this->SpeedMsg("SaveMeta: Not loaded");
			return true;
		}
		
		if (!$this->IsXmlChange())
		{
			$this->SpeedMsg("SaveMeta: No Change");
			return true;
		}
		
		$file = $this->XmlFilePath();
		if ($this->XmlSaveFile($file))
		{
			$this->IsXmlChange(false);
			return true;
		}
		
		return false;
	}
	
	function IsXmlLoaded()
	{
		return $this->m_xmlLoaded;
	}
	
	function IsXmlChange($isChange)
	{
		if (isset($isChange)) $this->m_xmlIsChange = $isChange;
		return $this->m_xmlIsChange;
	}

	///////////////////////////////////////////////////////
	// XML Functions
	///////////////////////////////////////////////////////
	function XmlFilePath()
	{
		if (isset($this->m_info['xmlfilepath'])) return $this->m_info['xmlfilepath'];
		
		if ($this->IsGallery())
		{
			$this->m_info['xmlfilepath'] = $this->AbsPath()."/".AUTOGAL_GALLERYXMLFILENAME;
		}
		else
		{
			$this->m_info['xmlfilepath'] = $this->AbsPath().".xml";
		}

		return $this->m_info['xmlfilepath'];
	}
	
	function XmlFileElePath()
	{
		if ($this->IsRoot())
		{
			return "Gallery.xml";
		}
		else
		{
			return ($this->IsInRoot() ? '' : dirname($this->Element())."/").basename($this->XmlFilePath());
		}
	}
	
	function XmlFileExists()
	{
		if (isset($this->m_info['xmlfileexists'])) return $this->m_info['xmlfileexists'];
		
		$this->SpeedMsg("XmlFileExists: is_readable(".$this->XmlFilePath().")");
		if (is_readable($this->XmlFilePath()))
		{
			$this->m_info['xmlfileexists'] = 1;
		}
		else
		{
			$this->m_info['xmlfileexists'] = 0;
		}
		
		return $this->m_info['xmlfileexists'];
	}
	
	function XmlLoadFile($fileName)
	{
		$this->m_xmlParser = NULL;
		$this->m_xmlCurrentTag = '';
		$this->m_currComment = array();
		$this->m_xmlCurrGroup = array();
				
		// CHECK IF XML PARSER EXISTS
		if(!function_exists('xml_parser_create'))
		{
			$this->LastError(AUTOGAL_LANG_METACLASS_L1);
			return false;
		}
		
		// READ FILE IN
		$this->SpeedMsg("XmlLoadFile: Open [r] $fileName");
		$HANDLE = fopen($fileName, "r");
		
		if (!$HANDLE)
		{
			$this->LastError(str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L3));
			return false;
		}
		
		$fileLen = filesize($fileName);
		if ($fileLen <= 0)
		{
			$this->SpeedMsg("XmlLoadFile: $fileName is zero-length");
			return 0;
		}
		
		$xmlData = fread($HANDLE, $fileLen);
		
		if (!$xmlData)
		{
			$this->LastError(str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L4));
			return false;
		}
		
		fclose($HANDLE);
		
		// START XML PARSER
		$this->m_xmlParser = xml_parser_create('');
		xml_set_object($this->m_xmlParser, $this);
		xml_set_element_handler($this->m_xmlParser, 'XmlStartElement', 'XmlEndElement');
		xml_set_character_data_handler($this->m_xmlParser, 'XmlCharacterData'); 
		xml_parse($this->m_xmlParser, $xmlData);
		
		xml_parser_free($this->m_xmlParser );
		
		// DECODE DATES
		foreach ($this->m_info as $field => $val)
		{
			if ((!is_array($val))&&(preg_match("/date$/", $field)))
			{
				$this->m_info[$field] = $this->DecodeDate($val);
			}
		}
			
		// SORT THE COMMENTS
		usort($this->m_info['comments'], "AutoGal_CompareComments");

		return true;
	}
	
	function XmlStartElement($parser, $element, &$attrs)
	{
		$element = strtolower($element);
		$currGroup = $this->m_xmlCurrGroup[count($this->m_xmlCurrGroup) - 1];
		
		$this->DebugMsg("START $element");
		
		if ($element == 'autogalmeta')
		{
			$this->m_xmlCurrGroup[] = 'autogalmeta';
		}
		elseif ($currGroup == 'autogalmeta')
		{
			if ($element == 'comments')
			{
				$this->m_xmlCurrGroup[] = 'comments';
			}
			else if ($element == 'arcade')
			{
				$this->m_xmlCurrGroup[] = 'arcade';
			}
			else if ($element == 'ratings')
			{
				$this->m_xmlCurrGroup[] = 'ratings';
			}
		}
		elseif ($currGroup == 'comments')
		{
			if ($element == 'post')
			{
				$this->m_xmlCurrGroup[] = 'post';
			}
		}
		elseif ($currGroup == 'arcade')
		{
			if ($element == 'topscores')
			{
				$this->m_xmlCurrGroup[] = 'topscores';
			}
		}
		elseif ($currGroup == 'topscores')
		{
			if ($element == 'score')
			{
				$this->m_xmlCurrGroup[] = 'score';
			}
		}
		elseif ($currGroup == 'ratings')
		{
			if ($element == 'rating')
			{
				$this->m_xmlCurrGroup[] = 'rating';
			}
		}
		else
		{
			$this->DebugMsg("START BAD: $element (Group: $currGroup)");
		}
						
		$this->m_xmlCurrentTag = $element;
	}
		
	function XmlEndElement($parser, $element)
	{
		if (count($this->m_xmlCurrGroup) <= 0) return;
		
		$element = strtolower($element);
		$currGroup = $this->m_xmlCurrGroup[count($this->m_xmlCurrGroup) - 1];
		
		$this->DebugMsg("END $element (group $currGroup)");
			
		if ($element == $currGroup)
		{
			array_pop($this->m_xmlCurrGroup);
			
			if ($element == 'post')
			{
				$this->m_xmlCurrGroupData['date'] = $this->DecodeDate($this->m_xmlCurrGroupData['date']);
				if ($this->m_xmlCurrGroupData['date'] >= 0)
				{
					$this->m_info['comments'][] = $this->m_xmlCurrGroupData;
				}
			}
			elseif ($element == 'rating')
			{
				$this->m_xmlCurrGroupData['date'] = $this->DecodeDate($this->m_xmlCurrGroupData['date']);
				if ($this->m_xmlCurrGroupData['date'] > 0)
				{
					$this->m_info['ratings'][] = $this->m_xmlCurrGroupData;
				}
			}
			elseif ($element == 'score')
			{
				$this->m_xmlCurrGroupData['date'] = $this->DecodeDate($this->m_xmlCurrGroupData['date']);
				if ($this->m_xmlCurrGroupData['date'] > 0)
				{
					$this->m_info['arcade']['topscores'][] = $this->m_xmlCurrGroupData;
				}
			}
			$this->m_xmlCurrGroupData = array();
		}
			
		$this->m_xmlCurrentTag = "";
	}

	function XmlCharacterData ($parser, $data)
	{
		if (count($this->m_xmlCurrGroup) <= 0) return;
		
		$this->m_xmlCurrentTag = strtolower($this->m_xmlCurrentTag);
		$currGroup = $this->m_xmlCurrGroup[count($this->m_xmlCurrGroup) - 1];
		
		$this->DebugMsg("DATA ".$this->m_xmlCurrentTag."/".$currGroup."($data)");
		
		if ($currGroup == 'post')
		{
			// COMMENT POST
			if ($this->m_xmlCurrentTag == 'text')
			{
				$this->m_xmlCurrGroupData['text'] .= $data;
			}
			elseif ($this->m_xmlCurrentTag == 'date')
			{
				$this->m_xmlCurrGroupData['date'] .= $data;
			}
			elseif ($this->m_xmlCurrentTag == 'authorid')
			{
				$this->m_xmlCurrGroupData['authorid'] .= $data;
			}
			elseif ($this->m_xmlCurrentTag == 'authorusername')
			{
				$this->m_xmlCurrGroupData['authorusername'] .= $data;
			}
		}
		else if ($currGroup == 'rating')
		{
			// RATING
			if ($this->m_xmlCurrentTag == 'userid')
			{
				$this->m_xmlCurrGroupData['userid'] .= $data;
			}
			elseif ($this->m_xmlCurrentTag == 'username')
			{
				$this->m_xmlCurrGroupData['username'] .= $data;
			}
			elseif ($this->m_xmlCurrentTag == 'date')
			{
				$this->m_xmlCurrGroupData['date'] .= $data;
			}
			elseif ($this->m_xmlCurrentTag == 'mark')
			{
				$this->m_xmlCurrGroupData['mark'] .= $data;
			}
		}
		else if ($currGroup == 'score')
		{
			// ARCADE SCORE
			if ($this->m_xmlCurrentTag == 'userid')
			{
				$this->m_xmlCurrGroupData['userid'] .= $data;
			}
			elseif ($this->m_xmlCurrentTag == 'username')
			{
				$this->m_xmlCurrGroupData['username'] .= $data;
			}
			elseif ($this->m_xmlCurrentTag == 'date')
			{
				$this->m_xmlCurrGroupData['date'] .= $data;
			}
			elseif ($this->m_xmlCurrentTag == 'points')
			{
				$this->m_xmlCurrGroupData['points'] .= $data;
			}
		}
		elseif (in_array($this->m_xmlCurrentTag, explode('|', AUTOGAL_XMLALLOWEDTAGS)))
		{
			$this->m_info[$this->m_xmlCurrentTag] .= $data;
		}
	}
	
	function XmlSaveFile($fileName, $perms=777)
	{
		// BUILD XML META DATA
		$xmlData .=
		"<?xml version=\"1.0\" encoding=\"".AUTOGAL_CHARSET."\"?>\n\n".
		"<!-- Auto Gallery (www.cerebralsynergy.com) XML Meta File -->\n\n".
		"<autogalmeta>\n";
		
		// WRITE OUT NORMAL TAGS
		if ($this->m_printDebugMsgs)
		{	
			AutoGal_Dump($this->m_info);
		}
		
		$saveTags = explode('|', AUTOGAL_XMLALLOWEDTAGS);	
		foreach ($saveTags as $tagName)
		{
			if (!array_key_exists($tagName, $this->m_info)) continue;
			
			$tagValue = $this->m_info[$tagName];

			$this->DebugMsg("SAVE: $tagName => $tagValue");
			
			if (is_array($tagValue))
			{
				$xmlData .= "<$tagName>\n";
				foreach ($tagValue as $subTagName => $subTagValue)
				{
					$xmlData .= "\t<$subTagName>".htmlspecialchars($subTagValue)."</$subTagName>\n";
				}
				$xmlData .= "</$tagName>\n";
			}
			else
			{
				if (preg_match("/date$/", $tagName))
				{
					$xmlData .= "<$tagName>".htmlspecialchars(strftime("%H:%M:%S %d-%b-%Y", $tagValue))."</$tagName>\n";
				}
				else
				{
					$xmlData .= "<$tagName>".htmlspecialchars($tagValue)."</$tagName>\n";
				}
			}
		}
		
		// WRITE OUT COMMENTS
		if (count($this->m_info['comments']) > 0)
		{
			$xmlData .= "<comments>\n";
			
			foreach ($this->m_info['comments'] as $commentI => $comment)
			{
				$xmlData .= "\t<post>\n";
				$xmlData .= "\t\t<authorid>".htmlspecialchars($comment['authorid'])."</authorid>\n";
				$xmlData .= "\t\t<authorusername>".htmlspecialchars($comment['authorusername'])."</authorusername>\n";
				$xmlData .= "\t\t<date>".htmlspecialchars(strftime("%H:%M:%S %d-%b-%Y", $comment['date']))."</date>\n";
				$xmlData .= "\t\t<text>".htmlspecialchars($comment['text'])."</text>\n";
				$xmlData .= "\t</post>\n";
			}
			
			$xmlData .= "</comments>\n";
		}
		
		if (count($this->m_info['ratings']) > 0)
		{
			$xmlData .= "<ratings>\n";
			
			foreach ($this->m_info['ratings'] as $ratingI => $rating)
			{
				$xmlData .= "\t<rating>\n";
				$xmlData .= "\t\t<userid>".htmlspecialchars($rating['userid'])."</userid>\n";
				$xmlData .= "\t\t<username>".htmlspecialchars($rating['username'])."</username>\n";
				$xmlData .= "\t\t<date>".htmlspecialchars(strftime("%H:%M:%S %d-%b-%Y", $rating['date']))."</date>\n";
				$xmlData .= "\t\t<mark>".htmlspecialchars($rating['mark'])."</mark>\n";
				$xmlData .= "\t</rating>\n";
			}
			
			$xmlData .= "</ratings>\n";
		}
		
		// WRITE OUT ARCADE DATA
		if (count($this->m_info['arcade']) > 0)
		{
			$xmlData .= "<arcade>\n";
			
			// TOP SCORES
			if (count($this->m_info['arcade']['topscores']) > 0)
			{
				$xmlData .= "\t<topscores>\n";
			
				foreach ($this->m_info['arcade']['topscores'] as $scoreI => $score)
				{
					$xmlData .= "\t\t<score>\n";
					$xmlData .= "\t\t\t<userid>".htmlspecialchars($score['userid'])."</userid>\n";
					$xmlData .= "\t\t\t<username>".htmlspecialchars($score['username'])."</username>\n";
					$xmlData .= "\t\t\t<date>".htmlspecialchars(strftime("%H:%M:%S %d-%b-%Y", $score['date']))."</date>\n";
					$xmlData .= "\t\t\t<points>".htmlspecialchars($score['points'])."</points>\n";
					$xmlData .= "\t\t</score>\n";
				}
			
				$xmlData .= "\t</topscores>\n";
			}
			
			$xmlData .= "</arcade>\n";
		}
		
		$xmlData .= "</autogalmeta>\n";
			
		// TOUCH AND CHMOD XML FILE IF NEW
		$this->SpeedMsg("XmlSaveFile: file_exists($fileName)");
		if (!file_exists($fileName))
		{
			$this->SpeedMsg("XmlSaveFile: touch($fileName)");
			touch($fileName);
			
			$this->SpeedMsg("XmlSaveFile: chmod($fileName, $perms)");
			chmod($fileName, octdec($perms));
		}
		
		// WRITE XML TO FILE
		#print "FOPEN($fileName, w+)<br />";
		$this->SpeedMsg("XmlSaveFile: Open [w] $fileName");
		$HANDLE = fopen($fileName, "w+");
				
		if (!$HANDLE)
		{
			$this->LastError(str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L5));
			flock($HANDLE, LOCK_UN);
			return false;
		}
		
		flock($HANDLE, LOCK_EX);
		if (fwrite($HANDLE, $xmlData) === false)
		{
			$this->LastError(str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L6));
			flock($HANDLE, LOCK_UN);
			return false;
		}
		
		flock($HANDLE, LOCK_UN);
		fclose($HANDLE);
		
		$this->SpeedMsg("XmlSaveFile: file_exists($fileName)");
		if (!file_exists($fileName))
		{
			$this->LastError(str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L8));
			return false;
		}
		
		/*if (filesize($fileName) <= 0)
		{
			$this->LastError(str_replace('[FILE]', $fileName, AUTOGAL_LANG_METACLASS_L9));
			return false;
		}*/
		
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
	
	function DecodeMySqlDate($dateStr)
	{
		if (preg_match("/^\s*(\d+)\-(\d+)\-(\d+)\s+(\d+)\:(\d+)\:(\d+)\s*$/", $dateStr, $dBits))
		{
			return mktime($dBits[4], $dBits[5], $dBits[6], $dBits[2], $dBits[3], $dBits[1]);
		}
		
		return 0;
	}
	
}

if (!function_exists('AutoGal_CompareComments'))
{
	function AutoGal_CompareComments($a, $b)
	{
		if ($a['date'] > $b['date']) return 1;
		if ($a['date'] < $b['date']) return -1;
		return 0;
	}
}

if (!function_exists('AutoGal_CmpArcadeScores'))
{
	function AutoGal_CmpArcadeScores($a, $b)
	{
		if ($a['points'] == $b['points'])
		{
			if ($a['date'] == $b['date']) return 0;
					
			return ($a['date'] > $b['date']) ? -1 : 1;
		}
		
		return ($a['points'] > $b['points']) ? -1 : 1;
	}
}
?>