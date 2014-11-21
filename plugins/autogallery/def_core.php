<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.xx
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        22/08/2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

//

include_once(dirname(__FILE__)."/def_basedirs.php");

if (!isset($pref)) $pref = AutoGal_GetPrefs();
if (!defined("e_PLUGINS")) define("e_PLUGINS", AutoGal_E107Path(basename(dirname(dirname(__FILE__)))));

if (!defined("AUTOGAL_CHARSET"))
{
	if (defined("CHARSET")){
		define("AUTOGAL_CHARSET", CHARSET);
	}else{
		define("AUTOGAL_CHARSET", "utf-8");
	}
}

# TRY AND WORK THESE TWO FUCKERS OUT
define('AUTOGAL_BASE', 				 AutoGal_GuessUrlPrefix().$_SERVER['HTTP_HOST'].(AUTOGAL_CUSTOMHTTPPATH ? AUTOGAL_CUSTOMHTTPPATH : AutoGal_GuessBaseHTTPPath()));
define('AUTOGAL_BASEABS', 			 (AUTOGAL_CUSTOMABSPATH ? AUTOGAL_CUSTOMABSPATH : AutoGal_GuessBaseAbsPath()));

# PATHS
define('AUTOGAL_CONFIG',             AUTOGAL_BASE."/admin_main.php");
define('AUTOGAL_GALLERYSETTINGS',    AUTOGAL_BASE."/admin_gallery.php");
define('AUTOGAL_APPEARANCESETTINGS', AUTOGAL_BASE."/admin_appearance.php");
define('AUTOGAL_VIEWADMINLOG',       AUTOGAL_BASE."/admin_viewadminlog.php");
define('AUTOGAL_REVIEWUPLOADS',      AUTOGAL_BASE."/admin_reviewuploads.php");
define('AUTOGAL_XMLMETASETTINGS',    AUTOGAL_BASE."/admin_metadata.php");
define('AUTOGAL_THUMBNAILSETTINGS',  AUTOGAL_BASE."/admin_thumbnails.php");
define('AUTOGAL_VIEWDEBUGLOG',       AUTOGAL_BASE."/admin_viewdebuglog.php");
define('AUTOGAL_DOCHMOD',            AUTOGAL_BASE."/admin_dochmod.php");
define('AUTOGAL_SECURITYSETTINGS',   AUTOGAL_BASE."/admin_security.php");
define('AUTOGAL_WATERMARKADMIN',     AUTOGAL_BASE."/admin_watermark.php");
define('AUTOGAL_LANGADMIN',          AUTOGAL_BASE."/admin_languages.php");
define('AUTOGAL_SLIDESHOWADMIN',     AUTOGAL_BASE."/admin_slideshow.php");
define('AUTOGAL_BUGREPORT',          AUTOGAL_BASE."/admin_bugreport.php");
define('AUTOGAL_CACHEADMIN',         AUTOGAL_BASE."/admin_speed.php");
define('AUTOGAL_USERACCESS',         AUTOGAL_BASE."/admin_useraccess.php");
define('AUTOGAL_DBUPDATE',           AUTOGAL_BASE."/admin_dbupdate.php");
define('AUTOGAL_USERGALLERYADMIN',   AUTOGAL_BASE."/admin_usergalleries.php");
define('AUTOGAL_FILEUPDATE',         AUTOGAL_BASE."/admin_fileupdate.php");
define('AUTOGAL_WATERMARK',          AUTOGAL_BASE."/watermark.php");
define('AUTOGAL_ADMINEDIT',          AUTOGAL_BASE."/edit.php");
define('AUTOGAL_RATING',             AUTOGAL_BASE."/rating.php");
define('AUTOGAL_UPLOAD',             AUTOGAL_BASE."/upload.php");
define('AUTOGAL_EMAILTOFRIEND',      AUTOGAL_BASE."/email.php");
define('AUTOGAL_RESIZE',             AUTOGAL_BASE."/resize.php");
define('AUTOGAL_AUTOGALLERY',        AUTOGAL_BASE."/autogallery.php");
define('AUTOGAL_SHOWIMAGE',          AUTOGAL_BASE."/showimage.php");
define('AUTOGAL_SEARCH',             AUTOGAL_BASE."/search.php");
define('AUTOGAL_STATNEWEST',         AUTOGAL_BASE."/stat_newest.php");
define('AUTOGAL_SHOWXML',            AUTOGAL_BASE."/showxml.php");
define('AUTOGAL_FLVPLAYER',          AUTOGAL_BASE."/flvplayer.swf");
define('AUTOGAL_LATESTCOMMSVIEW',    AUTOGAL_BASE."/stat_newestcomms.php");
define('AUTOGAL_SLIDESHOW',          AUTOGAL_BASE."/slideshow.php");
define('AUTOGAL_DEFGALLERYDIR',      "Gallery");
define('AUTOGAL_UPLOADDIR',          AUTOGAL_BASE."/Upload");
define('AUTOGAL_UPLOADDIRABS',       AUTOGAL_BASEABS."/Upload");
define('AUTOGAL_LANGDIR',     		 AUTOGAL_BASEABS."/Languages");
define('AUTOGAL_LOGDIR',     		 AUTOGAL_BASEABS."/Log");
define('AUTOGAL_CONFIGDIR',     	 AUTOGAL_BASEABS."/Configuration");
define('AUTOGAL_ADMINLOG',           AUTOGAL_LOGDIR."/admin.log");
define('AUTOGAL_RESIZELOG',          AUTOGAL_LOGDIR."/resize.log");
define('AUTOGAL_ERRORLOG',           AUTOGAL_LOGDIR."/error.log");
define("AUTOGAL_HTACCESS",           AUTOGAL_BASEABS."/.htaccess");
define('AUTOGAL_LATESTCOMMSXML',     AUTOGAL_CONFIGDIR."/LatestComments.xml");
define('AUTOGAL_ARCADEPLAYERSXML',   AUTOGAL_CONFIGDIR."/ArcadePlayers.xml");
define('AUTOGAL_LTSTCOMSHANDLER',    AUTOGAL_BASEABS."/latestcomms_class.php");
define('AUTOGAL_IMGMANIPHANDLER',    AUTOGAL_BASEABS."/gdim_class.php");
define('AUTOGAL_ADMINFUNCTIONS',     AUTOGAL_BASEABS."/admin_functions.php");
define('AUTOGAL_ARCADEPLAYERS',      AUTOGAL_BASEABS."/arcadeplayers_class.php");
define('AUTOGAL_GALLERYLISTCLASS',   AUTOGAL_BASEABS."/gallerylist_class.php");
define('AUTOGAL_FLVPLAYER_LOGO',     AUTOGAL_BASE."/Images/flvplayerlogo.gif");
define('AUTOGAL_E107SEARCH',         AUTOGAL_BASE."/e107_search.php");
define('AUTOGAL_MEDIAOBJCLASS',      AUTOGAL_BASEABS."/mediaobj_class.php");
define('AUTOGAL_MEDIALISTCLASS',     AUTOGAL_BASEABS."/medialist_class.php");
define('AUTOGAL_RENDERFILE',         AUTOGAL_BASEABS."/renderfile.php");
define('AUTOGAL_RENDERMETA',         AUTOGAL_BASEABS."/rendermeta.php");
define('AUTOGAL_EDITFUNCTIONS',      AUTOGAL_BASEABS."/editobj.php");
define('AUTOGAL_ADMINACTION',        AUTOGAL_BASE."/editaction.php");
define('AUTOGAL_CREATEUSERGALLERY',  AUTOGAL_BASE."/createusergal.php");
define('AUTOGAL_USERACCESSCLASS',         AUTOGAL_BASEABS."/useraccess_class.php");

# THUMB UNAVAILABLE THUMBS
define('AUTOGAL_IMAGESDIR',               AUTOGAL_BASE."/Images");
define('AUTOGAL_IMAGESDIRABS',            AUTOGAL_BASEABS."/Images");
define('AUTOGAL_UNAVAILTHUMB',            AUTOGAL_IMAGESDIR."/thumbunavailable.png");
define('AUTOGAL_UNAVAILTHUMB_IMAGE',      AUTOGAL_IMAGESDIR."/thumbunavailable_image.png");
define('AUTOGAL_UNAVAILTHUMB_MOVIE',      AUTOGAL_IMAGESDIR."/thumbunavailable_movie.png");
define('AUTOGAL_UNAVAILTHUMB_ANIMATION',  AUTOGAL_IMAGESDIR."/thumbunavailable_animation.png");
define('AUTOGAL_UNAVAILTHUMB_AUDIO',      AUTOGAL_IMAGESDIR."/thumbunavailable_audio.png");
define('AUTOGAL_UNAVAILTHUMB_GALLERY',    AUTOGAL_IMAGESDIR."/thumbunavailable_gallery.png");
define("AUTOGAL_RATEIMAGEFULL",           AUTOGAL_IMAGESDIR."/ratefull.png");
define("AUTOGAL_RATEIMAGEHALF",           AUTOGAL_IMAGESDIR."/ratehalf.png"); 
define("AUTOGAL_RATEIMAGEBLANK",          AUTOGAL_IMAGESDIR."/rateblank.png"); 
define('AUTOGAL_WATERMARKDIR',            AUTOGAL_IMAGESDIR."/Watermarks");
define('AUTOGAL_WATERMARKDIRABS',         AUTOGAL_IMAGESDIRABS."/Watermarks");
 
# SUPPORTED EXTENSIONS
define('AUTOGAL_IMAGEEXTS', 			'bmp|jpg|jpeg|gif|png');
define('AUTOGAL_THUMBIMAGEEXTS', 	    'jpg|jpeg|gif|png');
define('AUTOGAL_FLASHEXTS', 			'swf');
define('AUTOGAL_FLVEXTS', 			    'flv');
define('AUTOGAL_WINMEDIAEXTS', 			'wmv|mpg|mpeg|avi|divx');
define('AUTOGAL_WINMEDIAEXTS_A',		'mp3|wma');
define('AUTOGAL_QUICKTIMEEXTS', 		'mov');
define('AUTOGAL_REALMEDIAEXTS', 		'rm');
define('AUTOGAL_SUPPORTEDEXTS', 		AUTOGAL_IMAGEEXTS.'|'.AUTOGAL_FLASHEXTS.'|'.AUTOGAL_WINMEDIAEXTS.'|'.AUTOGAL_WINMEDIAEXTS_A.'|'.AUTOGAL_QUICKTIMEEXTS.'|'.AUTOGAL_REALMEDIAEXTS.'|'.AUTOGAL_FLVEXTS);

# FILE EXTENSION CLASSES
define('AUTOGAL_EXTCLASS_IMAGE', 		AUTOGAL_IMAGEEXTS);
define('AUTOGAL_EXTCLASS_MOVIE', 		AUTOGAL_QUICKTIMEEXTS.'|'.AUTOGAL_WINMEDIAEXTS.'|'.AUTOGAL_REALMEDIAEXTS.'|'.AUTOGAL_FLVEXTS);
define('AUTOGAL_EXTCLASS_AUDIO', 		AUTOGAL_WINMEDIAEXTS_A);
define('AUTOGAL_EXTCLASS_ANIMATION',	AUTOGAL_FLASHEXTS);

# FILE PERMISSIONS
define("AUTOGAL_PERMSGALDIR", 		777);
define("AUTOGAL_PERMSGALMEDIA",		666);
define("AUTOGAL_PERMSGALTHUMBS", 	666);
define("AUTOGAL_PERMSGALXML", 		666);
define("AUTOGAL_PERMSLOGFILES", 	666);
define("AUTOGAL_PERMSLOGDIR", 		777);
define("AUTOGAL_PERMSUPLMEDIA", 	666);
define("AUTOGAL_PERMSUPLDIR", 		777);
define("AUTOGAL_PERMSUPLXML", 		666);
define("AUTOGAL_PERMSCFGDIR", 		777);
define("AUTOGAL_PERMSCFGXML", 		666);
define("AUTOGAL_PERMSBSEDIR", 		755);
define("AUTOGAL_PERMSHTACCESS", 	666);

# MISC SETTINGS
define('AUTOGAL_DIRCACHETABLE',          'e107_agDirCache');
define('AUTOGAL_THUMBPREFIX', 			 'th_');
define('AUTOGAL_PREVIEWIMGPREFIX',       'pv_');
define('AUTOGAL_GALLERYTHUMBFILENAME',	 '__thumbnail');
define('AUTOGAL_GALLERYXMLFILENAME',	 '__gallery.xml');
define('AUTOGAL_DEFAULTVIEWUC', 		 0);
define('AUTOGAL_DEFAULTUPLOADUC', 		 254);
define('AUTOGAL_DEFAULTADMINUC', 		 254);
define('AUTOGAL_DEFAULTMCOMMENTUC', 	 253);
define('AUTOGAL_DEFAULTGCOMMENTUC', 	 254);
define("AUTOGAL_SHOWNEWESTNUM", 		 10);
define("AUTOGAL_MINSEARCHSTRLEN", 		 3);
define("AUTOGAL_LEECHIMAGE",             "/Images/leech.png");
define("AUTOGAL_MAXRATE",                5);
define("AUTOGAL_SUPPORTLINK",            'http://www.cerebralsynergy.com');
define("AUTOGAL_REPORTBUGLINK",          'http://www.cerebralsynergy.com/bugs');
define("AUTOGAL_RESIZEDEBUG",            false);
define("AUTOGAL_LANGEMAILADDR",          'mr_visible.at.cerebralsynergy.(dot).com');
define("AUTOGAL_DOWNLOADURL",            'http://www.cerebralsynergy.com/download.php?view.98');
define("AUTOGAL_ARCADEMAXPLAYERXMLTIME", 48); # HOURS
define("AUTOGAL_DIRCACHEFILE",           '__cache.xml'); 
define("AUTOGAL_ADMINACTIONBOXHEIGHT",   100);
define("AUTOGAL_USERGALLERYDIR",         'user');

# USER SETTINGS FROM VERSIONS 0.1 - 1.0
//define('AUTOGAL_TITLE',             $pref['autogal_title'] );
//define('AUTOGAL_NUMCOLS',           $pref['autogal_numcols'] );
//define('AUTOGAL_NUMGALLCOLS',       $pref['autogal_numgallcols']);
//define('AUTOGAL_SHOW_FOOTER',       $pref['autogal_showfooter']);
//define('AUTOGAL_SUBGALLERYCLASS',   $pref['autogal_subgalleryclass']);
//define('AUTOGAL_IMAGECELLCLASS',    $pref['autogal_imagecellclass']);
//define('AUTOGAL_NAVCLASS',          $pref['autogal_navclass']);
//define('AUTOGAL_THUMBWIDTH',        $pref['autogal_thumbwidth']);
//define('AUTOGAL_THUMBHEIGHT',       $pref['autogal_thumbheight']);
//define('AUTOGAL_KEEPASPECT',        $pref['autogal_keepaspect']);
//define('AUTOGAL_NAVSEPERATOR',      $pref['autogal_navseperator']);
//define('AUTOGAL_ROOTNAME',          $pref['autogal_rootname']);
//define('AUTOGAL_UPLOADMAXSIZE',     $pref['autogal_uploadmaxsize']);
//define('AUTOGAL_SHOWEMAILTOFRIEND', $pref['autogal_emailtofriend']);
//define('AUTOGAL_REVUPLOADUC',       $pref['autogal_revuploaduc']);
//define('AUTOGAL_DEFAULTETFCOM',     $pref['autogal_defaultetfcom']);

# USER SETTINGS FROM VERSION 1.5
//define('AUTOGAL_MAXIMAGEWIDTH',     $pref['autogal_maximagewidth']);
//define('AUTOGAL_MAXIMAGEHEIGHT',    $pref['autogal_maximageheight']);
//define('AUTOGAL_MAXPERPAGE',        $pref['autogal_maxperpage']);
//define('AUTOGAL_GALLERYDIR',        ($pref['autogal_gallerydir'] ? $pref['autogal_gallerydir'] : AUTOGAL_DEFGALLERYDIR));

# USER SETTINGS FROM VERSION 1.6
//define('AUTOGAL_SHOWAUTOGALVER',    $pref['autogal_showautogalver']);
define('AUTOGAL_SHOWADMINMENU',     1);
//define('AUTOGAL_UPLOADNUMBER', 		$pref['autogal_uploadnumber']);

# USER SETTINGS FROM VERSION 1.7
//define('AUTOGAL_CHMODWARNOFF', 		$pref['autogal_chmodwarnoff']);

# USER SETTINGS FROM VERSION 1.8
//define('AUTOGAL_ADMINREVIEWUC',     $pref['autogal_adminreviewuc']);
//define('AUTOGAL_SHOWSUBTITLESGAL',  $pref['autogal_showsubtitlesgal']);
//define('AUTOGAL_FLASHWIDTH',        $pref['autogal_flashwidth']);
//define('AUTOGAL_FLASHHEIGHT',       $pref['autogal_flashheight']);

# USER SETTINGS FROM VERSION 1.83
//define('AUTOGAL_IMKANIGIF1ST',      $pref['autogal_imkanigif1st']);
//define('AUTOGAL_GALTHUMBWIDTH',     $pref['autogal_galthumbwidth']);
//define('AUTOGAL_GALTHUMBHEIGHT',    $pref['autogal_galthumbheight']);

# USER SETTINGS FROM VERSION 1.84
//define("AUTOGAL_SHOWNEWESTLINK",    $pref['autogal_shownewest']);
//define("AUTOGAL_SHOWNEWESTINROOT",  $pref['autogal_shownewestinroot']);

# USER SETTINGS FROM VERSION 1.87
//define("AUTOGAL_SHOWINNEWWINDOW",   $pref['autogal_showinnewwindow']);
//define("AUTOGAL_NEWWINDOWARGS",     $pref['autogal_newwindowargs']);
//define("AUTOGAL_MOVIEWIDTH",     	$pref['autogal_moviewidth']);
//define("AUTOGAL_MOVIEHEIGHT",   	$pref['autogal_movieheight']);

# USER SETTINGS FROM VERSION 2.0
//define("AUTOGAL_AUTOTHUMB",         $pref['autogal_autothumb']);
//define("AUTOGAL_USEXMLMETACOMS",    $pref['autogal_metacomments']);
//define("AUTOGAL_USEXMLMETAVHITS",   $pref['autogal_metaviewhits']);
//define("AUTOGAL_USEXMLMETAEHITS",   $pref['autogal_metaemailhits']);
//define("AUTOGAL_TITLEHEADSTYLE",    $pref['autogal_titleheadstyle']);
//define("AUTOGAL_SHOWERRORLOG",      $pref['autogal_showerrorlog']);
//define("AUTOGAL_GENERATEDEBUGLOG",  $pref['autogal_generatedebuglog']);
//define("AUTOGAL_ENABLESEARCH",  	$pref['autogal_enablesearch']);
//define("AUTOGAL_SEARCHMAXRESULTS", 	$pref['autogal_searchmaxresults']);
//define("AUTOGAL_XMLSEARCH", 		$pref['autogal_xmlsearch']);

# USER SETTINGS FROM VERSION 2.1
//define("AUTOGAL_LARGEIMGNEWWINDOW",	$pref['autogal_largeimgnewwindow']);
//define("AUTOGAL_SHOWTITLEINGALL", 	$pref['autogal_showtitleingall']);

# USER SETTINGS FROM VERSION 2.2
//define("AUTOGAL_COMMENTBBCODE",      $pref['autogal_metacommentsbb']);
#define("AUTOGAL_APACHEINDEXIGNORE",  $pref['autogal_apacheindexignore']);
#define("AUTOGAL_APACHEDENYEXTS",     $pref['autogal_apachedenyexts']);
#define("AUTOGAL_APACHELEECHPROTECT", $pref['autogal_apacheleechprotect']);
#define("AUTOGAL_APACHEALLOWEDSITES", $pref['autogal_apacheallowedsites']);
#define("AUTOGAL_APACHELEECHIMAGE",   $pref['autogal_apacheleechimage']);
#define("AUTOGAL_RANDOMDEFAULTIMG",   $pref['autogal_randomdefaultimg']);
//define("AUTOGAL_SORTDATECTIME",      $pref['autogal_sortdatectime']);

# USER SETTINGS FROM VERSION 2.4
//define("AUTOGAL_UPLOADEXTS",         $pref['autogal_uploadexts']);
//define("AUTOGAL_UCASETITLES",        $pref['autogal_ucasetitles']);
//define('AUTOGAL_SMALLWORDS',         $pref['autogal_smallwords']);
//define("AUTOGAL_USEXMLMETARATINGS",  $pref['autogal_metaratings']);
//define("AUTOGAL_RATECLASS",          $pref['autogal_rateclass']);
//define("AUTOGAL_RATEIFRAME",         $pref['autogal_rateiniframe']);
//define("AUTOGAL_RATEIFRAMEHEIGHT",   $pref['autogal_rateiframeheight']);
	
# USER SETTINGS FROM VERSION 2.5
//define("AUTOGAL_WMARKIMAGE",         $pref['autogal_wmarkimage']);
//define("AUTOGAL_WMARKINTENSITY",     $pref['autogal_wmarkintensity']);
//define('AUTOGAL_WMARKXALIGN',        $pref['autogal_wmarkxalign']);
//define("AUTOGAL_WMARKYALIGN",        $pref['autogal_wmarkyalign']);
//define("AUTOGAL_WMARKXOFFSET",       $pref['autogal_wmarkxoffset']);
//define("AUTOGAL_WMARKYOFFSET",       $pref['autogal_wmarkyoffset']);
//define("AUTOGAL_WMARKAUTO",          $pref['autogal_wmarkauto']);
//define("AUTOGAL_WMARKNOSMALL",       $pref['autogal_wmarknosmall']);
//define("AUTOGAL_ARCTOPSCORES",       $pref['autogal_arctopscores']);
//define("AUTOGAL_ARCMAXTOPSCORES",    $pref['autogal_arcmaxtopscores']);
//define("AUTOGAL_DOLATESTCOMMS",      $pref['autogal_latestcomms']);
//define("AUTOGAL_MAXLATESTCOMMS",     $pref['autogal_maxlatestcomms']);
//define("AUTOGAL_LCMAXTEXTLENGTH",    $pref['autogal_lcmaxtextlength']);
//define("AUTOGAL_LCSTRIPBBCODE",      $pref['autogal_lcstripbbcode']);

# USER SETTINGS FROM VERSION 2.54
#define("AUTOGAL_SEARCHSMALLFORM",    $pref['autogal_searchsmallform']);

# USER SETTINGS FROM VERSION 2.55
//define("AUTOGAL_PAGEMAXDIST",        $pref['autogal_pagemaxdist']);
//define("AUTOGAL_ARCADEUSEXMLTRACK",  $pref['autogal_arcadeusexmltrack']);

# USER SETTINGS FROM VERSION 2.60
@define("AUTOGAL_DIRCACHEMINS",       $pref['autogal_dircachemins']); 
@define("AUTOGAL_DIRCACHEMETHOD",     $pref['autogal_dircachemethod']); 
//define("AUTOGAL_RESIZEPREVIEWIMGS",  $pref['autogal_resizepreviewimgs']); 
//define("AUTOGAL_SLIDESENABLE",       $pref['autogal_slidesenable']); 
//define("AUTOGAL_SLIDESNEWWINDOW",    $pref['autogal_slidesnewwindow']); 
//define("AUTOGAL_SLIDENWINWIDTH",     $pref['autogal_slidenwinwidth']); 
//define("AUTOGAL_SLIDENWINHEIGHT",    $pref['autogal_slidenwinheight']); 
//define("AUTOGAL_SLIDENWINTOOBAR",    $pref['autogal_slidenwintoobar']); 
//define("AUTOGAL_SLIDENWINLOCBAR",    $pref['autogal_slidenwinlocbar']); 
//define("AUTOGAL_SLIDENWINDIRECT",    $pref['autogal_slidenwindirect']); 
//define("AUTOGAL_SLIDENWINSTSBAR",    $pref['autogal_slidenwinstsbar']); 
//define("AUTOGAL_SLIDENWINMNUBAR",    $pref['autogal_slidenwinmnubar']); 
//define("AUTOGAL_SLIDENWINSCRBAR",    $pref['autogal_slidenwinscrbar']); 
//define("AUTOGAL_SLIDENWINCPHIST",    $pref['autogal_slidenwincphist']); 
//define("AUTOGAL_SLIDENWINRESIZE",    $pref['autogal_slidenwinresize']); 
//define("AUTOGAL_SLIDENWINEXARGS",    $pref['autogal_slidenwinexargs']); 
//define("AUTOGAL_SLIDEBODYCLASS",     $pref['autogal_slidebodyclass']); 
//define("AUTOGAL_SLIDEBODYSTYLE",     $pref['autogal_slidebodystyle']); 
//define("AUTOGAL_SHOWEMBEDLINK",  	 $pref['autogal_showembedlink']);
@define("AUTOGAL_USELIGHTBOX",        $pref['autogal_uselightbox']);
//define("AUTOGAL_SHOWNWINWIDTH",      $pref['autogal_shownwinwidth']); 
//define("AUTOGAL_SHOWNWINHEIGHT",     $pref['autogal_shownwinheight']); 
//define("AUTOGAL_SHOWNWINTOOBAR",     $pref['autogal_shownwintoobar']); 
//define("AUTOGAL_SHOWNWINLOCBAR",     $pref['autogal_shownwinlocbar']); 
//define("AUTOGAL_SHOWWINDIRECT",      $pref['autogal_shownwindirect']); 
//define("AUTOGAL_SHOWNWINSTSBAR",     $pref['autogal_shownwinstsbar']); 
//define("AUTOGAL_SHOWNWINMNUBAR",     $pref['autogal_shownwinmnubar']); 
//define("AUTOGAL_SHOWNWINSCRBAR",     $pref['autogal_shownwinscrbar']); 
//define("AUTOGAL_SHOWNWINCPHIST",     $pref['autogal_shownwincphist']); 
//define("AUTOGAL_SHOWNWINRESIZE",     $pref['autogal_shownwinresize']); 
//define("AUTOGAL_SHOWNWINEXARGS",     $pref['autogal_shownwinexargs']); 

# USER SETTINGS FROM VERSION 2.61
#define("AUTOGAL_ENABLESEARCHE107",   $pref['autogal_enablesearche107']); 

# USER SETTINGS FROM VERSION 2.65
//define("AUTOGAL_CHECKSUBGALVCLASS",  $pref['autogal_checksubgalvclass']);
//define("AUTOGAL_ENABLEDBCACHE",      $pref['autogal_enabledbcache']);

# USER SETTINGS FROM VERSION 3.00
//define("AUTOGAL_AUTOSIZEGALTHUMBS",  $pref['autogal_autosizegalthumbs']);
//define("AUTOGAL_SHOWSUBMITINFO",     $pref['autogal_showsubmitinfo']);
//define("AUTOGAL_SHOWPEAKMEMORY",     $pref['autogal_showpeakmemory']);
//define("AUTOGAL_SHOWREVIEWCOUNT",    $pref['autogal_showreviewcount']); 
//define("AUTOGAL_ENABLEGALDISPORD",   $pref['autogal_enablegaldispord']); 
//define("AUTOGAL_DEFAULTDISPORD",     $pref['autogal_defaultdisporder']); 
//define("AUTOGAL_SHOWDATEORDNAME",    $pref['autogal_showdateordname']); 
//define("AUTOGAL_SHOWDATEORDDATE",    $pref['autogal_showdateorddate']); 
//define("AUTOGAL_LATESTTIMEFORMAT",   $pref['autogal_timefmtlatest']);
//define("AUTOGAL_SUBMITTIMEFORMAT", 	 $pref['autogal_timefmtsubmit']);
//define("AUTOGAL_TOPSCORETIMEFORMAT", $pref['autogal_timefmttopscore']);
//define("AUTOGAL_THUMBTIMEFORMAT",    $pref['autogal_timefmtthumb']);
//define("AUTOGAL_LOGTIMEFORMAT",      $pref['autogal_timefmtlog']);
//define("AUTOGAL_COMMENTTIMEFORMAT",  $pref['autogal_timefmtcomment']);
//define("AUTOGAL_LATCOMMTIMEFORMAT",  $pref['autogal_timefmtlatcomm']);
//define("AUTOGAL_AUTHCACHELATEST",    $pref['autogal_authcachelatest']);
//define("AUTOGAL_AUTHCACHESEARCH",    $pref['autogal_authcachesearch']);
//define("AUTOGAL_USEQUICKGALDETECT",  $pref['autogal_usequickgaldetect']);
//define("AUTOGAL_USETHUMBNAILCACHE",  $pref['autogal_usethumbnailcache']);
//define("AUTOGAL_NOFILEVALIDATION",   $pref['autogal_nofilevalidation']);
//define("AUTOGAL_DEFTHUMBGALLERY",    $pref['autogal_defthumbgallery']);
//define("AUTOGAL_DEFTHUMBIMAGE",      $pref['autogal_defthumbimage']);
//define("AUTOGAL_DEFTHUMBAUDIO",      $pref['autogal_defthumbaudio']);
//define("AUTOGAL_DEFTHUMBMOVIE",      $pref['autogal_defthumbmovie']);
//define("AUTOGAL_DEFTHUMBANIMATION",  $pref['autogal_defthumbanimation']);

# USER SETTINGS FROM VERSION 3.01
//define("AUTOGAL_CHECKLATESTVCLASS",  $pref['autogal_checklatestvclass']);
//define("AUTOGAL_CHECKSEARCHVCLASS",  $pref['autogal_checksearchvclass']);
//define("AUTOGAL_CHECKLCOMMSVCLASS",  $pref['autogal_checklcommsvclass']);
//define("AUTOGAL_CHECKUPLOADVCLASS",  $pref['autogal_checkuploadvclass']);
define("AUTOGAL_USERCLASSCACHE",     0);

# USER SETTINGS FROM VERSION 3.10
@define("AUTOGAL_EDITUSERCLASS",      $pref['autogal_edituserclass']);
@define("AUTOGAL_USERGALENABLE",      $pref['autogal_usergalenable']);
@define("AUTOGAL_USERGALNAME",        $pref['autogal_usergalname']);
@define("AUTOGAL_USERGALUSERCLASS",   $pref['autogal_usergaluserclass']);
//define("AUTOGAL_MAXGALSPERPAGE",     $pref['autogal_maxgalsperpage']);
//define("AUTOGAL_SHOWSUBGALTOPCAP",   $pref['autogal_showsubgaltopcap']);
//define("AUTOGAL_SHOWFILETOPCAP",     $pref['autogal_showfiletopcap']);
//define("AUTOGAL_SUBGALTOPCAPCLASS",  $pref['autogal_subgaltopcapclass']);
//define("AUTOGAL_SUBGALBOTCAPCLASS",  $pref['autogal_subgalbotcapclass']);
//define("AUTOGAL_FILETOPCAPCLASS",    $pref['autogal_filetopcapclass']);
//define("AUTOGAL_FILEBOTCAPCLASS",    $pref['autogal_filebotcapclass']);
//define("AUTOGAL_LATESTTOPCAPCLASS",  $pref['autogal_latesttopcapclass']);
//define("AUTOGAL_USERGALTOPCAPCLASS", $pref['autogal_usergaltopcapclass']);

function AutoGal_GuessUrlPrefix()
{
	if (defined('AUTOGAL_USINGHTTPS'))
	{
		$usingHttps = AUTOGAL_USINGHTTPS;
	}
	else
	{
		$usingHttps = 'detect';
	}
	
	if ($usingHttps == 'always') return "https://";
	if ($usingHttps == 'never') return "http://";
	
	if ((array_key_exists('HTTPS', $_SERVER))&&($_SERVER['HTTPS'] == 'on'))
	{
		return "https://";
	}
	
	return "http://";
}

function AutoGal_GuessBaseHTTPPath()
{
	$httpPath = dirname(AutoGal_GetAbsHttpPath(__FILE__));
	return $httpPath;
}

function AutoGal_GuessBaseAbsPath()
{
	return str_replace("\\", "/", dirname(__FILE__));
}

# GETS e107 PREFERENCES WITHOUT USING e107 ITSELF (BECAUSE e107 IS SOME HOW SCREWING
# WITH HTTP DATA SENT BACK TO CLIENT WHEN A LANGUAGE OTHER THAN ENGLISH IS SELECTED,
# ONLY NOTICIBLE WITH IMAGES). I DON'T KNOW WHY OR HOW.
function AutoGal_GetPrefs()
{
	global $mySQLserver;
	global $mySQLuser;
	global $mySQLpassword;
	global $mySQLdefaultdb;
	global $mySQLprefix;
	
	include_once(dirname(__FILE__).'/../../etruck_config.php');
	
	$dbc = mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword) or die('Could not connect to db: ' . mysql_error());
	mysql_select_db($mySQLdefaultdb) or die('Could not select database');
	
	$result = mysql_query("SELECT value FROM ${mySQLprefix}core WHERE name='SitePrefs'");
	$row = mysql_fetch_array($result);
	$prefsSer = $row["value"];
	
	# FFS, e107 doesn't seem to use serialize/unserialize for siteprefs. WHY? WHY?!?!
	# Instead they use this nasty code to do the work.
	# Anyway, below block is ripped from arraystorage_class.php. 
	$prefsSer = '$prefs = '.trim($prefsSer).';';
	@eval($prefsSer);
	
	if (!isset($prefs) || !is_array($prefs)) 
	{
		trigger_error("Bad stored array data - <br /><br />".htmlentities($ArrayData), E_USER_ERROR);
		return false;
	}
		
	return $prefs;
}

function AutoGal_IsThumb($file)
{
	$pathInfo = pathinfo($file);
	
	if ((preg_match("/^".AUTOGAL_THUMBPREFIX."/i", $pathInfo['basename']))&&(AutoGal_IsImage($file)))
	{
		return true;
	}
	
	return false;
}

function AutoGal_IsDefaultImage($file)
{
	$pathInfo = pathinfo($file);
	
	if ((preg_match("/^".preg_quote(AUTOGAL_GALLERYTHUMBFILENAME)."/i", $pathInfo['basename']))&&(AutoGal_IsImage($file)))
	{
		return true;
	}
	
	return false;
}

function AutoGal_IsXmlFile($file)
{
	$pathInfo = pathinfo($file);
	
	if ($pathInfo['extension'] == 'xml') return 1;
	return 0;
}

function AutoGal_IsGalleryXmlFile($file)
{
	if (basename($file) == AUTOGAL_GALLERYXMLFILENAME) return 1;
	return 0;
}

function AutoGal_IsPreviewImage($file)
{
	$pathInfo = pathinfo($file);
	
	if ((preg_match("/^".preg_quote(AUTOGAL_PREVIEWIMGPREFIX)."/i", $pathInfo['basename']))&&(AutoGal_IsImage($file)))
	{
		return true;
	}
	
	return false;
}

function AutoGal_IsImage($file)
{
	return preg_match("/\.(".strtolower(AUTOGAL_IMAGEEXTS).")$/", strtolower($file));
}

function AutoGal_IsFlashVideo($file)
{
    return preg_match("/\.(".strtolower(AUTOGAL_FLVEXTS).")$/", strtolower($file));
}

function AutoGal_IsSupportedFormat($file)
{
    return preg_match("/\.(".strtolower(AUTOGAL_SUPPORTEDEXTS).")$/", strtolower($file));
}

function AutoGal_IsSpecDir($dir)
{
	return false; # Getting there...
}

function AutoGal_IsSpecFile($file)
{
	return (AutoGal_IsDefaultImage($file)||AutoGal_IsThumb($file)||AutoGal_IsPreviewImage($file)||AutoGal_IsXmlFile($file));
}

function AutoGal_IsMediaDir($dir)
{
	$filename = basename($file);
	return ((!AutoGal_IsSpecDir($dir))&&(!AutoGal_IsIllegalName($filename)));
}

function AutoGal_IsMediaFile($file)
{
	$filename = basename($file);
	return (AutoGal_IsSupportedFormat($file)&&(!AutoGal_IsSpecFile($file))&&(!AutoGal_IsIllegalName($filename)));
}

function AutoGal_Dump($var)
{
	print "<pre>";
	$dump = var_export($var, true);
	print htmlspecialchars($dump);
	print "</pre>";
}

function AutoGal_EscArgs($args)
{
	$newArgs = array();
	
	foreach ($args as $arg)
	{
		$newArgs[] = escapeshellarg($arg);
	}
	
	return $newArgs;
}

function AutoGal_IsEleInGallery($ele, $isAbsPath=0)
{
	if ($isAbsPath)
	{
		$absPath = str_replace("\\", "/", $ele);
	}
	else
	{
		$absPath = str_replace("\\", "/", AutoGal_GetAbsGalPath($ele));
	}
	
	$galAbsPath =  str_replace("\\", "/", AutoGal_GetAbsGalPath(''));
	
	if (preg_match("/^".preg_quote($galAbsPath, '/')."/i", $absPath))
	{
		return 1;
	}

	return 0;
}

function AutoGal_GetAbsGalPath($element, $useRealPath=0)
{
	$absPath = dirname(__FILE__)."/".$pref['autogal_gallerydir']."/$element";
	
	if ($useRealPath) $absPath = realpath($absPath);
	
    $absPath = str_replace("\\", '/', $absPath);
    $absPath = str_replace('//', '/', $absPath);
	$absPath = preg_replace("/\/+$/", "", $absPath);

    $absPath = AutoGal_CleanPath($absPath);
	
    return $absPath;
}


# RIPPED FROM http://au3.php.net/manual/en/function.realpath.php (bart at mediawave dot nl)
function AutoGal_CleanPath($path)
{
	$result = array();
	$prefix = '';
	if (preg_match("/^(https?\:\/\/)(.*)$/i", $path, $matches))
	{
		$prefix = $matches[1];
		$path = $matches[2];
	}
	
	$pathA = explode('/', $path);
	if (!$pathA[0]) $result[] = '';
	
	foreach ($pathA AS $key => $dir)
	{
		if ($dir == '..')
		{
			if (end($result) == '..'){
				$result[] = '..';
			}else if (!array_pop($result)) {
				$result[] = '..';
			}
		}
		elseif ($dir && $dir != '.')
		{
			$result[] = $dir;
		}
	}
	
	if (!end($pathA)) $result[] = '';
	
	return $prefix.implode('/', $result);
}

function AutoGal_GetElement($absPath)
{
	$galPath = str_replace("\\", '/', AutoGal_GetAbsGalPath(''));
	$absPath = str_replace("\\", '/', $absPath);
		
	if ($absPath == $galPath)
	{
		return '';
	}
	
	$element = '';	
	if (strpos($absPath, $galPath.'/') == 0)
		$element = str_replace($galPath.'/', "", $absPath);
	elseif (strpos($absPath, $galPath) == 0)
		$element = str_replace($galPath, "", $absPath);
		
	$element = preg_replace("/^\//", "", $element);
	$element = preg_replace("/\/$/", "", $element);
			
	return $element;
}

# RIPPED FROM PHP.NET
function AutoGal_FormatFilePerms($perms)
{
	if (($perms & 0xC000) == 0xC000) {
	   // Socket
	   $info = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
	   // Symbolic Link
	   $info = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
	   // Regular
	   $info = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
	   // Block special
	   $info = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
	   // Directory
	   $info = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
	   // Character special
	   $info = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
	   // FIFO pipe
	   $info = 'p';
	} else {
	   // Unknown
	   $info = 'u';
	}
	
	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
			   (($perms & 0x0800) ? 's' : 'x' ) :
			   (($perms & 0x0800) ? 'S' : '-'));
	
	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
			   (($perms & 0x0400) ? 's' : 'x' ) :
			   (($perms & 0x0400) ? 'S' : '-'));
	
	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
			   (($perms & 0x0200) ? 't' : 'x' ) :
			   (($perms & 0x0200) ? 'T' : '-'));
	
	return $info;
}

function AutoGal_DrawImage($imagePath)
{
	if (!AutoGal_IsSupportedFormat($imagePath)) return;
	if (AUTOGAL_RESIZEDEBUG) return;
	
	$imageInfo = pathinfo($imagePath);
	$ext = strtolower($imageInfo['extension']);
	$imgData = file_get_contents($imagePath);
	
	if ($ext == 'jpg') $ext = 'jpeg';
	
	header("Last-Modified: ".date('r'));
	header("Accept-Ranges: bytes");
	
	if (preg_match("/^(".AUTOGAL_FLASHEXTS.")$/i", $ext))
	{
		header("Content-Type: application/x-shockwave-flash");
	}
	elseif (AutoGal_IsFlashVideo($imagePath))
	{
		header("Content-type: video/x-flv");
	}
	elseif (AutoGal_IsImage($imagePath))
	{
		header("Content-type: image/$ext");
	}
	else
	{
		header("Content-type: application/octet-stream");
	}
	
	header("Content-length: ".strlen($imgData));
	header("Content-Disposition: inline; filename=".$imageInfo['basename']);
	
	print $imgData; 
	
	exit;
}

function AutoGal_HtmlVar($var)
{
	$var = rawurlencode($var);
	return $var;
}

function AutoGal_GetHtmlVar($var)
{
	$var = str_replace('.', '_', str_replace(' ', '_', rawurlencode($var)));
	
	if ($_POST[$var]) return rawurldecode($_POST[$var]);
	if ($_GET[$var]) return rawurldecode($_GET[$var]);
}

// LISTS ALL FILES IN A DIRECTORY AND ITS SUBDIRECTORIES MATCHING A PATTERN.
function AutoGal_ListDirectory($dir, $pattern, $matchPattern=true, $includeXML=false, $includeLog=false, $includeDirs=false, $maxItems=0) 
{
    $file_list = '';
    $stack[] = $dir;
	$file_list = array();
	
	while ($stack)
    {
        $current_dir = array_pop($stack);
        if ($dh = opendir($current_dir))
        {
            while ($file = readdir($dh))
            {
				if ($file !== '.' AND $file !== '..')
                {
					$current_file = "{$current_dir}/{$file}";
					
					$fileMatchesPattern = false;
					if ($matchPattern)
					{
						$fileMatchesPattern = (preg_match("/$pattern/", $file) ? true : false);
					}
					else
					{
						$fileMatchesPattern = (preg_match("/$pattern/", $file) ? false : true);
					}
					
					if ($fileMatchesPattern)
					{
						if (is_file($current_file))
						{
							if (AutoGal_IsSupportedFormat($file))
							{
								$file_list[] = "{$current_dir}/{$file}";
							}
							elseif (($includeXML)&&(preg_match("/\.xml$/i", $file)))
							{
								$file_list[] = "{$current_dir}/{$file}";
							}
							elseif (($includeLog)&&(preg_match("/\.log$/i", $file)))
							{
								$file_list[] = "{$current_dir}/{$file}";
							}
						}
						else
						{
							if ($includeDirs)
							{
								$file_list[] = "{$current_dir}/{$file}";
							}
							
							$stack[] = $current_file;
						}
					}
                }
				
				if (($maxItems > 0)&&(count($file_list) >= $maxItems))
				{
					 return $file_list;
				}
            }
        }
    }

    return $file_list;
}

function AutoGal_IsIllegalName($filename)
{
    if (preg_match("/[\/\\\*\<\>\|\:\"\?]/", $filename))
    {
        return "<font face='courier new'>\\/*&lt;&gt;|:\"?</font>";
    }
	
	return "";
}

function AutoGal_RemoveIllegalFileChars($filename)
{
	$filename = str_replace("\\", "", $filename);
	$filename = str_replace('/', "", $filename);
	$filename = str_replace('*', "", $filename);
	$filename = str_replace('<', "", $filename);
	$filename = str_replace('>', "", $filename);
	$filename = str_replace('|', "", $filename);
	$filename = str_replace(':', "", $filename);
	$filename = str_replace('"', "", $filename);
	$filename = str_replace('?', "", $filename);
	return $filename;
}

function AutoGal_GetUnavailThumb($filename)
{
	$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		
	if (preg_match("/^(".AUTOGAL_EXTCLASS_IMAGE.")$/", $ext))
	{
		return AUTOGAL_UNAVAILTHUMB_IMAGE;
	}
	elseif (preg_match("/^(".AUTOGAL_EXTCLASS_MOVIE.")$/", $ext))
	{
		return AUTOGAL_UNAVAILTHUMB_MOVIE;
	}
	elseif (preg_match("/^(".AUTOGAL_EXTCLASS_ANIMATION.")$/", $ext))
	{
		return AUTOGAL_UNAVAILTHUMB_ANIMATION;
	}
	elseif (preg_match("/^(".AUTOGAL_EXTCLASS_AUDIO.")$/", $ext))
	{
		return AUTOGAL_UNAVAILTHUMB_AUDIO;
	}
	
	return 	AUTOGAL_UNAVAILTHUMB;
}

function AutoGal_DocumentRoot()
{
	$path = AutoGal_FixWinAbsPath($_SERVER['DOCUMENT_ROOT']);
	return $path;
}

function AutoGal_DefCorePath()
{
	$path = AutoGal_FixWinAbsPath(__FILE__);
	return $path;
}

function AutoGal_FixWinAbsPath($path)
{
	$path = realpath($path);
	$path = str_replace("\\", "/", $path);
	
	# Remove Drive
	$path = (substr($path, 1, 2) == ":/" ? substr($path, 2) : $path);
	
	return $path;
}

function AutoGal_GetAbsHttpPath($relPath=__FILE__, $isAbsPath=0)
{
	$absPath = AutoGal_FixWinAbsPath($relPath);
	$docRoot = AutoGal_DocumentRoot();
	
	$httpPath = preg_replace("/^".preg_quote($docRoot, '/')."/i", '', $absPath);
	
	if (!preg_match("/^\//", $httpPath)) $httpPath = "/$httpPath";
	
	return $httpPath;
}

# Ripped from e107_class
function AutoGal_E107Path($subdir='')
{
	$path = ""; $i = 0;
	
	while (!file_exists("{$path}class.php")) 
	{
		$path .= "../";
		$i++;
	}
	
	return $path.($subdir ? "$subdir/" : '');
}

function AutoGal_GetFileThumb($absPath, $ext)
{
	
	$pathInfo = pathinfo($absPath);

	if (AutoGal_IsImage($absPath))
	{
		return $pathInfo['dirname'].'/'.AUTOGAL_THUMBPREFIX.$pathInfo['basename'];
		//echo $pathInfo['dirname'].'/'.AUTOGAL_THUMBPREFIX.$pathInfo['basename'];
	}
	else if (is_dir($absPath))
	{
		return $absPath.'/'.AUTOGAL_GALLERYTHUMBFILENAME.'.'.strtolower($ext);
	}
	else
	{
		return $pathInfo['dirname'].'/'.AUTOGAL_THUMBPREFIX.$pathInfo['basename'].'.'.strtolower($ext);
	}
}

function AutoGal_LoadGlobals($loadObj=true)
{
	global $g_element;
	global $g_mediaObj;
	global $g_absPath;
	global $g_galAbsPath;
	global $g_startFile;
	global $g_showFullImage;
	global $g_isNewWindow;
	global $g_showInNewWindow;
	global $g_isAdminMode;
	global $g_sortOrder;
	global $g_startGallery;
	global $g_userAccess;
	
	include_once(AUTOGAL_USERACCESSCLASS);
	$g_userAccess = new AutoGal_CUserAccess();
	#$classes = $g_userAccess->UserClasses();

	if ($loadObj)
	{
		if (!$g_mediaObj)
		{
			$g_element = rawurldecode($_GET['show']);
			$g_element = stripslashes($g_element);
			$g_mediaObj = new AutoGal_CMediaObj($g_element);
		}
			
		if ($g_mediaObj->IsValid())
		{
			$g_absPath = $g_mediaObj->AbsPath();
			$g_galAbsPath = $g_mediaObj->GalleryAbsPath();
		}
	}
		
	$g_startFile = (preg_match("/^\d+$/", $_GET['start']) ? $_GET['start'] : 0);
	$g_startGallery = (preg_match("/^\d+$/", $_GET['startgal']) ? $_GET['startgal'] : 0);
	$g_showFullImage = (preg_match("/^\d+$/", $_GET['full']) ? rawurldecode($_GET['full']) : 0);
	$g_isNewWindow = (preg_match("/^\d+$/", $_GET['newwindow']) ? $_GET['newwindow'] : 0);
	$g_showInNewWindow = false;
	$g_isAdminMode = ($g_mediaObj->CheckUserPriv('adminmenu') ? AutoGal_IsAdminMode() : false);
	
	if (AUTOGAL_ENABLEGALDISPORD)
	{
		$g_sortOrder = $_GET['order'];
	}
	else
	{
		$g_sortOrder = AUTOGAL_DEFAULTDISPORD;
	}
}

function AutoGal_IsMainAdmin()
{
	return (ADMIN ? 1 : 0);
}

function AutoGal_IsAdminMode()
{
	$oneMonth = time() + (60 * 60 * 24 * 30); 
	
	if (isset($_GET['adminmode']))
	{
		if ($_GET['adminmode'] == 1)
		{
			setcookie('ag_adminmode', 1, $oneMonth); 
			return 1;
		}
		else
		{
			setcookie('ag_adminmode', 0, 0); 
			return 0;
		}
	}
	
	if (isset($_COOKIE['ag_adminmode']))
	{
		return $_COOKIE['ag_adminmode'];
	}
	
	return 0;
}

function AutoGal_FormatBytes($bytes)
{
	$unitNames = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	$size = round($bytes/pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $unitNames[$i];
	return $size;
}

// Ripped from php.net (e dot a dot schultz at gmail dot com)
if(!function_exists('memory_get_usage'))
{
    function memory_get_usage()
    {
        //If its Windows
        //Tested on Win XP Pro SP2. Should work on Win 2003 Server too
        //Doesn't work for 2000
        //If you need it to work for 2000 look at http://us2.php.net/manual/en/function.memory-get-usage.php#54642
        if (substr(PHP_OS, 0, 3) == 'WIN')
        {
			$output = array();
			exec( 'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output );
			return preg_replace( '/[\D]/', '', $output[5] ) * 1024;
        }
		else
        {
            //We now assume the OS is UNIX
            //Tested on Mac OS X 10.4.6 and Linux Red Hat Enterprise 4
            //This should work on most UNIX systems
            $pid = getmypid();
            exec("ps -eo%mem,rss,pid | grep $pid", $output);
            $output = explode("  ", $output[0]);
            //rss is given in 1024 byte units
            return $output[1] * 1024;
        }
    }
}

?>
