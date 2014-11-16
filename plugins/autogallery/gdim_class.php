<?php
/*********************************************************************************************
 * GDIM - GD/ImageMagick wrapper class
 *
 * VERSION:     1.00
 * DESCRIPTION: Various image processing functions that can use either GD or image magick
 *              to work, based on the mode set my user. Intended for use by AutoGallery.
 * WRITTEN BY:  Matthew Hart (www.cerebralsynergy.com)
 * DATE:        11-04-2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

#//

class GDIM
{
	var $m_lastError;
	var $m_mode;
	var $m_imPath;
	var $m_gdVer;
	var $m_currImgType;
	var $m_quality;
	var $m_prevDir;
	
	# Constructor
	function GDIM($mode='', $imPath='', $imQuality=0)
	{
		$this->m_mode = 'gd';
		$this->m_gdVer = 2;
		$this->m_quality = 75;
		
		if ($mode) $this->setMode($mode);
		if ($imPath) $this->setImPath($imPath);
		if ($imQuality) $this->setQuality($imQuality);
	}
	
	# Sets the mode (either gd1, gd2 or imagemagick)
	function setMode($mode)
	{
		$mode = trim(strtolower($mode));
		
		if (preg_match("/^gd([12])$/", $mode, $verBits))
		{
			if (!function_exists('gd_info'))
			{
				$this->m_lastError = "(setMode) The 'gd_info' function cannot be found! (Is GD installed?)"; 
				return 0;
			}
			
			if (!extension_loaded('gd'))
			{
				$this->m_lastError = "(setMode) GD library not loaded!";    
				return 0;
			}
			
			$this->m_mode = 'gd';
			$this->m_gdVer = $verBits[1];
		}
		else if (preg_match("/^(imagemagick?|im)$/", $mode))
		{
			$this->m_mode = 'im';
			$this->m_gdVer = 0;
		}
		else
		{
			$this->m_lastError = "(setMode) Invalid Mode: $mode";
			return 0;
		}
		
		return 1;
	}
	
	# Sets the path to Imagemagick directory (where convert, compose, mogrify etc. liv)
	function setImPath($path)
	{
		$path = str_replace('&#092;', '/', $path);
		$path = str_replace('&#92;', '/', $path);
		
		# Just incase path to one of the Imagemagick binaries is set
		if (!is_dir($path))
		{
			$path = str_replace("\\", "/", $path);
			$path = dirname($path);
		}
	
		$this->m_imPath = $path;
	
		return 1;
	}
	
	# Sets the image quality for jpegs/pngs
	function setQuality($quality)
	{
		if (($quality > 0)&&($quality <= 100))
		{
			$this->m_quality = $quality;
			return 1;
		}
		
		$this->m_lastError = "(setQuality) Quality must be > 0 and <= 100";
		return 0;
	}
	
	# Returns the last error message string
	function lastError()
	{
		return $this->m_lastError;
	}
	
	# Watermarks $targetImg with image $watermarkImage and saves the result to $destImg.
	# Options are defined by $opts array:
	# intesity: Value from 1 (transparent) to 100 (opaque/solid)
	# xalign: How watermark is aligned on the x-axis (l = left, c = center, r = right)
	# yalign: How watermark is aligned on the y-axis (t = top, m = middle, b = bottom)
	# xoffset: Number of pixels to push watermark to the left (can be + or -)
	# yoffset: Number of pixels to push watermark to the bottom (can be + or -)
	# nosmall: If set to 1, will only watermark images with size > than watermark image size
	function watermark($targetImg, $destImg, $watermarkImg, $opts=null)
	{
		# SET DEFAULT OPTIONS
		if (!$opts['intensity']) $opts['intensity'] = 100;
		if (!$opts['xalign']) $opts['xalign'] = 'r';
		if (!$opts['yalign']) $opts['yalign'] = 'b';
		if (!$opts['xoffset']) $opts['xoffset'] = 0;
		if (!$opts['yoffset']) $opts['yoffset'] = 0;
		if (!isset($opts['nosmall'])) $opts['nosmall'] = 0;
		
		if (!file_exists($targetImg))
		{
			$this->m_lastError = "(watermark) File does not exist: '$targetImg'!";
			return 0;
		}
		
		if (!file_exists($watermarkImg))
		{
			$this->m_lastError = "(watermark) File does not exist: '$watermarkImg'!";
			return 0;
		}
		
		if ($this->m_mode == 'im')
		{
			return $this->im_watermark($targetImg, $destImg, $watermarkImg, $opts);
		}
		else
		{
			return $this->gd_watermark($targetImg, $destImg, $watermarkImg, $opts);
		}
	}
	
	# Uses GD to watermark $targetImg with image $watermarkImage and save result to $destImg.
	# Options are defined by $opts array. See function 'watermark' for details.
	function gd_watermark($targetImg, $destImg, $watermarkImg, $opts=null)
	{
		# SET DEFAULT OPTIONS
		if (!$opts['intensity']) $opts['intensity'] = 100;
		if (!$opts['xalign']) $opts['xalign'] = 'r';
		if (!$opts['yalign']) $opts['yalign'] = 'b';
		if (!$opts['xoffset']) $opts['xoffset'] = 0;
		if (!$opts['yoffset']) $opts['yoffset'] = 0;
		if (!isset($opts['nosmall'])) $opts['nosmall'] = 0;
		
		# GET TARGET IMAGE TYPES
		$tarImgStats = getimagesize($targetImg);
		if (!$tarImgStats)
		{
			$this->m_lastError = "(gd_watermark) Image '$targetImg' invalid!";
			return 0;
		}
		
		$tarImageType = $tarImgStats[2];
		
		# GET WATERMARK IMAGE TYPES
		$wmImgStats = getimagesize($watermarkImg);
		if (!$wmImgStats)
		{
			$this->m_lastError = "(gd_watermark) Image '$watermarkImg' invalid!";
			return 0;
		}
				
		$wmImageType = $wmImgStats[2];
		
		# GET READ IN IMAGES
		$gdWatermarkImg = $this->gd_open($watermarkImg, $wmImageType);
		if (!$gdWatermarkImg)
		{
			$this->m_lastError = "(gd_watermark) Image type for '$watermarkImg' not supported!";
			return 0;
		}
	
		$gdTargetImg = $this->gd_open($targetImg, $tarImageType);
		if (!$gdTargetImg)
		{
			$this->m_lastError = "(gd_watermark) Image type for '$targetImg' not supported!";
			return 0;
		}
		
		# GET IMAGE SIZES
		$wmWidth = imagesx($gdWatermarkImg);  
		$wmHeight = imagesy($gdWatermarkImg);
		$tarWidth = imagesx($gdTargetImg);  
		$tarHeight = imagesy($gdTargetImg);
		
		# WORK OUT X/Y POSITION FOR WATERMARK
		switch ($opts['xalign'])
		{
			case 'l': $wmXPos = 0; break;
			case 'c': $wmXPos = ($tarWidth / 2) - ($wmWidth / 2); break;
			case 'r': $wmXPos = $tarWidth - $wmWidth; break;
		}
	
		switch ($opts['yalign'])
		{
			case 't': $wmYPos = 0; break;
			case 'm': $wmYPos = ($tarHeight / 2) - ($wmHeight / 2); break;
			case 'b': $wmYPos = $tarHeight - $wmHeight; break;
		}
			
		$wmXPos += $opts['xoffset'];
		$wmYPos += $opts['yoffset'];
		
		$wmXPos = floor($wmXPos);
		$wmYPos = floor($wmYPos);
	
		$tooBig = 0;
		if (($opts['nosmall'])&&(($wmWidth > $tarWidth)||($wmHeight > $tarHeight))) $tooBig = 1;

		if (!$tooBig)
		{
			# MAKE THE WATERMARK
			if (!$res = imagecopymerge($gdTargetImg, $gdWatermarkImg, $wmXPos, $wmYPos, 0, 0, $wmWidth, $wmHeight, $opts['intensity']))
			{
				$this->m_lastError = "(gd_watermark) imagecopymerge returned status '$res'!";
				return 0;
			}
		}
		
		# WRITE THE IMAGE TO THE FILE
		if ($destImg)
		{
			if (!$this->gd_write($gdTargetImg, $destImg, $tarImageType))
			{
				$this->m_lastError = "(gd_watermark) Error writting to image '$destImg', type '$tarImageType'!";
				return 0;
			}
		}
		else
		{
			if (!$this->gd_disp($gdTargetImg, $tarImageType))
			{
				$this->m_lastError = "(gd_watermark) Error writting to image '$destImg', type '$tarImageType'!";
				return 0;
			}
		}
				
		# FREE IMAGES
		imagedestroy($gdTargetImg);
		imagedestroy($gdWatermarkImg);
		
		return 1;
	}
	
	# Uses ImageMagick to watermark $targetImg with image $watermarkImage and save result to $destImg.
	# Options are defined by $opts array. See function 'watermark' for details.
	function im_watermark($targetImg, $destImg, $watermarkImg, $opts=null)
	{
		# SET DEFAULT OPTIONS
		if (!$opts['intensity']) $opts['intensity'] = 100;
		if (!$opts['xalign']) $opts['xalign'] = 'r';
		if (!$opts['yalign']) $opts['yalign'] = 'b';
		if (!$opts['xoffset']) $opts['xoffset'] = 0;
		if (!$opts['yoffset']) $opts['yoffset'] = 0;
		if (!isset($opts['nosmall'])) $opts['nosmall'] = 0;
		
		if (!$destImg) $destImg = '-';
		
		# GET TARGET IMAGE TYPES
		$tarImgStats = getimagesize($targetImg);
		if (!$tarImgStats)
		{
			$this->m_lastError = "(im_watermark) Image '$targetImg' invalid!";
			return 0;
		}
			
		# GET WATERMARK IMAGE TYPES
		$wmImgStats = getimagesize($watermarkImg);
		if (!$wmImgStats)
		{
			$this->m_lastError = "(im_watermark) Image '$watermarkImg' invalid!";
			return 0;
		}
			
		# TEST IF THE WATERMARK IMAGE SIZE > TARGET IMAGE SIZE
		if (($opts['nosmall'])&&(($wmImgStats[0] > $tarImgStats[0])||($wmImgStats[1] > $tarImgStats[1])))
		{
			if ($destImg == '-')
			{
				$this->disp($targetImg);
				return 1;
			}
			else
			{
				if (!copy($targetImg, $destImg))
				{
					$this->m_lastError = "(im_watermark) Copy from '$targetImg' to '$destImg' failed!";
					return 0;
				}
				
				return 1;
			}
		}
		
		# CHANGE TO THE IMAGEMAGICK DIR
		$this->im_chdir(1);
	
		# GET THE GRAVITY
		$xOffsetInv = 0;
		$yOffsetInv = 0;
		if ($opts['yalign'] == 't')
		{
			switch ($opts['xalign'])
			{
				case 'l': $gravity = 'NorthWest'; break;
				case 'c': $gravity = 'North'; break;
				case 'r': $gravity = 'NorthEast'; $xOffsetInv = 1; break;
			}
		}
		else if ($opts['yalign'] == 'm')
		{
			switch ($opts['xalign'])
			{
				case 'l': $gravity = 'West'; break;
				case 'c': $gravity = 'Center'; break;
				case 'r': $gravity = 'East'; $xOffsetInv = 1; break;
			}
		}
		else if ($opts['yalign'] == 'b')
		{
			switch ($opts['xalign'])
			{
				case 'l': $gravity = 'SouthWest'; break;
				case 'c': $gravity = 'South'; break;
				case 'r': $gravity = 'SouthEast'; $xOffsetInv = 1; break;
			}
			
			$yOffsetInv = 1;
		}
		
		if (!$gravity) $gravity = 'SouthEast';
		
		if ($xOffsetInv)
		{
			if ($opts['xoffset'] >= 0) 
				$opts['xoffset'] = '-'.$opts['xoffset'];
			else
				$opts['xoffset'] = '+'.abs($opts['xoffset']);
		}
		else
		{
			if ($opts['xoffset'] >= 0) $opts['xoffset'] = '+'.$opts['xoffset'];
		}
				
		if ($yOffsetInv)
		{
			if ($opts['yoffset'] >= 0) 
				$opts['yoffset'] = '-'.$opts['yoffset'];
			else
				$opts['yoffset'] = '+'.abs($opts['yoffset']);
		}
		else
		{
			if ($opts['yoffset'] >= 0) $opts['yoffset'] = '+'.$opts['yoffset'];
		}
		
		# BUILD COMPOSITE COMMAND
		$args = array('-gravity', $gravity, '-dissolve', $opts['intensity'], '-geometry', $opts['xoffset'].$opts['yoffset'], $watermarkImg, $targetImg, $destImg);
		$args = $this->escArgs($args);
		$argStr = implode(' ', $args);
		$cmd = "composite $argStr";
		
		# RUN THE COMPOSITE COMMAND
		if ($destImg == '-')
		{
			$pathInfo = pathinfo($targetImg);
			$ext = $pathInfo['extension'];
			if ($ext == 'jpg') $ext = 'jpeg';
			
			$this->writeImgHeaders($ext);
			
			passthru($cmd);
		}
		else
		{
			exec($cmd);
		}
		
		# CHANGE BACK THE DIR IF WE CHANGED IT BEFORE
		$this->im_chdir(0);
		
		return 1;
	}
	
	function writeImgHeaders($ext, $len=0)
	{
		if ($ext == 'jpg') $ext = 'jpeg';
		
		header("Last-Modified: ".date('r')); 
		header('Content-Transfer-Encoding: binary');
		header("Content-Disposition: inline; filename=image.$ext");
		header("Last-Modified: ".date('r'));
		header("Content-type: image/$ext");
		if ($len > 0) header("Content-length: ".$len);
	}
	
	# Writes GD image data $gdImg of type $imageType (integer returned from getimagesize, index 2) to file $toFile  
	function gd_write($gdImg, $toFile, $imageType)
	{
		switch ($imageType)
		{
			case 1: touch($toFile); return imagegif($gdImg, $toFile); 
			case 2: touch($toFile); return imagejpeg($gdImg, $toFile, $this->m_quality);
			case 3: touch($toFile); return imagepng($gdImg, $toFile);
			case 15: touch($toFile); return imagwbmp($gdImg, $toFile);
			case 16: touch($toFile); return imagexbm($gdImg, $toFile);
		}
		
		$this->m_lastError = "(gd_imgWrite) Image type '$imageType' not supported";
		return 0;
	}
	
	function gd_disp($gdImg, $imageType)
	{
		switch ($imageType)
		{
			case 1: $this->writeImgHeaders('gif');	return imagegif($gdImg); 
			case 2: $this->writeImgHeaders('jpg');	return imagejpeg($gdImg);
			case 3: $this->writeImgHeaders('png');	return imagepng($gdImg);
			case 15: $this->writeImgHeaders('bmp'); return imagwbmp($gdImg);
			case 16: $this->writeImgHeaders('xbm'); return imagexbm($gdImg);
		}
		
		$this->m_lastError = "(gd_imgWrite) Image type '$imageType' not supported";
		return 0;
	}
	
	# Opens and reads in image file $image of type $imageType. $imageType is worked out automatically if 0.
	function gd_open($image, $imageType=0)
	{
		if (!$imageType)
		{
			$imgStats = getimagesize($image);
			
			if (!$imgStats) 
			{
				$this->m_lastError = "(gd_open) getimagesize returned null";
				return 0;
			}
			
			$imageType = $imgStats[2];
		}
			
		$GDInfo = gd_info();
		$GIFSupport = ($GDInfo['GIF Read Support'] && $GDInfo['GIF Create Support']);
		$JPGSupport = $GDInfo['JPG Support'];
		$PNGSupport = $GDInfo['PNG Support'];
		$WBMPSupport = $GDInfo['WBMP Support'];;
		$XBMSupport = $GDInfo['XBM Support'];;
	
		if (($imageType == 1)&&($GIFSupport))
		{
			$srcImg = imagecreatefromgif($image);
			if (!$srcImg) $this->m_lastError = "(gd_open) imagecreatefromgif returned null";
		}
		elseif(($imageType == 2)&&($JPGSupport))
		{
			$srcImg = imagecreatefromjpeg($image);
			if (!$srcImg) $this->m_lastError = "(gd_open) imagecreatefromjpeg returned null";
		}
		elseif(($imageType == 3)&&($PNGSupport))
		{
			$srcImg = imagecreatefrompng($image);
			if (!$srcImg) $this->m_lastError = "(gd_open) imagecreatefrompng returned null";
		}
		elseif(($imageType == 15)&&($WBMPSupport))
		{
			$srcImg = imagecreatefromwbmp($image);
			if (!$srcImg) $this->m_lastError = "(gd_open) imagecreatefromwbmp returned null";
		}
		elseif(($imageType == 16)&&($XBMSupport))
		{
			$srcImg = imagecreatefromxbm($image);
			if (!$srcImg) $this->m_lastError = "(gd_open) imagecreatefromxbm returned null";
		}
		else
		{
			$this->m_lastError = "(gd_open) Image type '$imageType' not supported";
			return 0;
		}
	
		return $srcImg;
	}
	
	function disp($targetImg)
	{
		$imageInfo = pathinfo($targetImg);
		$ext = strtolower($imageInfo['extension']);
		$imgData = file_get_contents($targetImg);
		
		$this->writeImgHeaders($ext, strlen($imgData));
		
		print $imgData; 
	}
	
	# Resizes image $targetImg to dimensions x=$resizeWidth, y=$resizeHeight and write it to $destImg.
	# If $keepAspect == 1 aspect ratio is keeped, otherwise dimensions are forced.
	function resize($targetImg, $destImg, $resizeWidth, $resizeHeight, $opts)
	{
		# SET DEFAULT OPTIONS
		if (!isset($opts['keepaspect'])) $opts['keepaspect'] = 1;
		if (!isset($opts['iflarger'])) $opts['iflarger'] = 0;
		if (!isset($opts['1stframe'])) $opts['1stframe'] = 1;
		if (!isset($opts['perms'])) $opts['perms'] = 644;
		
		if (!preg_match("/^[0-9]+$/", $resizeWidth))
		{
			$this->m_lastError = "(resize) Invalid resize width '$resizeWidth'!";
			return 0;
		}
		
		if (!preg_match("/^[0-9]+$/", $resizeHeight))
		{
			$this->m_lastError = "(resize) Invalid resize height '$resizeHeight'!";
			return 0;
		}
		
		if (!file_exists($targetImg))
		{
			$this->m_lastError = "(resize) File does not exist: '$targetImg'!";
			return 0;
		}
		
		if ($this->m_mode == 'im')
		{
			$returnVal = $this->im_resize($targetImg, $destImg, $resizeWidth, $resizeHeight, $opts);
		}
		else
		{
			$returnVal = $this->gd_resize($targetImg, $destImg, $resizeWidth, $resizeHeight, $opts);
		}
		
		if (file_exists($destImg))
		{
			if (!chmod($destImg, octdec($opts['perms'])))
			{
				$this->m_lastError = "(resize) Error chmodding: '$destImg'!";
			}
		}
		
		return $returnVal;
	}
	
	# Uses ImageMagick to resize image $targetImg to dimensions x=$resizeWidth, y=$resizeHeight and write it to $destImg.
	# If $keepAspect == 1 aspect ratio is keeped, otherwise dimensions are forced.
	function im_resize($targetImg, $destImg, $resizeWidth, $resizeHeight, $opts)
	{
		# SET DEFAULT OPTIONS
		if (!isset($opts['keepaspect'])) $opts['keepaspect'] = 1;
		if (!isset($opts['iflarger'])) $opts['iflarger'] = 0;
		if (!isset($opts['1stframe'])) $opts['1stframe'] = 1;
		
		if ($opts['iflarger'])
		{
			# GET THE DIMESIONS OF IMAGE
			$imgStats = getimagesize($targetImg);
			if (!$imgStats)
			{
				$this->m_lastError = "(im_resize) Image '$targetImg' invalid!";
				return 0;
			}
			
			$imageWidth = $imgStats[0];
			$imageHeight = $imgStats[1];
			$imageType = $imgStats[2];
			
			# IF SMALLER, JUST COPY THE TARGET TO DESTINATION
			if (($imageWidth <= $resizeWidth)&&($imageHeight <= $resizeHeight))
			{
				if ($targetImg != $destImg)
				{
					if (!copy($targetImg, $destImg))
					{
						$this->m_lastError = "(im_resize) Cannot copy '$targetImg' to '$destImg'!";
						return 0;
					}
				}
				return 1;
			}
		}
		
		if ($opts['1stframe'])
		{
			# APPEND FRAME SELECTOR TARGET IMAGE
			$targetImg .= '[0]';
		}
		
		$changeBackDir = 0;
		$currDir = getcwd();

		$this->im_chdir(1);
		
		$args = array('-quality', $this->m_quality, '-geometry', $resizeWidth.'x'.$resizeHeight.($opts['keepaspect'] ? '' : '!'), $targetImg, $destImg);
		$args = $this->escArgs($args);
		$argStr = implode(' ', $args);
		
		$cmd = "convert $argStr";
			
		exec($cmd);
		$this->im_chdir(0);
		
		if (!file_exists($destImg))
		{
			$this->m_lastError = "(im_resize) File '$destImg' not created. CMD: $cmd";
			return 0;
		}
		
		$imgStats = getimagesize($destImg);
		$imageWidth = $imgStats[0];
		$imageHeight = $imgStats[1];
		if (($imageWidth > $resizeWidth)||($imageHeight > $resizeHeight))
		{
			$this->m_lastError = "(im_resize) File '$destImg' not < $resizeWidth x $resizeHeight rather $imageWidth x $imageHeight. CMD: $cmd";
			return 0;
		}
		
		return 1;
	}
	
	# Uses GD to resize image $targetImg to dimensions x=$resizeWidth, y=$resizeHeight and write it to $destImg.
	# If $keepAspect == 1 aspect ratio is keeped, otherwise dimensions are forced.
	function gd_resize($targetImg, $destImg, $resizeWidth, $resizeHeight, $opts)
	{
		# SET DEFAULT OPTIONS
		if (!isset($opts['keepaspect'])) $opts['keepaspect'] = 1;
		if (!isset($opts['iflarger'])) $opts['iflarger'] = 0;
		if (!isset($opts['1stframe'])) $opts['1stframe'] = 1;
		
		$imgStats = getimagesize($targetImg);
		if (!$imgStats)
		{
			$this->m_lastError = "(gd_resize) Image '$targetImg' invalid!";
			return 0;
		}
		
		$imageWidth = $imgStats[0];
		$imageHeight = $imgStats[1];
		$imageType = $imgStats[2];
		
		if ($opts['iflarger'])
		{
			# IF SMALLER, JUST COPY THE TARGET TO DESTINATION
			if (($imageWidth <= $resizeWidth)&&($imageHeight <= $resizeHeight))
			{
				if ($targetImg != $destImg)
				{
					if (!copy($targetImg, $destImg))
					{
						$this->m_lastError = "(gd_resize) Cannot copy '$targetImg' to '$destImg'!";
						return 0;
					}
				}
				return 1;
			}
		}

		if ($opts['keepaspect'])
		{
			if ($imageWidth > $imageHeight)
			{
				$ratio = ($imageWidth / $imageHeight);
				$resizeHeight = round($resizeWidth / $ratio);    
			}
			elseif ($imageHeight > $imageWidth) 
			{
				$ratio = ($imageHeight / $imageWidth);
				$resizeWidth = round($resizeHeight / $ratio); 
			} 
		}
			
		$gdSrcImg = $this->gd_open($targetImg, $imageType);
		if (!$gdSrcImg) return 0;

		if (!touch($destImg))
		{
			$this->m_lastError = "(gd_resize) Touch '$destImg' failed!";
			return 0;
		}
		
		if ($this->m_gdVer == 1)
		{
			$gdDstImg = imagecreate($resizeWidth, $resizeHeight);
			imagecopyresized($gdDstImg, $gdSrcImg, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $imageWidth, $imageHeight);
		}
		else
		{	
			$gdDstImg = imagecreatetruecolor($resizeWidth, $resizeHeight);
			imagecopyresampled($gdDstImg, $gdSrcImg, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $imageWidth, $imageHeight);
		}
		
		$this->gd_write($gdDstImg, $destImg, $imageType);
		
		imagedestroy($gdSrcImg);
		imagedestroy($gdDstImg);
		
		return 1;
	}
	
	# Rotates image '$targetImg' clockwise $degrees degrees (1-359) and saves it as $destImg.
	function rotate($targetImg, $destImg, $degrees=90)
	{
		if ($this->m_mode == 'im')
		{
			return $this->im_rotate($targetImg, $destImg, $degrees);
		}
		else
		{
			return $this->gd_rotate($targetImg, $destImg, $degrees);
		}
	}
	
	# Uses ImageMagick to rotate image '$targetImg' clockwise $degrees degrees (1-359) and save it as $destImg.
	function im_rotate($targetImg, $destImg, $degrees=90)
	{
		$changeBackDir = 0;
		$currDir = getcwd();
		
		if (($degrees <= 0)||($degrees >= 360))
		{
			$this->m_lastError = "(im_rotate) Invalid degrees '$degrees'";
			return 0;
		}
		
		$this->im_chdir(1);
		
		$args = array('-rotate', $degrees, $targetImg, $destImg);
		$args = $this->escArgs($args);
		$argStr = implode(' ', $args);
		
		$cmd = "convert $argStr";

		exec($cmd);
		$this->im_chdir(0);
		
		return 1;
	}
	
	# Uses GD to rotate image '$targetImg' clockwise $degrees degrees (1-359) and save it as $destImg.
	function gd_rotate($targetImg, $destImg, $degrees=90)
	{
		if (($degrees <= 0)||($degrees >= 360))
		{
			$this->m_lastError = "(gd_rotate) Invalid degrees '$degrees'";
			return 0;
		}
		
		# Make the same as IM
		$degrees = 360 - $degrees;
		
		$imgStats = getimagesize($targetImg);
		
		if (!$imgStats)
		{
			$this->m_lastError = "(gd_rotate) Image '$targetImg' invalid!";
			return 0;
		}
				
		$imageWidth = $imgStats[0];
		$imageHeight = $imgStats[1];
		$imageType = $imgStats[2];

		$gdSrcImg = $this->gd_open($targetImg, $imageType);
		if (!$gdSrcImg) return 0;
		
		$gdSrcImg = $this->gd_trueColorImg($gdSrcImg);
		$gdRotatedImg = imagerotate($gdSrcImg, $degrees, 0);
		
		if ($destImg != $targetImg)
		{
			if (!touch($destImg))
			{
				$this->m_lastError = "(gd_resize) Touch '$destImg' failed!";
				return 0;
			}
		}
		
		$this->gd_write($gdRotatedImg, $destImg, $imageType);
		
		imagedestroy($gdSrcImg);
		imagedestroy($gdRotatedImg);
		
		return 1;
	}
	
	# Returns the true color GD image data for given GD image data
	function gd_trueColorImg($gdImg)
	{
		if (!imageistruecolor($gdImg))
		{
			$width = imagesx($gdImg);
			$height = imagesy($gdImg);
			$trueColourImg = imagecreatetruecolor($width, $height);
			
			imagecopy($trueColourImg, $gdImg, 0, 0, 0, 0, $width, $height);
			
			$gdImg = $trueColourImg;
		}
		
		return $gdImg;
	}
	
	# Runs 'escapeshellarg' on all elements of given array
	function escArgs($args)
	{
		$newArgs = array();
		
		foreach ($args as $arg)
		{
			$newArgs[] = escapeshellarg($arg);
		}
		
		return $newArgs;
	}
	
	function im_chdir($toIMDir)
	{
		if ($toIMDir)
		{
			if (($this->m_imPath)&&(file_exists($this->m_imPath)))
			{
				$currDir = getcwd();
				if (chdir($this->m_imPath))
				{
					$this->m_prevDir = $currDir;
				}
			}
		}
		else
		{
			if ($this->m_prevDir)
			{
				chdir($this->m_prevDir);
				$this->m_prevDir = '';
			}
		}
	}
}


?>