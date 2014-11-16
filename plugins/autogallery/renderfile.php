<?php

function AutoGal_RenderFileObj($mediaObj, $showFullImage, $showLimitMsg=true, $fullImgNewWin=AUTOGAL_LARGEIMGNEWWINDOW)
{
	if (!$mediaObj->IsFile()) return '';
	
	switch ($mediaObj->FileType())
	{
		case 'flash': return AutoGal_RenderFileObj_flash($mediaObj);
		case 'flv':   return AutoGal_RenderFileObj_flv  ($mediaObj);
		case 'wmv':   return AutoGal_RenderFileObj_wmv  ($mediaObj);
		case 'wma':   return AutoGal_RenderFileObj_wma  ($mediaObj);
		case 'qt':    return AutoGal_RenderFileObj_qt   ($mediaObj);
		case 'rm':    return AutoGal_RenderFileObj_rm   ($mediaObj);
		case 'image': return AutoGal_RenderFileObj_image($mediaObj, $showFullImage, $showLimitMsg, $fullImgNewWin);
	}
}

function AutoGal_RenderFileObj_flash($mediaObj)
{
	$pvWidth = ($mediaObj->ViewWidth() ? $mediaObj->ViewWidth() : AUTOGAL_FLASHWIDTH);
	$pvHeight = ($mediaObj->ViewHeight() ? $mediaObj->ViewHeight() : AUTOGAL_FLASHHEIGHT);
	
	$text = '
	<object width="'.$pvWidth.'" height="'.$pvHeight.'">
		<param name="movie" value="'.$mediaObj->BaseName().'">
		<param name="quality" value="high">
		<param name="bgcolor" value="#FFFFFF">
		<embed src="'.$mediaObj->Url().'" 
			width="'.$pvWidth.'" height="'.$pvHeight.'" align="" 
			name="'.$mediaObj->Title().'" 
			type="application/x-shockwave-flash"
			pluginspace="http://www.macromedia.com/go/getflashplayer">
		</embed>
	</object><br />'.
	AUTOGAL_LANG_L37.$mediaObj->SizeFormatted().'<br />';
	
	return $text;
}

function AutoGal_RenderFileObj_flv($mediaObj)
{
	$url = urlencode($mediaObj->Url()); # FF
	$target = AUTOGAL_FLVPLAYER.'?file='.$url.'&autoStart=false';
	
	$pvWidth = ($mediaObj->ViewWidth() ? $mediaObj->ViewWidth() : AUTOGAL_FLASHWIDTH);
	$pvHeight = ($mediaObj->ViewHeight() ? $mediaObj->ViewHeight() : AUTOGAL_FLASHHEIGHT);
			
	if (AUTOGAL_SHOWEMBEDLINK)
	{
		$embed = '<embed src="'.AUTOGAL_FLVPLAYER.'" width="'.$pvWidth.'" height="'.$pvHeight .'" name="'.$mediaObj->Title().'" type="application/x-shockwave-flash" pluginspace="http://www.macromedia.com/go/getflashplayer" flashvars="file='.AutoGal_ChangeToEmbed($mediaObj->Url()).'&autoStart=false&lightcolor=0x996600&backcolor=0x000000&frontcolor=0xCCCCCC&repeat=true&logo='.AUTOGAL_FLVPLAYER_LOGO.'"></embed>';
		$inputEmbed ='<input name="ag_embedcode" type="text" value=\''.$embed.'\' readonly="true" onfocus=\'this.select();this.focus();\' onClick=\'this.select();this.focus();\'>';
		$inputLink  ='<input name="ag_embedlink" type="text" value=\''.$target.'\' readonly="true" onfocus=\'this.select();this.focus();\' onClick=\'this.select();this.focus();\'>';
		$embedHtmlCode = '<br /><table><tr><td><b>'.AUTOGAL_LANG_EMBED_L1.'</b></td><td>'.$inputEmbed.'</td><tr><tr><td><b>'.AUTOGAL_LANG_EMBED_L2.'</b></td><td>'.$inputLink.'</td></tr></table>';
	}
	else
	{
		$embedHtmlCode = '';
	}
	
	$text = '
	<object width="'.$pvWidth.'" height="'.$pvHeight.'">
		<param name="movie" value="'.$mediaObj->BaseName().'">
		<param name="quality" value="high">
		<param name="bgcolor" value="#FFFFFF">
		<param name="flashvars" value="file='.$url.'&autoStart=false">
		<embed src="'.AUTOGAL_FLVPLAYER.'" 
			width="'.$pvWidth.'" height="'.$pvHeight.'"
			name="'.$mediaObj->Title().'" 
			type="application/x-shockwave-flash"
			pluginspace="http://www.macromedia.com/go/getflashplayer"
			flashvars="file='.$url.'&autoStart=false">
		</embed>
	</object><br />'.
	AUTOGAL_LANG_L37.$mediaObj->SizeFormatted().$embedHtmlCode.'<br />';
	
	return $text;
}

function AutoGal_RenderFileObj_qt($mediaObj)
{
	$pvWidth = ($mediaObj->ViewWidth() ? $mediaObj->ViewWidth() : AUTOGAL_MOVIEWIDTH);
	$pvHeight = ($mediaObj->ViewHeight() ? $mediaObj->ViewHeight() : AUTOGAL_MOVIEHEIGHT);
	
	$text = '
	<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" 
		width="'.$pvWidth.'" height="'.($pvHeight + 15).'" 
		codebase="http://www.apple.com/qtactivex/qtplugin.cab">
		<param name="src" value="'.$mediaObj->Url().'">
		<param name="autoplay" value="true">
		<param name="controller" value="true">
		<param name="loop" value="true">
		<embed src="'.$mediaObj->Url().'" 
			width="'.$pvWidth.'" height="'.($pvHeight + 15).'" 
			autoplay="true" 
			controller="true" 
			loop="true" 
			pluginspage="http://www.apple.com/quicktime/download/">
		</embed>
	</object><br />'.
	AUTOGAL_LANG_L37.$mediaObj->SizeFormatted().'<br />';
	
	return $text;
}

function AutoGal_RenderFileObj_wmv($mediaObj)
{
	$pvWidth = ($mediaObj->ViewWidth() ? $mediaObj->ViewWidth() : AUTOGAL_MOVIEWIDTH);
	$pvHeight = ($mediaObj->ViewHeight() ? $mediaObj->ViewHeight() : AUTOGAL_MOVIEHEIGHT);

	$text = '
	<table border="0" cellpadding="0" align="center" width="'.$pvWidth.'" height="'.$pvHeight.'">
	<tr><td>
		<object id="mediaPlayer"
			classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" 
			width="'.$pvWidth.'" height="'.$pvHeight.'" 
			codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"
			standby="'.AUTOGAL_LANG_L27.'" type="application/x-oleobject">
			<param name="fileName" value="'.$mediaObj->Url().'">
			<param name="animationatStart" value="true">
			<param name="transparentatStart" value="true">
			<param name="autoStart" value="true">
			<param name="showControls" value="true">
			<param name="loop" value="true">
			<embed src="'.$mediaObj->Url().'" 
				type="application/x-mplayer2"
				width="'.$pvWidth.'" height="'.$pvHeight.'"
				pluginspage="http://microsoft.com/windows/mediaplayer/en/download/"
				id="mediaPlayer" 
				name="mediaPlayer" 
				displaysize="4"
				autosize="-1" 
				bgcolor="darkblue" 
				showcontrols="true" 
				showtracker="-1" 
				showdisplay="0" 
				showstatusbar="-1" 
				videoborder3d="-1" 
				autostart="true"
				designtimesp="5311" 
				loop="true">
			</embed>
		</object>
	</td></tr>
	<tr><td style="text-align:center">
		'.AUTOGAL_LANG_L37.$mediaObj->SizeFormatted().'
	</td></tr>
	<tr><td style="text-align:center">
		<a href="'.$mediaObj->Url().'" target="_blank">'.AUTOGAL_LANG_L26.'</a>
	</td></tr>
	</table>';
	
	return $text;
}

function AutoGal_RenderFileObj_wma($mediaObj)
{
	$pvWidth = 300;
	$pvHeight = 46;

	$text = '
	<table border="0" cellpadding="0" align="center" width="'.$pvWidth.'" height="'.$pvHeight.'">
	<tr><td>
		<object id="mediaPlayer"
			classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" 
			width="'.$pvWidth.'" height="'.$pvHeight.'" 
			codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"
			standby="'.AUTOGAL_LANG_L27.'" type="application/x-oleobject">
			<param name="fileName" value="'.$mediaObj->Url().'">
			<param name="animationatStart" value="true">
			<param name="transparentatStart" value="true">
			<param name="autoStart" value="true">
			<param name="showControls" value="true">
			<param name="loop" value="true">
			<embed src="'.$mediaObj->Url().'" 
				type="application/x-mplayer2"
				width="'.$pvWidth.'" height="'.$pvHeight.'"
				pluginspage="http://microsoft.com/windows/mediaplayer/en/download/"
				id="mediaPlayer" 
				name="mediaPlayer" 
				displaysize="4"
				autosize="-1" 
				bgcolor="darkblue" 
				showcontrols="true" 
				showtracker="-1" 
				showdisplay="0" 
				showstatusbar="-1" 
				videoborder3d="-1" 
				autostart="true"
				designtimesp="5311" 
				loop="true">
			</embed>
		</object>
	</td></tr>
	<tr><td style="text-align:center">
		'.AUTOGAL_LANG_L37.$mediaObj->SizeFormatted().'
	</td></tr>
	<tr><td style="text-align:center">
		<a href="'.$mediaObj->Url().'" target="_blank">'.AUTOGAL_LANG_L26.'</a>
	</td></tr>
	</table>';
	
	return $text;
}
	
function AutoGal_RenderFileObj_rm($mediaObj)
{	
	$pvWidth = ($mediaObj->ViewWidth() ? $mediaObj->ViewWidth() : AUTOGAL_MOVIEWIDTH);
	$pvHeight = ($mediaObj->ViewHeight() ? $mediaObj->ViewHeight() : AUTOGAL_MOVIEHEIGHT);
		
	$text = '
	<table border="0" cellpadding="0" align="center" width="'.$pvWidth.'" height="'.($pvHeight + 45).'">
	<tr><td>
	<object id="rvocx" 
		classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
		width="'.$pvWidth.'" height="'.$pvHeight.'" 
		<param name="src" value="'.$mediaObj->Url().'">
		<param name="autostart" value="true">
		<param name="controls" value="imagewindow">
		<param name="console" value="video">
		<param name="loop" value="true">
		<embed src="'.$mediaObj->Url().'" 
			width="'.$pvWidth.'" height="'.$pvHeight.'" 
			loop="true" 
			type="audio/x-pn-realaudio-plugin" 
			controls="imagewindow" 
			console="video" 
			autostart="true">
		</embed>
	</object>
	<object id="rvocx" 
		classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
		width="'.$pvWidth.'" height="30">
		<param name="src" value="'.$mediaObj->Url().'">
		<param name="autostart" value="true">
		<param name="controls" value="ControlPanel">
		<param name="console" value="video">
		<embed src="'.$mediaObj->Url().'" 
			width="'.$pvWidth.'" height="30" 
			controls="ControlPanel" 
			type="audio/x-pn-realaudio-plugin" 
			console="video" 
			autostart="true">
		</embed>
	</object>
	</td></tr>
	<tr><td style="text-align:center">
		'.AUTOGAL_LANG_L37.$mediaObj->SizeFormattedl().'
	</td></tr>
	<tr><td style="text-align:center">
		<a href="'.$mediaObj->Url().'" target="_blank">'.AUTOGAL_LANG_L26.'</a>
	</td></tr>
	</table><br />';
	
	return $text;
}

function AutoGal_RenderFileObj_image($mediaObj, $showFullImage, $showLimitMsg, $fullImgNewWin)
{
	$imgUrl = $mediaObj->Url();
	$pvWidth = 0;
	$pvHeight = 0;
	$isLimited = 0;
	$title = $mediaObj->Title();
	
	if (!$showFullImage)
	{
		$imageStats= getimagesize($mediaObj->AbsPath());
		$imageWidth = $imageStats[0];
		$imageHeight = $imageStats[1];
	
		$whRatio = ($imageWidth / $imageHeight);
		$hwRatio = ($imageHeight / $imageWidth);
		
		# CHECK IMAGE DIMENSIONS TO SEE IF IT LARGER THAN MAX DISPLAY SIZE
		if (($imageWidth > AUTOGAL_MAXIMAGEWIDTH)||($imageHeight > AUTOGAL_MAXIMAGEHEIGHT))
		{
			$isLimited = 1;
			$targetWidth = AUTOGAL_MAXIMAGEWIDTH;
			$targetHeight = AUTOGAL_MAXIMAGEHEIGHT;
						
			if ($imageWidth > $imageHeight)
			{
				$ratio = ($imageWidth / $imageHeight);
				$targetHeight = round($targetWidth / $ratio);    
			}
			elseif ($imageHeight > $imageWidth) 
			{
				$ratio = ($imageHeight / $imageWidth);
				$targetWidth = round($targetHeight / $ratio); 
			} 
							
			# IF WE HAVE PREVIEW IMAGE RESIZING ON
			if (AUTOGAL_RESIZEPREVIEWIMGS)
			{
				$makePvImg = 0;
				# IF THE PREVIEW IMAGE EXISTS
				if ($mediaObj->PreviewImageExists())
				{
					# CHECK TO SEE IF THE PREVIE IMAGE IS THE RIGHT SIZE
					$pvImageSize = AutoGal_GetImageDimensions($mediaObj->PreviewImagePath(), true);
					
					if (($pvImageSize['w'] != $targetWidth)||($pvImageSize['h'] != $targetHeight))
					{
						# NOT THE RIGHT SIZE? RESIZE IT.
						$makePvImg = 1;
					}
				}
				else
				{
					# PREVIEW IMAGE DOESN'T EXIT? MAKE ONE.
					$makePvImg = 1;
				}
				
				if ($makePvImg)
				{
					# RESIZE IMAGE TO THE PREVIEW IMAGE
					require_once(AUTOGAL_IMGMANIPHANDLER);
					$gdim = new GDIM($mode, $imPath, $imQuality);

					$opts['keepaspect'] = 1;
					$opts['1stframe'] = 1;
					$opts['iflarger'] = 0;
					$opts['perms'] = AUTOGAL_PERMSGALTHUMBS;
					
					# RESIZE THE IMAGE
					if (!$gdim->resize($mediaObj->AbsPath(), $mediaObj->PreviewImagePath(), AUTOGAL_MAXIMAGEWIDTH, AUTOGAL_MAXIMAGEHEIGHT, $opts))
					{
						# SOMETHING FUCKED UP...
						global $ns;
						$ns->tablerender('', $gdim->lastError());
					}
					else
					{
						$imgUrl = $mediaObj->PreviewImageUrl();
					}
				}
				else
				{
					$imgUrl = $mediaObj->PreviewImageUrl();
				}
			}
			else
			{
				# NO PREVIEW IMAGE RESIZING? JUST RESIZE IT WITH HTML
				$pvWidth = $targetWidth;
				$pvHeight = $targetHeight;
			}
		}
	}
	
	# Create links for viewing full image
	$showFullAHref = "";
	$showFullAHrefEnd = "";
	$showFullLink = "";
	if ($showLimitMsg)
	{
		if ($isLimited)
		{
			if ($fullImgNewWin)
			{
				$showFullAHref = "<a target='_blank' href=\"".$mediaObj->Url()."\">";
			}
			else
			{
				$showFullAHref = "<a href=\"".AUTOGAL_AUTOGALLERY."?show=".urlencode($mediaObj->Element())."&amp;full=1$shNewWin\">";
			}
			
			$showFullLink = "$showFullAHref<span class='smalltext'>".str_replace("[SIZE]", $imageWidth."x".$imageHeight, AUTOGAL_LANG_L14)."</span></a>";
			$showFullAHrefEnd = "</a>";
		}
		else if ($showFullImage)
		{
			if (!$fullImgNewWin)
			{
				$showFullAHref = "<a href=\"".AUTOGAL_AUTOGALLERY."?show=".urlencode($mediaObj->Element())."\">";
				$showFullLink = "$showFullAHref<span class='smalltext'>".AUTOGAL_LANG_L58."</span></a>";
				$showFullAHrefEnd = "</a>";
			}
		}
	}
		
	# Get the preview size from XML setting if it exists
	$pvWidth = ($mediaObj->ViewWidth() ? $mediaObj->ViewWidth() : $pvWidth);
	$pvHeight = ($mediaObj->ViewHeight() ? $mediaObj->ViewHeight() : $pvHeight);
	
	# Generate HTML style dimensions for image
	$imgDims = ";";
	if ($pvWidth) $imgDims .= "width:${pvWidth}px;";
	if ($pvHeight) $imgDims .= "height:${pvHeight}px;";
	$imgDims = substr($imgDims , 0, -1);

	# Generate embed link/code HTML
	if (AUTOGAL_SHOWEMBEDLINK)
	{
		$imageLink = "<img src=\"".AutoGal_ChangeToEmbed($mediaObj->Url())."\" alt=\"".htmlspecialchars($title)."\" style=\"border:0$imgDims\" />";
		$inputEmbed = "<input class='tbox' name=\"ag_embedcode\" type=\"text\" value='".$imageLink."' readonly=\"true\" onfocus='this.select();this.focus();' onClick='this.select();this.focus();'>";
		$inputLink = "<input class='tbox' name=\"ag_embedlink\" type=\"text\" value='".AutoGal_ChangeToEmbed($mediaObj->Url())."' readonly=\"true\" onfocus='this.select();this.focus();' onClick='this.select();this.focus();'>";
		$embedHtmlCode = '<br /><table><tr><td><b>'.AUTOGAL_LANG_EMBED_L1.'</b>&#160;</td><td>'.$inputEmbed.'&#160;</td><tr><tr><td><b>'.AUTOGAL_LANG_EMBED_L2.'</b></td><td>'.$inputLink.'</td></tr></table>';
	}
	else
	{
		$embedHtmlCode = '';
	}
	
	# Generate preview HTML for image
	$text = "$showFullAHref<img src=\"".htmlspecialchars($imgUrl)."\" alt=\"".htmlspecialchars($title)."\" style='border:0$imgDims' />$showFullAHrefEnd<br />";
	$text .= ($showFullLink ? "$showFullLink<br />" : '');
	$text .= $embedHtmlCode;
	
	return $text;
}

function AutoGal_ChangeToEmbed($text)
{
	$text_len = strlen($text);
	$fixed_text = "";
	for($i=0;$i<$text_len;$i++)
	{	
		if(substr($text,$i,1) == " ")
		{
			$fixed_text = $fixed_text . "%20";
		}
		else
		{
			$fixed_text = $fixed_text . substr($text,$i,1);
		}
	}
	return $fixed_text;
}
?>