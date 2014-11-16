<?php

function AutoGal_MetaDeleteComments(&$mediaObj, $IDs)
{
	if (!AUTOGAL_USEXMLMETACOMS) return;
	if (!$mediaObj->CheckUserPriv('commentadmin')) return;

	foreach ($IDs as $commentIndex => $commentID)
	{
		$mediaObj->DeleteComment($commentID);
	}

	$text = AUTOGAL_LANG_COMMENTS_L11;
		
	if (AUTOGAL_DOLATESTCOMMS) 
	{
		$mediaObj->SaveMeta();
		require_once(AUTOGAL_ADMINFUNCTIONS);
		AutoGal_RegenLatestCommentsMenu();
	}
	
	return $text;
}

function AutoGal_RenderComments(&$mediaObj)
{
	if (!AUTOGAL_USEXMLMETACOMS) return;
	
	global $ns;
	
	$isAdmin = $mediaObj->CheckUserPriv('commentadmin');
	$userCanPost = $mediaObj->CheckUserPriv('comment');
	$text = "<form name='AutoGal_Comments' method='post'>";
	
	if (($_POST['AutoGal_DeleteComments'])&&($isAdmin))
	{
		$comIDs = array();
		for ($commID = 0; $commID < $mediaObj->NumComments(); $commID ++)
		{
			if ($_POST["AutoGal_CommentDelete_$commID"])
			{
				$comIDs[] = $commID;
			}
		}
		
		if (count($comIDs) <= 0)
		{
			$ns -> tablerender(AUTOGAL_LANG_COMMENTS_L10, "<div style='text-align:center'><b>".AUTOGAL_LANG_COMMENTS_L12."</b></div>");
		}
		else
		{
			$ns -> tablerender(AUTOGAL_LANG_COMMENTS_L10, AutoGal_MetaDeleteComments($mediaObj, $comIDs));
		}
	}
	elseif ($_POST['AutoGal_SumbitComment'])
	{
		$commOK = false;
		$commentText = stripslashes($_POST['AutoGal_CommentText']);
		
		if (!$commentText)
		{
			$ns -> tablerender(AUTOGAL_LANG_COMMENTS_L2, "<div style='text-align:center'><b>".AUTOGAL_LANG_COMMENTS_L3."</b></div>");
		}
		elseif(trim($commentText) == trim($mediaObj->LastCommentText()))
		{
			$ns -> tablerender(AUTOGAL_LANG_COMMENTS_L2, "<div style='text-align:center'><b>".AUTOGAL_LANG_COMMENTS_L4."</b></div>");
		}
		else if (!$userCanPost)
		{
			$ns -> tablerender(AUTOGAL_LANG_COMMENTS_L2, "<div style='text-align:center'><b>".AUTOGAL_LANG_COMMENTS_L5."</b></div>");
		}
		else
		{
			if (USER)
			{
				$newComment['authorid'] = USERID;
				$newComment['authorusername'] = USERNAME;
				$commOK = true;
			}
			else
			{
				$newComment['authorid'] = 0;
				$newComment['authorusername'] = $_POST['AutoGal_CommentAuthor'];
				
				if (!$newComment['authorusername'] || strlen($newComment['authorusername']) > 30)
				{
					$ns -> tablerender(AUTOGAL_LANG_COMMENTS_L2, "<div style='text-align:center'><b>".AUTOGAL_LANG_COMMENTS_L6."</b></div>");
				}
				else
				{
					$commOK = true;
				}
			}
		}
		
		if ($commOK)
		{
			$newComment['text'] = $commentText;
			$newComment['date'] = time();
			$mediaObj->AddComment($newComment);
		}
	}

	$comments = $mediaObj->Comments();
	foreach ($comments as $commentI => $comment)
	{
		if (AUTOGAL_COMMENTBBCODE)
		{
			$commentText = AutoGal_DoBBCode($comment['text']);
		}
		else
		{
			$commentText = $comment['text'];
			$commentText = nl2br($commentText);
			$commentText = htmlspecialchars($commentText);
		}
	
		$text .= "
		<div class='spacer' style='text-align:center'>
		<table class='fborder' width='97%'>
		<tr>
			<td class='fcaption'".($isAdmin ? " colspan='2'" : '')."><b>".($comment['authorid'] == 0 ? $comment['authorusername'].AUTOGAL_LANG_COMMENTS_L8 : "<a href=\"".e_BASE."user.php?id.".$comment['authorid']."\">".$comment['authorusername']."</a>")."</b> @ ".strftime(AUTOGAL_COMMENTTIMEFORMAT, $comment['date'])."</td>
		</tr>
		<tr>".
			($isAdmin ?	"<td class='forumheader3' style='vertical-align:top'><input type='checkbox' name='AutoGal_CommentDelete_$commentI'></td>" : '').
			"<td class='forumheader3' style='vertical-align:top;width:100%'>$commentText</td>
		</tr>
		</table>
		</div>";
	}
	
	$text .= "
	<br />
	<div class='spacer' style='text-align:center'>".
	
	($userCanPost ? "
	<table><tr><td style='text-align:left'>
	".(USER ? '' : AUTOGAL_LANG_COMMENTS_L7." <input name='AutoGal_CommentAuthor' class='tbox' size='30' maxlength='30'><br />")."
	<textarea name='AutoGal_CommentText' class='tbox' cols='80' rows='6'></textarea><br />
	</td></tr></table>
	<input type='submit' name='AutoGal_SumbitComment' class='button' value='".AUTOGAL_LANG_COMMENTS_L2."'>" : '').
	(($isAdmin)&&($mediaObj->NumComments() > 0) ? "<input type='submit' name='AutoGal_DeleteComments' class='button' value='".AUTOGAL_LANG_COMMENTS_L10."'>" : '')."<br />
	<span class='smalltext'>".(AUTOGAL_COMMENTBBCODE ? AUTOGAL_LANG_COMMENTS_L13 : '')."</span>
	</form>
	</div>";
	
	if (!(($mediaObj->NumComments() <= 0)&&(!$userCanPost)))
	{
		$ns -> tablerender(AUTOGAL_LANG_COMMENTS_L1. " (".$mediaObj->NumComments().")", $text);
	}
}

function AutoGal_RenderDescription(&$mediaObj)
{
	global $ns;
	
	if (!$mediaObj->Description()) return;
	
	$ns->tablerender(str_replace("[TYPE]", $mediaObj->FileTypeTitle(), AUTOGAL_LANG_DESCRIPTION_L1), AutoGal_DoBBCode($mediaObj->Description()));
}

function AutoGal_RenderRating(&$mediaObj)
{
	if (!AUTOGAL_USEXMLMETARATINGS) return;
	if (!$mediaObj->IsFile()) return;
	
	global $ns;
		
	if (AUTOGAL_RATEIFRAME)
	{
		$text = "
		<iframe src=\"".AUTOGAL_RATING."?show=".rawurlencode($mediaObj->Element())."\" width='100%' frameborder='0' scrolling='no' height='".AUTOGAL_RATEIFRAMEHEIGHT."'>
		</iframe>";
		
		print $text;
	}
	else
	{
		$text = AutoGal_RatingHTML($mediaObj);
		$ns->tablerender(str_replace('[TYPE]', $mediaObj->FileTypeTitle(), AUTOGAL_LANG_RATING_L1), $text);
	}
}

function AutoGal_RatingHTML(&$mediaObj, $skipNew=0)
{
	if (!AUTOGAL_USEXMLMETARATINGS) return;
	if (!$mediaObj->IsFile()) return;
		
	global $ns;
	
	# GET RATING INFO
	$userCanRate = AutoGal_IsRatingAllowed();
	
	if (USER)
	{
		$rateUsername = USERNAME;
		$rateUserID = USERID;
	}
	else
	{
		$rateUsername = $_SERVER['REMOTE_ADDR'];
		$rateUserID = 0;
	}
	
	$userHasRated = $mediaObj->UserHasRated($rateUserID, $rateUsername);
	
	# PROCESS RATING
	if ($_POST['ag_dorating'])
	{
		$newRatingVal = $_POST['ag_rating'];
		
		if ((!$userHasRated)&&($newRatingVal <= AUTOGAL_MAXRATE)&&($newRatingVal >= 1)&&($userCanRate))
		{
			$mediaObj->AddRating($newRatingVal, $rateUsername, $rateUserID);
			$userCanRate = 0;
			$userHasRated = 1;
		}
	}
	
	$ratingRecs = $mediaObj->Ratings();
	$numRatings = $mediaObj->NumRatings();
	$rating     = $mediaObj->AvgRating();
	$rateImages = $mediaObj->RatingImages();
	
	$rateMsg = AUTOGAL_LANG_RATING_L5;
	$rateMsg = str_replace('[TYPE]', $mediaObj->FileTypeTitle(), $rateMsg);
	$rateMsg = str_replace('[NUMVOTES]', $numRatings, $rateMsg);
	$rateMsg = str_replace('[RATING]', $rating, $rateMsg);
	$rateMsg = str_replace('[MAXRATING]', number_format(AUTOGAL_MAXRATE, 2), $rateMsg);
		
	# RENDER RATING	
	$text .= "<div style='text-align:center'>";
	
	if (!$userCanRate)
	{
		if ($numRatings == 0)
		{
			$text .= AUTOGAL_LANG_RATING_L3;
		}
		else
		{
			$text .= $rateImages."<br /><span class='smalltext'>$rateMsg</span>";
		}
	}
	else
	{
		for ($rateNum = 1; $rateNum <= AUTOGAL_MAXRATE; $rateNum ++)
		{
			$rateSelectNums .= "<td style='text-align:center'><b>$rateNum</b></td>";
			$rateSelectOpts .= "<td style='text-align:center'><input type='radio' name='ag_rating' value='$rateNum'".($rateNum == ceil(AUTOGAL_MAXRATE / 2) ? " checked='checked'" : '')."></td>";
		}
		
		$rateSelect = "<form method='POST'>
		<table style='text-align:center'>
			<tr>$rateSelectNums<td rowspan='2' style='text-align:center;padding-left:5px'><input type='submit' name='ag_dorating' class='button' value='".AUTOGAL_LANG_RATING_L6."'></td></tr>
			<tr>$rateSelectOpts</tr>
		</table>
		<input type='hidden' name='show' value=\"".rawurlencode($mediaObj->Element())."\">
		</form>";

		if ($numRatings == 0)
		{
			$text .= AUTOGAL_LANG_RATING_L3."<br />".$rateSelect;
		}
		else if ($userHasRated)
		{
			$text .= $rateImages."<br /><span class='smalltext'>$rateMsg<br />".AUTOGAL_LANG_RATING_L2."</span>";
		}
		else
		{
			$text .= $rateImages."<br /><span class='smalltext'>$rateMsg</span><br />".$rateSelect;
		}
	}

	$text .= "</div>";
	
	return $text;
}

function AutoGal_RenderTopScores(&$mediaObj)
{
	if (!AUTOGAL_ARCTOPSCORES) return;
	if ($mediaObj->FileType() != 'flash') return;
		
	global $ns;
	
	if ($mediaObj->ArcadeNumTopScores() <= 0) return;
	$scores = $mediaObj->ArcadeTopScores();
	
	$text = "
	<div style='text-align:center'>
	<table class='border'>
	<tr>
		<th class='forumheader' style='text-align:center'>".AUTOGAL_LANG_ARCADE_L2."</th>
		<th class='forumheader' style='text-align:center'>".AUTOGAL_LANG_ARCADE_L3."</th>
		<th class='forumheader' style='text-align:center'>".AUTOGAL_LANG_ARCADE_L5."</th>
		<th class='forumheader' style='text-align:center'>".AUTOGAL_LANG_ARCADE_L4."</th>
	</tr>";
	
	$rank = 1;
	foreach ($scores as $score)
	{
		$user = $score['username'];
		$userID = $score['userid'];
		$points = $score['points'];
		$date = strftime(AUTOGAL_TOPSCORETIMEFORMAT, $score['date']);
		
		if ($userID > 0)
		{
			$user = "<a href=\"".e_BASE."user.php?id.$userID\">$user</a>";
		}
		
		$text .= "
		<tr>
			<td class='forumheader3' style='text-align:left'>$rank.</td>
			<td class='forumheader3' style='text-align:center'>$user</td>
			<td class='forumheader3' style='text-align:center'>$date</td>
			<td class='forumheader3' style='text-align:right'>$points</td>
		</tr>";
		
		$rank ++;
	}
	
	$text .= "
	</table>
	</div>";
	
	$ns -> tablerender(AUTOGAL_LANG_ARCADE_L1, $text);
}

?>