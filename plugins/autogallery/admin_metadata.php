<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org).
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        02/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(AUTOGAL_ADMINFUNCTIONS);
require_once(dirname(__FILE__)."/language.php");

/*require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");
*/
###################
# SET PREFERENCES #
###################
if (IsSet($_POST['updatesettings']))
{
	$commsWas = $pref['autogal_latestcomms'];
	$commsNow = $_POST['autogal_latestcomms'];
	
    $pref['autogal_metacomments']      = $_POST['autogal_metacomments'];
	$pref['autogal_metacommentsbb']    = $_POST['autogal_metacommentsbb'];
    $pref['autogal_metaviewhits']	   = $_POST['autogal_metaviewhits'];
	$pref['autogal_metaemailhits']	   = $_POST['autogal_metaemailhits'];
	$pref['autogal_xmlsearch']	  	   = $_POST['autogal_xmlsearch'];
	$pref['autogal_metaratings']       = $_POST['autogal_metaratings'];
	$pref['autogal_rateclass']         = $_POST['autogal_rateclass'];
	$pref['autogal_rateiniframe']      = $_POST['autogal_rateiniframe'];
	$pref['autogal_rateiframeheight']  = $_POST['autogal_rateiframeheight'];
	$pref['autogal_arctopscores']      = $_POST['autogal_arctopscores'];
	$pref['autogal_arcmaxtopscores']   = $_POST['autogal_arcmaxtopscores'];
	$pref['autogal_latestcomms']       = $_POST['autogal_latestcomms'];
	$pref['autogal_maxlatestcomms']    = $_POST['autogal_maxlatestcomms'];
	$pref['autogal_lcmaxtextlength']   = $_POST['autogal_lcmaxtextlength'];
	$pref['autogal_lcstripbbcode']     = $_POST['autogal_lcstripbbcode'];
	$pref['autogal_arcadeusexmltrack'] = $_POST['autogal_arcadeusexmltrack'];
		
	save_prefs();
		
	if ((((!$commsWas)&&($commsNow))||($_POST['autogal_regencomms']))&&($pref['autogal_metacomments']))
	{
		AutoGal_RegenLatestCommentsMenu(1);
	}
	else
	{
		$message = "<b>".AUTOGAL_LANG_ADMIN_METADATA_L1."</b>";
	}
}

if ($message)
{
    $ns -> tablerender("", "<div style='text-align:center'>$message</div>");
}

$commentDis = (!$pref['autogal_metacomments'] ? " disabled='disabled'" : '');
$latestCommentDis = (!$pref['autogal_metacomments'] || !$pref['autogal_maxlatestcomms'] ? " disabled='disabled'" : '');
$ratingDis = (!$pref['autogal_metaratings'] ? " disabled='disabled'" : '');
$arcadeDis = (!$pref['autogal_arctopscores'] ? " disabled='disabled'" : '');

################
# INPUT FIELDS #
################
$text = "
<script type='text/javascript'>
function enableCommentsSettings()
{
	var commentsOn;
	var latestCommentsOn;
	
	commentsOn = document.getElementById('autogal_metacomments').checked;
	latestCommentsOn = ((document.getElementById('autogal_latestcomms').checked)&&(commentsOn));
	
	document.getElementById('autogal_metacommentsbb').disabled = !commentsOn;
	document.getElementById('autogal_latestcomms').disabled = !commentsOn;
	document.getElementById('autogal_maxlatestcomms').disabled = !latestCommentsOn;
	document.getElementById('autogal_lcmaxtextlength').disabled = !latestCommentsOn;
	document.getElementById('autogal_lcstripbbcode').disabled = !latestCommentsOn;
	document.getElementById('autogal_regencomms').disabled = !latestCommentsOn;
}

function enableRatingsSettings()
{
	var ratingsOn;
	
	ratingsOn = document.getElementById('autogal_metaratings').checked;
	
	document.getElementById('autogal_rateclass').disabled = !ratingsOn;
	document.getElementById('autogal_rateiniframe').disabled = !ratingsOn;
	document.getElementById('autogal_rateiframeheight').disabled = !ratingsOn;
}

function enableArcadeSettings()
{
	var arcadeOn;
	
	arcadeOn = document.getElementById('autogal_arctopscores').checked;
	
	document.getElementById('autogal_arcmaxtopscores').disabled = !arcadeOn;
	document.getElementById('autogal_arcadeusexmltrack').disabled = !arcadeOn;
}
</script>
<div style='text-align:center'>
<form method='post' name='autogal_xmladmin' action='".e_SELF."'>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_METADATA_L2."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L7."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L8."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_metaviewhits'".($pref['autogal_metaviewhits'] ? " checked='checked'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L9."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L10."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_metaemailhits'".($pref['autogal_metaemailhits'] ? " checked='checked'" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L11."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L12."</span></td>
    <td style='width:50%' class='forumheader3'>
		".
		($pref['autogal_authcachesearch'] 
			? str_replace("[SETTING]", AUTOGAL_LANG_ADMIN_CACHE_24, str_replace("[LINK]", "<a href=\"".AUTOGAL_CACHEADMIN."\">".AUTOGAL_LANG_ADMIN_MENU_L16."</a>", AUTOGAL_LANG_ADMIN_METADATA_L53))
			: "<input type='checkbox' name='autogal_xmlsearch'".($pref['autogal_xmlsearch'] ? " checked='checked'" : "").">"
		 )
		."
	</td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_METADATA_L33."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L5."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L6."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_metacomments' name='autogal_metacomments'".($pref['autogal_metacomments'] ? " checked='checked'" : "")." onclick='javascript:enableCommentsSettings()'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L19."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L20."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_metacommentsbb' name='autogal_metacommentsbb'".($pref['autogal_metacommentsbb'] ? " checked='checked'" : "")."$commentDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L34."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L35."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_latestcomms' name='autogal_latestcomms'".($pref['autogal_latestcomms'] ? " checked='checked'" : "")." onclick='javascript:enableCommentsSettings()'$commentDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L36."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L37."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='3' id='autogal_maxlatestcomms' name='autogal_maxlatestcomms' value='".($pref['autogal_maxlatestcomms'] ? $pref['autogal_maxlatestcomms'] : 10)."'$latestCommentDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L47."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L48."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='3' id='autogal_lcmaxtextlength' name='autogal_lcmaxtextlength' value='".($pref['autogal_lcmaxtextlength'] ? $pref['autogal_lcmaxtextlength'] : 100)."'$latestCommentDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L49."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L50."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_lcstripbbcode' name='autogal_lcstripbbcode'".($pref['autogal_lcstripbbcode'] ? " checked='checked'" : "")."$latestCommentDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L43."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L44."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_regencomms' name='autogal_regencomms'$latestCommentDis></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_METADATA_L32."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L24."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L25."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_metaratings' name='autogal_metaratings'".($pref['autogal_metaratings'] ? " checked='checked'" : "")." onclick='javascript:enableRatingsSettings()'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L26."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L27."</span></td>
    <td style='width:50%' class='forumheader3'>".AutoGal_UserClassSelect("autogal_rateclass' id='autogal_rateclass'$ratingDis", (isset($pref['autogal_rateclass']) ? $pref['autogal_rateclass'] : 253))."</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L28."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L29."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_rateiniframe' name='autogal_rateiniframe'".($pref['autogal_rateiniframe'] ? " checked='checked'" : "")."$ratingDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L30."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L31."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='5' id='autogal_rateiframeheight' name='autogal_rateiframeheight' value='".($pref['autogal_rateiframeheight'] ? $pref['autogal_rateiframeheight'] : 90)."'$ratingDis></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_METADATA_L38."</b>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L39."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L40."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_arctopscores' name='autogal_arctopscores'".($pref['autogal_arctopscores'] ? " checked='checked'" : "")." onclick='javascript:enableArcadeSettings()'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L41."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L42."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='3' id='autogal_arcmaxtopscores' name='autogal_arcmaxtopscores' value='".($pref['autogal_arcmaxtopscores'] ? $pref['autogal_arcmaxtopscores'] : 10)."'$arcadeDis></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_METADATA_L51."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_METADATA_L52."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' id='autogal_arcadeusexmltrack' name='autogal_arcadeusexmltrack'".($pref['autogal_arcadeusexmltrack'] ? " checked='checked'" : "")."$arcadeDis></td>
</tr>
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2'  style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updatesettings' value='".AUTOGAL_LANG_ADMIN_METADATA_L13."' />
    </td>
</tr>
</table>
<br />
".AUTOGAL_LANG_ADMIN_METADATA_L23."<br />
<br />
".AUTOGAL_LANG_ADMIN_METADATA_L14."<br />
<br />
<u>".AUTOGAL_LANG_ADMIN_METADATA_L15."</u><br />
".AUTOGAL_LANG_ADMIN_METADATA_L16."<br />
<br />
<a href='http://www.cerebralsynergy.com'><img style='border:0' alt='Cerebral Synergy' src='".e_PLUGINS."autogallery/Images/button.png' /></a><br />
<a href='".AUTOGAL_SUPPORTLINK."'>".AUTOGAL_LANG_ADMIN_METADATA_L17."</a><br />
<br />
</form>
</div>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_METADATA_L18, $text);
require_once(e_ADMIN."footer.php");
exit;
