<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        14/04/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

$eplug_name = "Auto Gallery";
$eplug_version = "3.10B";
$eplug_author = "mr_visible";
$eplug_url = "http://www.cerebralsynergy.com";
$eplug_email = "mr_visible@cerebralsynergy.com";

if (file_exists(dirname(__FILE__)."/def.php"))
{
	include_once (dirname(__FILE__)."/../../class.php");
	include_once (dirname(__FILE__)."/def.php");
	$autoGallerySQL = new db;
	$autoGallerySQL->db_Select("plugin", "plugin_version", "plugin_name='Auto Gallery' AND plugin_installflag > 0");
	list($autoGalVer) = $autoGallerySQL->db_Fetch();
	$autoGalVer = preg_replace("/[a-zA-z\s]/", '', $autoGalVer);
}
else
{
	$autoGalVer = 0;
}
 
$eplug_description = "A media/image gallery, where galleries are based on a directory structure. Thumbnails are automatically generated through Imagemagick or GD.".($autoGalVer ? "<br /><br />Current Version: $autoGalVer" : '');
$eplug_compatible = "e107v6+";
$eplug_folder = "autogallery";
$eplug_conffile = "admin_main.php";
$eplug_logo = "Images/button.png";
$eplug_caption =  "Configure Auto Gallery";
$eplug_link = TRUE;
$eplug_link_name = "Image Gallery";
$eplug_link_url = AUTOGAL_AUTOGALLERY;
$eplug_readme = "readme.txt"; 
$eplug_icon = $eplug_folder."/Images/icon.png";
$eplug_icon_small = $eplug_folder."/Images/icon_16.png";

$eplug_prefs = array
(
    # 0.1 - 0.2
	"autogal_title" => 'Auto Gallery',
    "autogal_numcols" => 4,
    "autogal_showfooter" => 1,
    "autogal_subgalleryclass" => 'forumheader4',
    "autogal_imagecellclass" => 'forumheader3',
    "autogal_navclass" => 'caption2',
    "autogal_thumbwidth" => 100,
    "autogal_thumbheight" => 100,
    "autogal_keepaspect" => 1,
    "autogal_navseperator" => ' &gt;&gt; ',
    "autogal_rootname" => 'Root',
	# 1.0
	"autogal_revuploaduc" => 253,
    "autogal_uploadmaxsize" => 5242880,
    "autogal_emailtofriend" => 1,
    "autogal_numgallcols" => 2,
    "autogal_defaultetfcom" => "Check this out!",
	# 1.5
	"autogal_maximagewidth" => 800,
	"autogal_maximageheight" => 600,
	"autogal_maxperpage" => 20,
	"autogal_gallerydir" => "",
	# 1.6
	"autogal_showautogalver" => 1,
	"autogal_showadminmenu" => 1,
	"autogal_uploadnumber" => 5,
	# 1.7
	"autogal_chmodwarnoff" => 0,
	# 1.8
	"autogal_adminreviewuc" => 254,
	"autogal_showsubtitlesgal" => 1,
	"autogal_flashwidth" => 550,
	"autogal_flashheight" => 400,
	# 1.83
	"autogal_imkanigif1st" => 1,
	"autogal_galthumbwidth" => 150,
	"autogal_galthumbheight" => 150,
	# 1.84
	"autogal_shownewest" => 1,
	"autogal_shownewestinroot" => 0,
	# 1.90
	"autogal_showinnewwindow" => 0,
	"autogal_newwindowargs" => 'width=800,height=600,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,copyhistory=no,resizable=yes',
	"autogal_moviewidth" => 550,
	"autogal_movieheight" => 400,
	# 2.0 BETA
	"autogal_autothumb" => 1,
	"autogal_metaon" => 0,
	"autogal_metacomments" => 0,
	"autogal_metaviewhits" => 0,
	"autogal_metaemailhits" => 0,
	"autogal_titleheadstyle" => 1,
	"autogal_showerrorlog" => 1,
	"autogal_generatedebuglog" => 0,
	"autogal_enablesearch" => 1,
	"autogal_searchmaxresults" => 20,
	"autogal_xmlsearch" => 1,
	# 2.1 BETA
	"autogal_largeimgnewwindow" => 0,
	"autogal_showtitleingall" => 1,
	# 2.20
	"autogal_metacommentsbb" => 1,
	"autogal_apacheindexignore" => 0,
	"autogal_apachedenyexts" => 0,
	"autogal_apacheleechprotect" => 0,
	"autogal_apacheallowedsites" => '',
	"autogal_apacheleechimage" => '',
	"autogal_randomdefaultimg" => 0,
	"autogal_sortdatectime" => 0,
	# 2.40
	"autogal_uploadexts" => AUTOGAL_SUPPORTEDEXTS,
	"autogal_ucasetitles" => 1,
	"autogal_smallwords" => 'of,a,the,and,an,or,nor,but,if,then,else,when,up,at,from,by,on,for,in,to,this,is',
	"autogal_metaratings" => 0,
	"autogal_rateclass" => 253,
	"autogal_rateiniframe" => 0,
	"autogal_rateiframeheight" => 90,
	# 2.50
	"autogal_wmarkimage" => '',
	"autogal_wmarkintensity" => 30,
	"autogal_wmarkxalign" => 'r',
	"autogal_wmarkyalign" => 'b',
	"autogal_wmarkxoffset" => 0,
	"autogal_wmarkyoffset" => 0,
	"autogal_wmarkauto" => 0,
	"autogal_wmarknosmall" => 1,
	"autogal_arctopscores" => 0,
	"autogal_arcmaxtopscores" => 5,
	"autogal_latestcomms" => 0,
	"autogal_maxlatestcomms" => 10,
	"autogal_lcmaxtextlength" => 100,
	"autogal_lcstripbbcode" => 1,
	# 2.54
	"autogal_searchsmallform" => 1,
	# 2.55
	"autogal_pagemaxdist" => 2,
	"autogal_arcadeusexmltrack" => 0,
	# 2.60
	"autogal_resizepreviewimgs" => 1,
	"autogal_slidesenable" => 1, 
	"autogal_slidesnewwindow" => 1,
	"autogal_slidenwinwidth" => 800, 
	"autogal_slidenwinheight" => 600, 
	"autogal_slidenwintoobar" => 0, 
	"autogal_slidenwinlocbar" => 0, 
	"autogal_slidenwindirect" => 0, 
	"autogal_slidenwinstsbar" => 1,
	"autogal_slidenwinmnubar" => 0, 
	"autogal_slidenwinscrbar" => 1, 
	"autogal_slidenwincphist" => 1, 
	"autogal_slidenwinresize" => 1, 
	"autogal_slidenwinexargs" => "", 
	"autogal_slidebodyclass" => "",
	"autogal_slidebodystyle" => "text-align:center",
	"autogal_showembedlink" => 0,
	"autogal_shownwinwidth" => 800, 
	"autogal_shownwinheight" => 600, 
	"autogal_shownwintoobar" => 0, 
	"autogal_shownwinlocbar" => 0, 
	"autogal_shownwindirect" => 0, 
	"autogal_shownwinstsbar" => 1,
	"autogal_shownwinmnubar" => 0, 
	"autogal_shownwinscrbar" => 1, 
	"autogal_shownwincphist" => 1, 
	"autogal_shownwinresize" => 1, 
	"autogal_shownwinexargs" => "",
	# 2.61
	"autogal_enablesearche107" => 1,
	# 2.65 BETA
	"autogal_checksubgalvclass" => 0,
	"autogal_enabledbcache" => 0,
	# 3.00 BETA
	"autogal_autosizegalthumbs" => 0,
	"autogal_showsubmitinfo" => 1,
	"autogal_showpeakmemory" => 0,
	"autogal_showreviewcount" => 0,
	"autogal_enablegaldispord" => 0,
	"autogal_defaultdisporder" => 'nameasc',
	"autogal_showdateordname" => 0,
	"autogal_showdateorddate" => 1,
	"autogal_timefmtlatest" => "%A, %d %B %Y, %I:%M%p",
	"autogal_timefmtsubmit" => "%d %b, %I:%M%p",
	"autogal_timefmttopscore" => "%A %d %B %Y - %H:%M:%S",
	"autogal_timefmtthumb" => "%m-%b-%y %I:%M%p",
	"autogal_timefmtlog" => "%b %d %Y, %I:%M%p",
	"autogal_timefmtcomment" => "%A %d %B %Y - %H:%M:%S",
	"autogal_timefmtlatcomm" => "%d %b : %H:%M",
	"autogal_authcachelatest" => 0,
	"autogal_authcachesearch" => 0,
	"autogal_usequickgaldetect" => 0,
	"autogal_usethumbnailcache" => 0,
	"autogal_nofilevalidation" => 0,
	"autogal_defthumbgallery" => 0,
	"autogal_defthumbimage" => 1,
	"autogal_defthumbaudio" => 1,
	"autogal_defthumbmovie" => 1,
	"autogal_defthumbanimation" => 1,
	# 3.01 BETA
	"autogal_checklatestvclass" => 1,
	"autogal_checksearchvclass" => 1,
	"autogal_checklcommsvclass" => 1,
	"autogal_checkuploadvclass" => 1,
	"autogal_userclasscache" => 0,
	# 3.10 BETA
	"autogal_maxgalsperpage" => 0,
	"autogal_showsubgaltopcap" => 1,
	"autogal_showfiletopcap" => 1,
	"autogal_subgaltopcapclass" => 'caption',
	"autogal_subgalbotcapclass" => 'forumheader2',
	"autogal_filetopcapclass" => 'caption',
	"autogal_filebotcapclass" => 'forumheader2',
	"autogal_latesttopcapclass" => 'caption',
	"autogal_usergaltopcapclass" => 'caption',
);

$eplug_done = "
$eplug_name Installation Successful!<br />
<br />
A link named \"$eplug_link_name\" has been placed in your main links. You can rename it in the \"Links\" admin menu. Place your images and directories (through FTP) in the \"e107_plugins/autogallery/Gallery\" folder.<br />
<br />
<font color='red'><b>!!!!IMPORTANT!!!!</b></font><br />
Please run the <a href='".e_PLUGINS."$eplug_folder/admin_dochmod.php'>chmod script</a> to complete!<br />
<br />";

$upgrade_add_prefs = array();
$prefVersions = array(); 
$versionNotes = '';

######################
# VERSION 1.00
######################
if ($autoGalVer < 1.00)
{
	$prefVersions[] = "1.00";
	$verPrefs = array
	(
		"autogal_revuploaduc" => 253,
		"autogal_uploadmaxsize" => 5242880,
		"autogal_emailtofriend" => 1,
		"autogal_numgallcols" => 2,
		"autogal_defaultetfcom" => "Check this out!",
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 1.50
######################
if ($autoGalVer < 1.50)
{
	$prefVersions[] = "1.50";
	$verPrefs = array
	(
		"autogal_maximagewidth" => 800,
		"autogal_maximageheight" => 600,
		"autogal_maxperpage" => 20,
		"autogal_gallerydir" => "",	
	);
	
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 1.60
######################
if ($autoGalVer < 1.60)
{	
	$prefVersions[] = "1.60";
	$verPrefs = array
	(
		"autogal_showautogalver" => 1,
		"autogal_showadminmenu" => 1,
		"autogal_uploadnumber" => 5,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 1.70
######################
if ($autoGalVer < 1.70)
{	
	$prefVersions[] = "1.70";
	$verPrefs = array
	(
		"autogal_chmodwarnoff" => 0,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 1.80
######################
if ($autoGalVer < 1.80)
{	
	$prefVersions[] = "1.80";
	$verPrefs = array
	(
		"autogal_adminreviewuc" => 254,
		"autogal_showsubtitlesgal" => 1,
		"autogal_flashwidth" => 550,
		"autogal_flashheight" => 400,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 1.83
######################
if ($autoGalVer < 1.83)
{
	$prefVersions[] = "1.83";
	$verPrefs = array
	(
		"autogal_imkanigif1st" => 1,
		"autogal_galthumbwidth" => 150,
		"autogal_galthumbheight" => 150,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 1.84
######################
if ($autoGalVer < 1.84)
{
	$prefVersions[] = "1.84";
	$verPrefs = array
	(
		"autogal_shownewest" => 1,
		"autogal_shownewestinroot" => 0,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 1.90
######################
if ($autoGalVer < 1.90)
{
	$prefVersions[] = "1.90";
	$verPrefs = array
	(
		"autogal_showinnewwindow" => 0,
		"autogal_newwindowargs" => 'width=800,height=600,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,copyhistory=no,resizable=yes',
		"autogal_moviewidth" => 550,
		"autogal_movieheight" => 400,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 2.00
######################
if ($autoGalVer < 2.00)
{
	$prefVersions[] = "2.00";
	$verPrefs = array
	(
		"autogal_autothumb" => 1,
		"autogal_metaon" => 0,
		"autogal_metacomments" => 0,
		"autogal_metaviewhits" => 0,
		"autogal_metaemailhits" => 0,
		"autogal_titleheadstyle" => 1,
		"autogal_showerrorlog" => 1,
		"autogal_generatedebuglog" => 0,
		"autogal_enablesearch" => 1,
		"autogal_searchmaxresults" => 20,
		"autogal_xmlsearch" => 1,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 2.10
######################
if ($autoGalVer < 2.10)
{
	$prefVersions[] = "2.10";
	$verPrefs = array
	(
		"autogal_largeimgnewwindow" => 0,
		"autogal_showtitleingall" => 1,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 2.20
######################
if ($autoGalVer < 2.20)
{
	$prefVersions[] = "2.20";
	$verPrefs = array
	(
		"autogal_metacommentsbb" => 1,
		"autogal_apacheindexignore" => 0,
		"autogal_apachedenyexts" => 0,
		"autogal_apacheleechprotect" => 0,
		"autogal_apacheallowedsites" => '',
		"autogal_apacheleechimage" => '',
		"autogal_randomdefaultimg" => 0,
		"autogal_sortdatectime" => 0,
	);
	$upgrade_add_prefs += $verPrefs;
	
	$versionNotes .= "<u>2.20</u><br />For this version you will lose all of your gallery user classes, 
	you must reset them all. In addition to this you must also manually move Gallery.xml from the base 
	directory to the 'Configuration' gallery if you wish to keep meta information for the root gallery
	(such as comments). Please also run the chmod script.<br /><br />";
	
}

######################
# VERSION 2.40
######################
if ($autoGalVer < 2.40)
{
	$prefVersions[] = "2.40";
	$verPrefs = array
	(
		"autogal_uploadexts" => AUTOGAL_SUPPORTEDEXTS,
		"autogal_ucasetitles" => 1,
		"autogal_smallwords" => 'of,a,the,and,an,or,nor,but,if,then,else,when,up,at,from,by,on,for,in,to,this,is',
		"autogal_metaratings" => 0,
		"autogal_rateclass" => 253,
		"autogal_rateiniframe" => 0,
		"autogal_rateiframeheight" => 90,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 2.50
######################
if ($autoGalVer < 2.50)
{
	$prefVersions[] = "2.50";
	$verPrefs = array
	(
		"autogal_wmarkimage" => '',
		"autogal_wmarkintensity" => 30,
		"autogal_wmarkxalign" => 'r',
		"autogal_wmarkyalign" => 'b',
		"autogal_wmarkxoffset" => 0,
		"autogal_wmarkyoffset" => 0,
		"autogal_wmarkauto" => 0,
		"autogal_wmarknosmall" => 1,
		"autogal_arctopscores" => 0,
		"autogal_arcmaxtopscores" => 5,
		"autogal_latestcomms" => 0,
		"autogal_maxlatestcomms" => 10,
		"autogal_lcmaxtextlength" => 100,
		"autogal_lcstripbbcode" => 1,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 2.54
######################
if ($autoGalVer < 2.54)
{
	$prefVersions[] = "2.54";
	$verPrefs = array
	(
		"autogal_searchsmallform" => 1,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 2.55
######################
if ($autoGalVer < 2.55)
{
	$prefVersions[] = "2.55";
	$verPrefs = array
	(
		"autogal_pagemaxdist" => 2,
		"autogal_arcadeusexmltrack" => 0,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 2.60
######################
if ($autoGalVer < 2.60)
{
	$prefVersions[] = "2.60";
	$verPrefs = array
	(
		"autogal_resizepreviewimgs" => 1,
		"autogal_slidesenable" => 1, 
		"autogal_slidesnewwindow" => 1,
		"autogal_slidenwinwidth" => 800, 
		"autogal_slidenwinheight" => 600, 
		"autogal_slidenwintoobar" => 0, 
		"autogal_slidenwinlocbar" => 0, 
		"autogal_slidenwindirect" => 0, 
		"autogal_slidenwinstsbar" => 1,
		"autogal_slidenwinmnubar" => 0, 
		"autogal_slidenwinscrbar" => 1, 
		"autogal_slidenwincphist" => 1, 
		"autogal_slidenwinresize" => 1, 
		"autogal_slidenwinexargs" => "", 
		"autogal_slidebodyclass" => "",
		"autogal_slidebodystyle" => "text-align:center",
		"autogal_showembedlink" => 0,
		"autogal_shownwinwidth" => 800, 
		"autogal_shownwinheight" => 600, 
		"autogal_shownwintoobar" => 0, 
		"autogal_shownwinlocbar" => 0, 
		"autogal_shownwindirect" => 0, 
		"autogal_shownwinstsbar" => 1,
		"autogal_shownwinmnubar" => 0, 
		"autogal_shownwinscrbar" => 1, 
		"autogal_shownwincphist" => 1, 
		"autogal_shownwinresize" => 1, 
		"autogal_shownwinexargs" => "", 
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 2.61
######################
if ($autoGalVer < 2.61)
{
	$prefVersions[] = "2.61";
	$verPrefs = array
	(
		"autogal_enablesearche107" => 1,
	);
	$upgrade_add_prefs += $verPrefs;
}


######################
# VERSION 2.65 BETA
######################
if ($autoGalVer < 2.65)
{
	$prefVersions[] = "2.65";
	$verPrefs = array
	(
		"autogal_checksubgalvclass" => 0,
		"autogal_enabledbcache" => 0,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 3.00 BETA
######################
if ($autoGalVer < 3.00)
{
	$prefVersions[] = "3.00";
	$verPrefs = array
	(
		"autogal_autosizegalthumbs" => 0,
		"autogal_showsubmitinfo" => 1,
		"autogal_showpeakmemory" => 0,
		"autogal_showreviewcount" => 0,
		"autogal_enablegaldispord" => 0,
		"autogal_defaultdisporder" => 'nameasc',
		"autogal_showdateordname" => 0,
		"autogal_showdateorddate" => 1,
		"autogal_timefmtlatest" => "%A, %d %B %Y, %I:%M%p",
		"autogal_timefmtsubmit" => "%d %b, %I:%M%p",
		"autogal_timefmttopscore" => "%A %d %B %Y - %H:%M:%S",
		"autogal_timefmtthumb" => "%m-%b-%y %I:%M%p",
		"autogal_timefmtlog" => "%b %d %Y, %I:%M%p",
		"autogal_timefmtcomment" => "%A %d %B %Y - %H:%M:%S",
		"autogal_timefmtlatcomm" => "%d %b : %H:%M",
		"autogal_authcachelatest" => 0,
		"autogal_authcachesearch" => 0,
		"autogal_usequickgaldetect" => 0,
		"autogal_usethumbnailcache" => 0,
		"autogal_nofilevalidation" => 0,
		"autogal_defthumbgallery" => 0,
		"autogal_defthumbimage" => 1,
		"autogal_defthumbaudio" => 1,
		"autogal_defthumbmovie" => 1,
		"autogal_defthumbanimation" => 1,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 3.01 BETA
######################
if ($autoGalVer < 3.01)
{
	$prefVersions[] = "3.01";
	$verPrefs = array
	(
		"autogal_checklatestvclass" => 1,
		"autogal_checksearchvclass" => 1,
		"autogal_checklcommsvclass" => 1,
		"autogal_checkuploadvclass" => 1,
		"autogal_userclasscache" => 0,
	);
	$upgrade_add_prefs += $verPrefs;
}

######################
# VERSION 3.10 BETA
######################
if ($autoGalVer < 3.10)
{
	$prefVersions[] = "3.10";
	$verPrefs = array
	(
		"autogal_maxgalsperpage" => 0,
		"autogal_showsubgaltopcap" => 1,
		"autogal_showfiletopcap" => 1,
		"autogal_subgaltopcapclass" => 'caption',
		"autogal_subgalbotcapclass" => 'forumheader2',
		"autogal_filetopcapclass" => 'caption',
		"autogal_filebotcapclass" => 'forumheader2',
		"autogal_latesttopcapclass" => 'caption',
		"autogal_usergaltopcapclass" => 'caption',
		"autogal_needfileupdate" => 1,
	);
	$upgrade_add_prefs += $verPrefs;
}

$upgrade_alter_tables = "";
$eplug_upgrade_done = "
$eplug_name Upgrade Successful!<br />
<br />
Preferences Settings Added for Version".(count($prefVersions) == 1 ? '' : 's').": ".implode(", ", $prefVersions)."<br />
".($versionNotes ? "<br /><b>Version Notes:</b><br />$versionNotes" : '<br />')."
<b>Please run <a href='".e_PLUGINS."$eplug_folder/admin_dochmod.php'>chmod script</a> to complete</b><br />
<br />
<a href='".e_PLUGINS."$eplug_folder/$eplug_conffile'>Admin Auto Gallery</a>";

?>
