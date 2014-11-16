<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        05/01/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/language.php");

$text = "";
$showInNewWindow = false;
$newWindow = $_GET['newwindow'];

define("e_PAGETITLE", AUTOGAL_TITLE." - ".AUTOGAL_LANG_EMAIL_L20);

if ((AUTOGAL_SHOWINNEWWINDOW)&&($newWindow))
{
	$showInNewWindow = true;
	$text = AutoGal_GetNewWindowHeader(e_PAGETITLE);
}
else
{
	require_once(HEADERF);
}

if (AUTOGAL_EMAILTOFRIEND)
{
    $element = stripslashes(rawurldecode(($_GET['ele'] ? $_GET['ele'] : $_POST['ele'])));
	$mediaObj = new AutoGal_CMediaObj($element);
	
	if (!$mediaObj->IsValid())
	{
		$text .= AUTOGAL_LANG_EMAIL_L1." \"$element\" (".$mediaObj->LastError().")!";
	}
	else
	{
		$imageURL = $mediaObj->Link();
		$imageTitle = $mediaObj->Title();
		
		$imagePath = $mediaObj->Url();
		$thumbImageHTML = $mediaObj->ThumbImageHtml();
		$thumbImageUrl = $mediaObj->ThumbImageUrl();
		
		$comment = AUTOGAL_DEFAULTETFCOM;
		
		if ($_POST['ag_sendemail'])
		{
			require_once (e_HANDLER."mail.php");
			$fromName = htmlspecialchars($_POST['ag_fromname']);
			$fromEmail = $_POST['ag_fromemail'];
			$toEmail = $_POST['ag_emailaddress'];
			$comment = htmlspecialchars($_POST['ag_comment']);
			$commentToEmail = str_replace("\n", '<br />', strip_tags($comment));
			$serverHost = $_SERVER['HTTP_HOST'];
			if (!preg_match("/^http\:\/\//", $serverHost)) $serverHost = "http://$serverHost";
			
			$messageMsg = AUTOGAL_LANG_EMAIL_L2;
			$messageMsg = str_replace("[TOEMAIL]", $toEmail, $messageMsg);
			$messageMsg = str_replace("[FROMNAME]", $fromName, $messageMsg);
			$messageMsg = str_replace("[FROMEMAIL]", "<a href='mailto:$fromEmail'>$fromEmail</a>", $messageMsg);
			$messageMsg = str_replace("[SERVERNAME]", "<a href='$serverHost'>".SITENAME."</a>", $messageMsg);
			
			$message = 
			#"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n". 
			# ^^^^ TAKEN OUT DUE TO WEIRDNESS IN 0.7 (http://www.cerebralsynergy.com/forum_viewtopic.php?2.40)
			"<html xmlns=\"http://www.w3.org/1999/xhtml\">\n".
			"<head>\n".
			"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\">\n".
			"<title>$imageTitle</title>\n".
			"</head>\n".
			"\n".
			"<body>\n".
	  		"$messageMsg<br />\n".
			"<br />\n".
			"<table border='1' cellpadding='3' align='center'>\n".
			"<tr><td><b>".AUTOGAL_LANG_EMAIL_L4."</b></td><td>$imageTitle</td></tr>\n".
			"<tr><td><b>".str_replace("[FROMNAME]", $fromName, AUTOGAL_LANG_EMAIL_L5)."</b></td><td>$commentToEmail</td></tr>\n".
			"<tr><td><b>".AUTOGAL_LANG_EMAIL_L6."</b></td><td><a href=\"".$imageURL."\">".AUTOGAL_LANG_EMAIL_L7."</a></td></tr>\n".
			($mediaObj->Description() ? "<tr><td><b>".AUTOGAL_LANG_EMAIL_L21."</b></td><td>".AutoGal_DoBBCode($mediaObj->Description())."</td></tr>\n" : '').
			"</table>\n".
			"</body>\n".
			"</html>\n";
			
			if (sendemail($toEmail, str_replace("[IMAGETITLE]", $imageTitle, str_replace("[SITENAME]", SITENAME, AUTOGAL_LANG_EMAIL_L10)), $message, $toEmail, $fromEmail, $fromName))
			{
				$ns -> tablerender(AUTOGAL_LANG_EMAIL_L11, str_replace("[TOEMAIL]", $toEmail, AUTOGAL_LANG_EMAIL_L8));
				$mediaObj->EmailHitsInc();
				$mediaObj->SaveMetaData();
			}
			else
			{
				$ns -> tablerender(AUTOGAL_LANG_EMAIL_L12, str_replace("[TOEMAIL]", $toEmail, AUTOGAL_LANG_EMAIL_L9));
			}
		}
		
		$botLinks = AutoGal_GetBotLinks();
		
		$text .= " 
		<form method='post' action='".e_PLUGINS."autogallery/email.php'>
		<div style='text-align:center'>
		<h".AUTOGAL_TITLEHEADSTYLE.">".str_replace("[IMAGETITLE]", $imageTitle, AUTOGAL_LANG_EMAIL_L13)."</h".AUTOGAL_TITLEHEADSTYLE."><br />
		<a href=\"$imageURL".($showInNewWindow ? "&newwindow=1" : '')."\">$thumbImageHTML</a><br />
		<br />
		<table style='width:85%' class='fborder'>
		<tr>
			<td style='width:50%' class='forumheader3'>".AUTOGAL_LANG_EMAIL_L14."</td>
			<td style='width:50%' class='forumheader3'><input type='text' class='tbox' name='ag_emailaddress' size='40' value=''></td>
		</tr>
		<tr>
			<td style='width:50%' class='forumheader3'>".AUTOGAL_LANG_EMAIL_L15."</td>
			<td style='width:50%' class='forumheader3'><input type='text' class='tbox' name='ag_fromname' size='40' value='$fromName'></td>
		</tr>
		<tr>
			<td style='width:50%' class='forumheader3'>".AUTOGAL_LANG_EMAIL_L16."</td>
			<td style='width:50%' class='forumheader3'><input type='text' class='tbox' name='ag_fromemail' size='40' value='$fromEmail'></td>
		</tr>
		<tr>
			<td style='width:50%' class='forumheader3'>".AUTOGAL_LANG_EMAIL_L17."</td>
			<td style='width:50%' class='forumheader3'><textarea class='tbox' name='ag_comment' cols='40' rows='7'>$comment</textarea></td>
		</tr>
		<tr style='vertical-align:top'>
			<td colspan='2'  style='text-align:center' class='forumheader'>
				<input class='button' type='submit' name='ag_sendemail' value='".AUTOGAL_LANG_EMAIL_L18."'>
			</td>
		</tr>
		<input type='hidden' name='img' value=\"".rawurlencode($image)."\">
		</table>
		</form>
		<br />".(count($botLinks) > 0 ? "<br />".implode(' ', $botLinks) : '').
		"</div>";
	}
}
else
{
    $text .= AUTOGAL_LANG_EMAIL_L19;
}
    
$ns -> tablerender(e_PAGETITLE, $text);

if ((!$showInNewWindow)&&(AUTOGAL_SHOW_FOOTER))
{
	require_once(FOOTERF);
}

?>