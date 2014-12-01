<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        11/04/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def.php");
require_once(dirname(__FILE__)."/admin_functions.php");
require_once(dirname(__FILE__)."/language.php");
 
/*if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_ADMIN."auth.php");*/
require_once(e_HANDLER."userclass_class.php");

###################
# SET PREFERENCES #
###################
if(IsSet($_POST['updatesettings'])||IsSet($_POST['resetthumbs']))
{
    $pref['autogal_numcols']            = $_POST['autogal_numcols'];
    $pref['autogal_numgallcols']        = $_POST['autogal_numgallcols'];
    $pref['autogal_showfooter']         = $_POST['autogal_showfooter'];
    $pref['autogal_subgalleryclass']    = $_POST['autogal_subgalleryclass'];
    $pref['autogal_imagecellclass']     = $_POST['autogal_imagecellclass'];
    $pref['autogal_navclass']           = $_POST['autogal_navclass'];
    $pref['autogal_navseperator']       = $_POST['autogal_navseperator'];
    $pref['autogal_maximagewidth']      = $_POST['autogal_maximagewidth'];
    $pref['autogal_maximageheight']     = $_POST['autogal_maximageheight'];
    $pref['autogal_maxperpage']         = $_POST['autogal_maxperpage'];
	$pref['autogal_showautogalver']     = $_POST['autogal_showautogalver'];
    $pref['autogal_showsubtitlesgal']   = $_POST['autogal_showsubtitlesgal'];
	$pref['autogal_flashwidth']         = $_POST['autogal_flashwidth'];
    $pref['autogal_flashheight']        = $_POST['autogal_flashheight'];
	$pref['autogal_moviewidth']         = $_POST['autogal_moviewidth'];
    $pref['autogal_movieheight']        = $_POST['autogal_movieheight'];
	$pref['autogal_titleheadstyle']     = $_POST['autogal_titleheadstyle'];
	$pref['autogal_showtitleingall']    = $_POST['autogal_showtitleingall'];
	$pref['autogal_ucasetitles']        = $_POST['autogal_ucasetitles'];
	$pref['autogal_smallwords']         = $_POST['autogal_smallwords'];
	$pref['autogal_pagemaxdist']        = $_POST['autogal_pagemaxdist'];
	$pref['autogal_showembedlink']      = $_POST['autogal_showembedlink'];
	$pref['autogal_showsubmitinfo']     = $_POST['autogal_showsubmitinfo'];
	$pref['autogal_showpeakmemory']     = $_POST['autogal_showpeakmemory'];
	$pref['autogal_timefmtlatest']      = $_POST['autogal_timefmtlatest'];
	$pref['autogal_timefmtsubmit']      = $_POST['autogal_timefmtsubmit'];
	$pref['autogal_timefmttopscore']    = $_POST['autogal_timefmttopscore'];
	$pref['autogal_timefmtthumb']       = $_POST['autogal_timefmtthumb'];
	$pref['autogal_timefmtlog']         = $_POST['autogal_timefmtlog'];
	$pref['autogal_timefmtcomment']     = $_POST['autogal_timefmtcomment'];
	$pref['autogal_timefmtlatcomm']     = $_POST['autogal_timefmtlatcomm'];
	$pref['autogal_showreviewcount']    = $_POST['autogal_showreviewcount'];
	$pref['autogal_showdateordname']    = $_POST['autogal_showdateordname'];
	$pref['autogal_showdateorddate']    = $_POST['autogal_showdateorddate'];
	$pref['autogal_maxgalsperpage']     = $_POST['autogal_maxgalsperpage'];
	$pref['autogal_showsubgaltopcap']   = $_POST['autogal_showsubgaltopcap'];
	$pref['autogal_showfiletopcap']     = $_POST['autogal_showfiletopcap'];
	$pref['autogal_subgaltopcapclass']  = $_POST['autogal_subgaltopcapclass'];
	$pref['autogal_subgalbotcapclass']  = $_POST['autogal_subgalbotcapclass'];
	$pref['autogal_filetopcapclass']    = $_POST['autogal_filetopcapclass'];
	$pref['autogal_filebotcapclass']    = $_POST['autogal_filebotcapclass'];
	$pref['autogal_latesttopcapclass']  = $_POST['autogal_latesttopcapclass'];
	$pref['autogal_usergaltopcapclass'] = $_POST['autogal_usergaltopcapclass'];
		
	save_prefs();
    $message = AUTOGAL_LANG_ADMIN_APPEARENCE_L2;
}

if ($message)
{
    $ns -> tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}
else
{
    ######################
    # CHECK RESIZE MODES #
    ######################
    
    # If someone would like to explain to me why these tests fail when the submit button is pressed, be my guest...
    
    $resizeMethValid = AutoGal_CheckResizeMethod($resizeMethodText);
	
    if (!$resizeMethValid)
	{
		$ns -> tablerender("", "<div style='text-align:center'>$resizeMethodText</div>");
	}
}

################
# INPUT FIELDS #
################
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L1."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L37."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L38."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_ucasetitles'".($pref['autogal_ucasetitles'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L39."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L40."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='50' name='autogal_smallwords' value='".$pref['autogal_smallwords']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L13."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L14."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showfooter'".($pref['autogal_showfooter'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L62."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L63."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showreviewcount'".($pref['autogal_showreviewcount'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L23."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L24."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showautogalver'".($pref['autogal_showautogalver'] ? " checked" : "")."></td>
</tr>".
(function_exists('memory_get_peak_usage') ? "
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L50."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L51."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showpeakmemory'".($pref['autogal_showpeakmemory'] ? " checked" : "")."></td>
</tr>" : '')."
</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L46."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L3."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L4."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='3' name='autogal_maxperpage' value='".$pref['autogal_maxperpage']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L75."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L76."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='3' name='autogal_maxgalsperpage' value='".$pref['autogal_maxgalsperpage']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L41."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L42."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='3' name='autogal_pagemaxdist' value='".$pref['autogal_pagemaxdist']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L5."</b></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='3' name='autogal_numcols' value='".$pref['autogal_numcols']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L6."</b></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='3' name='autogal_numgallcols' value='".$pref['autogal_numgallcols']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L7."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L8."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='10' name='autogal_navseperator' value='".$pref['autogal_navseperator']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L9."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L10."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showtitleingall'".($pref['autogal_showtitleingall'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L11."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L12."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showsubtitlesgal'".($pref['autogal_showsubtitlesgal'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L66."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L67.' '.AUTOGAL_LANG_ADMIN_APPEARENCE_L68."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showdateorddate'".($pref['autogal_showdateorddate'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L64."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L65.' '.AUTOGAL_LANG_ADMIN_APPEARENCE_L68."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showdateordname'".($pref['autogal_showdateordname'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L77."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L78."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showsubgaltopcap'".($pref['autogal_showsubgaltopcap'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L79."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L80."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showfiletopcap'".($pref['autogal_showfiletopcap'] ? " checked" : "")."></td>
</tr>
</table>
<br />

<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L47."</b></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L15."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L16."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='5' name='autogal_flashwidth' value='".$pref['autogal_flashwidth']."'> x <input class='tbox' type='text' size='5' name='autogal_flashheight' value='".$pref['autogal_flashheight']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L17."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L18."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='5' name='autogal_moviewidth' value='".$pref['autogal_moviewidth']."'> x <input class='tbox' type='text' size='5' name='autogal_movieheight' value='".$pref['autogal_movieheight']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L19."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L20."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='5' name='autogal_maximagewidth' value='".$pref['autogal_maximagewidth']."'> x <input class='tbox' type='text' size='5' name='autogal_maximageheight' value='".$pref['autogal_maximageheight']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L21."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L22."</span></td>
    <td style='width:50%' class='forumheader3'>
		<select name='autogal_titleheadstyle' class='tbox'>
		<option value='1'".($pref['autogal_titleheadstyle'] == 1 ? " selected='selected'" : '').">H1</option>
		<option value='2'".($pref['autogal_titleheadstyle'] == 2 ? " selected='selected'" : '').">H2</option>
		<option value='3'".($pref['autogal_titleheadstyle'] == 3 ? " selected='selected'" : '').">H3</option>
		<option value='4'".($pref['autogal_titleheadstyle'] == 4 ? " selected='selected'" : '').">H4</option>
		<option value='5'".($pref['autogal_titleheadstyle'] == 5 ? " selected='selected'" : '').">H5</option>
		<option value='6'".($pref['autogal_titleheadstyle'] == 6 ? " selected='selected'" : '').">H6</option>
		</select>
	</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L43."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L44."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showembedlink'".($pref['autogal_showembedlink'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L48."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L49."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showsubmitinfo'".($pref['autogal_showsubmitinfo'] ? " checked" : "")."></td>
</tr>
</table>
<br />

<table style='width:97%' class='fborder'>

<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L52."</b></td>
</tr>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader3'>".str_replace("[LINK]", "<a href=\"http://php.net/manual/function.strftime.php\">strftime</a>", AUTOGAL_LANG_ADMIN_APPEARENCE_L53)."</td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L54."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L55."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='30' name='autogal_timefmtlatest' value='".$pref['autogal_timefmtlatest']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L56."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L57."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='30' name='autogal_timefmtsubmit' value='".$pref['autogal_timefmtsubmit']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L58."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L59."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='30' name='autogal_timefmttopscore' value='".$pref['autogal_timefmttopscore']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L60."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L61."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='30' name='autogal_timefmtthumb' value='".$pref['autogal_timefmtthumb']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L69."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L70."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='30' name='autogal_timefmtlog' value='".$pref['autogal_timefmtlog']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L71."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L72."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='30' name='autogal_timefmtcomment' value='".$pref['autogal_timefmtcomment']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L73."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L74."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='30' name='autogal_timefmtlatcomm' value='".$pref['autogal_timefmtlatcomm']."'></td>
</tr>
</table>

<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L45."</b></td>
</tr>
<tr>
    <td style='width:30%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L25."</b></td>
    <td style='width:70%' class='forumheader3'>
		<table width='100%'>
		<tr>
			<td style='text-align:left'><input class='tbox' type='text' size='30' name='autogal_imagecellclass' value='".$pref['autogal_imagecellclass']."'></td>
			<td style='text-align:right'><table class='border'><tr><td class='".$pref['autogal_imagecellclass']."'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L26."<br /><a href=''>".AUTOGAL_LANG_ADMIN_APPEARENCE_L27."</a></td></tr></table></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
    <td style='width:30%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L28."</b></td>
	<td style='width:70%' class='forumheader3'>
		<table width='100%'>
		<tr>
			<td style='text-align:left'><input class='tbox' type='text' size='30' name='autogal_subgalleryclass' value='".$pref['autogal_subgalleryclass']."'></td>
			<td style='text-align:right'><table class='border'><tr><td class='".$pref['autogal_subgalleryclass']."'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L26."<br /><a href=''>".AUTOGAL_LANG_ADMIN_APPEARENCE_L27."</a></td></tr></table></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
    <td style='width:30%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L29."</b></td>
	<td style='width:70%' class='forumheader3'>
		<table width='100%'>
		<tr>
			<td style='text-align:left'><input class='tbox' type='text' size='30' name='autogal_navclass' value='".$pref['autogal_navclass']."'></td>
			<td style='text-align:right'><table class='border'><tr><td class='".$pref['autogal_navclass']."'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L26."&nbsp;<a href=''>".AUTOGAL_LANG_ADMIN_APPEARENCE_L27."</a></td></tr></table></td>
		</tr>
		</table>
	</td>
</tr>

<tr>
    <td style='width:30%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L81."</b></td>
	<td style='width:70%' class='forumheader3'>
		<table width='100%'>
		<tr>
			<td style='text-align:left'><input class='tbox' type='text' size='30' name='autogal_subgaltopcapclass' value='".$pref['autogal_subgaltopcapclass']."'></td>
			<td style='text-align:right'><table class='border'><tr><td class='".$pref['autogal_subgaltopcapclass']."'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L26."&nbsp;<a href=''>".AUTOGAL_LANG_ADMIN_APPEARENCE_L27."</a></td></tr></table></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
    <td style='width:30%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L82."</b></td>
	<td style='width:70%' class='forumheader3'>
		<table width='100%'>
		<tr>
			<td style='text-align:left'><input class='tbox' type='text' size='30' name='autogal_subgalbotcapclass' value='".$pref['autogal_subgalbotcapclass']."'></td>
			<td style='text-align:right'><table class='border'><tr><td class='".$pref['autogal_subgalbotcapclass']."'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L26."&nbsp;<a href=''>".AUTOGAL_LANG_ADMIN_APPEARENCE_L27."</a></td></tr></table></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
    <td style='width:30%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L83."</b></td>
	<td style='width:70%' class='forumheader3'>
		<table width='100%'>
		<tr>
			<td style='text-align:left'><input class='tbox' type='text' size='30' name='autogal_filetopcapclass' value='".$pref['autogal_filetopcapclass']."'></td>
			<td style='text-align:right'><table class='border'><tr><td class='".$pref['autogal_filetopcapclass']."'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L26."&nbsp;<a href=''>".AUTOGAL_LANG_ADMIN_APPEARENCE_L27."</a></td></tr></table></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
    <td style='width:30%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L84."</b></td>
	<td style='width:70%' class='forumheader3'>
		<table width='100%'>
		<tr>
			<td style='text-align:left'><input class='tbox' type='text' size='30' name='autogal_filebotcapclass' value='".$pref['autogal_filebotcapclass']."'></td>
			<td style='text-align:right'><table class='border'><tr><td class='".$pref['autogal_filebotcapclass']."'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L26."&nbsp;<a href=''>".AUTOGAL_LANG_ADMIN_APPEARENCE_L27."</a></td></tr></table></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
    <td style='width:30%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L85."</b></td>
	<td style='width:70%' class='forumheader3'>
		<table width='100%'>
		<tr>
			<td style='text-align:left'><input class='tbox' type='text' size='30' name='autogal_latesttopcapclass' value='".$pref['autogal_latesttopcapclass']."'></td>
			<td style='text-align:right'><table class='border'><tr><td class='".$pref['autogal_latesttopcapclass']."'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L26."&nbsp;<a href=''>".AUTOGAL_LANG_ADMIN_APPEARENCE_L27."</a></td></tr></table></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
    <td style='width:30%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L86."</b></td>
	<td style='width:70%' class='forumheader3'>
		<table width='100%'>
		<tr>
			<td style='text-align:left'><input class='tbox' type='text' size='30' name='autogal_usergaltopcapclass' value='".$pref['autogal_usergaltopcapclass']."'></td>
			<td style='text-align:right'><table class='border'><tr><td class='".$pref['autogal_usergaltopcapclass']."'>".AUTOGAL_LANG_ADMIN_APPEARENCE_L26."&nbsp;<a href=''>".AUTOGAL_LANG_ADMIN_APPEARENCE_L27."</a></td></tr></table></td>
		</tr>
		</table>
	</td>
</tr>

</table>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2'  style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updatesettings' value='".AUTOGAL_LANG_ADMIN_APPEARENCE_L30."' />
    </td>
</tr>
</table><br />
<br />
<div style='text-align:left'>
<b>".AUTOGAL_LANG_ADMIN_APPEARENCE_L31."</b><br />
<br />
".AUTOGAL_LANG_ADMIN_APPEARENCE_L32."<br />
<ol>
<li><u>".AUTOGAL_LANG_ADMIN_APPEARENCE_L33."</u><br />
	".AUTOGAL_LANG_ADMIN_APPEARENCE_L34."<br />
	<br />
	<ul>
	<li>forumheader</li>
	<li>forumheader1</li>
	<li>forumheader2</li>
	<li>forumheader3</li>
	<li>forumheader4</li>
	<li>caption</li>
	<li>button</li>
	<li>bodytable</li>
	</ul>
	<br />
</li>
<li><u>".AUTOGAL_LANG_ADMIN_APPEARENCE_L35."</u><br />
	".AUTOGAL_LANG_ADMIN_APPEARENCE_L36."
</li>
</ol>
</div>
<br />
</form>
</div>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_APPEARENCE_L0, $text);
require_once(FOOTERF);
exit;

?>
