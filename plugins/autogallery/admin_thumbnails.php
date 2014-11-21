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

require_once(e_ADMIN."auth.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."userclass_class.php");

###################
# SET PREFERENCES #
###################
if(IsSet($_POST['updatesettings'])||IsSet($_POST['resetthumbs']))
{
    $pref['autogal_thumbwidth']        = $_POST['autogal_thumbwidth'];
    $pref['autogal_thumbheight']       = $_POST['autogal_thumbheight'];
    $pref['autogal_keepaspect']        = $_POST['autogal_keepaspect'];
	$pref['autogal_imkanigif1st']      = $_POST['autogal_imkanigif1st'];
	$pref['autogal_showerrorlog']      = $_POST['autogal_showerrorlog'];
	$pref['autogal_generatedebuglog']  = $_POST['autogal_generatedebuglog'];
	$pref['autogal_autothumb'] 		   = $_POST['autogal_autothumb'];
	$pref['autogal_galthumbwidth']     = $_POST['autogal_galthumbwidth'];
	$pref['autogal_galthumbheight']    = $_POST['autogal_galthumbheight'];
	$pref['autogal_defthumbgallery']   = $_POST['autogal_defthumbgallery'];
	$pref['autogal_defthumbimage']     = $_POST['autogal_defthumbimage'];
	$pref['autogal_defthumbaudio']     = $_POST['autogal_defthumbaudio'];
	$pref['autogal_defthumbmovie']     = $_POST['autogal_defthumbmovie'];
	$pref['autogal_defthumbanimation'] = $_POST['autogal_defthumbanimation'];
	$pref['autogal_autosizegalthumbs'] = $_POST['autogal_autosizegalthumbs'];
	$pref['image_owner']     		   = $_POST['image_owner'];
	
    save_prefs();
    $message = AUTOGAL_LANG_ADMIN_THUMBS_L1;
}

####################
# RESET THUMBNAILS #
####################
if (IsSet($_POST['resetthumbs']))
{
    $thumbImages = AutoGal_ListDirectory($pref['autogal_gallerydir'], "^".AUTOGAL_THUMBPREFIX);        
    foreach ($thumbImages as $thumbImage)
    {
        unlink ($thumbImage);
    }
    $message .= "<br /><br />".AUTOGAL_LANG_ADMIN_THUMBS_L2;
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
<form method='post' action='".e_SELF."'>
<br />
<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <b>".AUTOGAL_LANG_ADMIN_THUMBS_L3."<b>
    </td>
</tr>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader2'>
        <span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L4." <a href='".e_ADMIN."image.php'>".AUTOGAL_LANG_ADMIN_THUMBS_L5."</a></span>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L6."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L7."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_autothumb'".($pref['autogal_autothumb'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L8."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L9."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='5' name='autogal_thumbwidth' value='".$pref['autogal_thumbwidth']."'> x <input class='tbox' type='text' size='5' name='autogal_thumbheight' value='".$pref['autogal_thumbheight']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L10."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L12."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='5' name='autogal_galthumbwidth' value='".$pref['autogal_galthumbwidth']."'> x <input class='tbox' type='text' size='5' name='autogal_galthumbheight' value='".$pref['autogal_galthumbheight']."'></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L13."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L14."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_imkanigif1st'".($pref['autogal_imkanigif1st'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L15."</b></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_keepaspect'".($pref['autogal_keepaspect'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L61."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L62."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_autosizegalthumbs'".($pref['autogal_autosizegalthumbs'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L16."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L17."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_showerrorlog'".($pref['autogal_showerrorlog'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L18."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L19." <a href=\"".AUTOGAL_VIEWDEBUGLOG."\">".AUTOGAL_LANG_ADMIN_THUMBS_L20."</a>.</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_generatedebuglog'".($pref['autogal_generatedebuglog'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L21."</b><br /><span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L22."</span></td>
    <td style='width:50%' class='forumheader3'><input class='tbox' type='text' size='10' name='image_owner' value='".$pref['image_owner']."'></td>
</tr>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader2'>
        <input class='button' type='submit' name='resetthumbs' value='".AUTOGAL_LANG_ADMIN_THUMBS_L23."' /><br />
		<span class='smalltext'>".AUTOGAL_LANG_ADMIN_THUMBS_L24."</span>
    </td>
</tr>
</table>
<br />

<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L48."</b></td>
</tr>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader2'>
        <span class='smalltext'>".str_replace("[DIR]", AUTOGAL_IMAGESDIRABS, AUTOGAL_LANG_ADMIN_THUMBS_L59)."</span>
    </td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L49."</b><br /><span class='smalltext'>".str_replace("[LINK]", "<a href=\"".AUTOGAL_UNAVAILTHUMB_GALLERY."\">".AUTOGAL_LANG_ADMIN_THUMBS_L60."</a>", AUTOGAL_LANG_ADMIN_THUMBS_L50)."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_defthumbgallery'".($pref['autogal_defthumbgallery'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L51."</b><br /><span class='smalltext'>".str_replace("[LINK]", "<a href=\"".AUTOGAL_UNAVAILTHUMB_IMAGE."\">".AUTOGAL_LANG_ADMIN_THUMBS_L60."</a>",AUTOGAL_LANG_ADMIN_THUMBS_L52)."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_defthumbimage'".($pref['autogal_defthumbimage'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L53."</b><br /><span class='smalltext'>".str_replace("[LINK]", "<a href=\"".AUTOGAL_UNAVAILTHUMB_AUDIO."\">".AUTOGAL_LANG_ADMIN_THUMBS_L60."</a>",AUTOGAL_LANG_ADMIN_THUMBS_L54)."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_defthumbaudio'".($pref['autogal_defthumbaudio'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L55."</b><br /><span class='smalltext'>".str_replace("[LINK]", "<a href=\"".AUTOGAL_UNAVAILTHUMB_MOVIE."\">".AUTOGAL_LANG_ADMIN_THUMBS_L60."</a>",AUTOGAL_LANG_ADMIN_THUMBS_L56)."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_defthumbmovie'".($pref['autogal_defthumbmovie'] ? " checked" : "")."></td>
</tr>
<tr>
    <td style='width:50%' class='forumheader3'><b>".AUTOGAL_LANG_ADMIN_THUMBS_L57."</b><br /><span class='smalltext'>".str_replace("[LINK]", "<a href=\"".AUTOGAL_UNAVAILTHUMB_ANIMATION."\">".AUTOGAL_LANG_ADMIN_THUMBS_L60."</a>",AUTOGAL_LANG_ADMIN_THUMBS_L58)."</span></td>
    <td style='width:50%' class='forumheader3'><input type='checkbox' name='autogal_defthumbanimation'".($pref['autogal_defthumbanimation'] ? " checked" : "")."></td>
</tr>
</table>
<br />

<table style='width:97%' class='fborder'>
<tr style='vertical-align:top'>
    <td colspan='2' style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updatesettings' value='".AUTOGAL_LANG_ADMIN_THUMBS_L25."' />
    </td>
</tr>
</table>
<br />
<div style='text-align:left'>
<b>".AUTOGAL_LANG_ADMIN_THUMBS_L26."</b>
<ol>
<li><u>".AUTOGAL_LANG_ADMIN_THUMBS_L27."</u><br />
	".AUTOGAL_LANG_ADMIN_THUMBS_L28."<br />
	<br />
	".AUTOGAL_LANG_ADMIN_THUMBS_L29."<br />
	<br>
	".AUTOGAL_LANG_ADMIN_THUMBS_L30."
	<ul>
	<li>/usr/local/bin/</li>
	<li>/usr/local/sbin/</li>
	<li>/usr/bin/</li>
	<li>/usr/sbin/</li>
	<li>/bin/</li>
	<li>/sbin/</li>
	<li>/opt/bin/</li>
	<li>/usr/X11R6/bin/</li>
	</ul>
	<br />
	".AUTOGAL_LANG_ADMIN_THUMBS_L31."
	<ul>
	<li>C:/".AUTOGAL_LANG_ADMIN_THUMBS_L32."/ImageMagick</li>
	<li>C:/".AUTOGAL_LANG_ADMIN_THUMBS_L32."/ImageMagick-&lt;".AUTOGAL_LANG_ADMIN_THUMBS_L33."&gt;</li>
	<li>C:/ImageMagick</li>
	<li>C:/ImageMagick-&lt;".AUTOGAL_LANG_ADMIN_THUMBS_L33."&gt;</li>
	</ul>
	<br />
	".AUTOGAL_LANG_ADMIN_THUMBS_L34."<br />
	<br />
</li>
<li><u>".AUTOGAL_LANG_ADMIN_THUMBS_L35."</u><br />
	".AUTOGAL_LANG_ADMIN_THUMBS_L36."<br />
	<br />
	".AUTOGAL_LANG_ADMIN_THUMBS_L37."<br />
	<br />
	<ul>
	<li>".AUTOGAL_LANG_ADMIN_THUMBS_L38." - ".AUTOGAL_LANG_ADMIN_THUMBS_L39."</li>
	<li>".AUTOGAL_LANG_ADMIN_THUMBS_L40." - ".AUTOGAL_LANG_ADMIN_THUMBS_L39."</li>
	<li>".AUTOGAL_LANG_ADMIN_THUMBS_L41." - ".AUTOGAL_LANG_ADMIN_THUMBS_L39."</li>
	</ul>
	<br />
	
	".AUTOGAL_LANG_ADMIN_THUMBS_L42."<br />
	<br />
	chmod -R &lt;".AUTOGAL_LANG_ADMIN_THUMBS_L43."&gt;/e107_plugins/autogallery/Gallery<br />
	<br />
	<b>".AUTOGAL_LANG_ADMIN_THUMBS_L44." <a href='".AUTOGAL_DOCHMOD."'>".AUTOGAL_LANG_ADMIN_THUMBS_L45."</a>.</b>
</li>
</ol>
</div>
<br />
<a href='http://www.cerebralsynergy.com'><img style='border:0' alt='Cerebral Synergy' src='".e_PLUGINS."autogallery/Images/button.png' /></a><br />
<a href='".AUTOGAL_SUPPORTLINK."'>".AUTOGAL_LANG_ADMIN_THUMBS_L46."</a><br />
</form>
</div>";

$ns -> tablerender(AUTOGAL_LANG_ADMIN_THUMBS_L47, $text);
if ($resizeMethValid) $ns -> tablerender("", "<div style='text-align:center'>$resizeMethodText</div>");
require_once(e_ADMIN."footer.php");
exit;

?>

