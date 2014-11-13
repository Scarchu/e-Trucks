(<?php
/*
+ ----------------------------------------------------------------------------+
|	 e107 website system
|
|	 Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|	 Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|	 Released under the terms and conditions of the
|	 GNU General Public License (http://gnu.org).
|
|	 $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.7/e107_plugins/linkwords/plugin.php $
|	 $Revision: 12178 $
|	 $Id: plugin.php 12178 2011-05-02 20:45:40Z e107steved $
|	 $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN."trucks_control/languages/".e_LANGUAGE.".php");

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "TCLANINS_0";
$eplug_version = "1.0";
$eplug_author = "Scarchu";
$eplug_url = "http://homies.scarchu.eu";
$eplug_email = "bobby@scarchu.eu";
$eplug_description = TCLANINS_1;
$eplug_compatible = "e107v7+";
$eplug_readme = "";
// leave blank if no readme file

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "trucks_control";

// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "TCLANINS_0";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin/admin.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/tr_control_32.png";
$eplug_icon_small = $eplug_folder."/images/tr_control_16.png";
$eplug_caption = TCLANINS_2;

// List of preferences -----------------------------------------------------------------------------------------------

$eplug_array_pref = "";
$eplug_prefs = "";

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
	"tc_saldo"
);

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
	"CREATE TABLE ".MPREFIX."tc_saldo(
   id int(11) UNSIGNED NOT NULL auto_increment,
   datestamp int(10) NOT NULL default 0,
   trip int(100) NOT NULL default 0,
   fuel int(100) NOT NULL default 0,
   income text,
   outcome text,
   truck int(30) NOT NULL default 0,
   saldo  int(100) NOT NULL default 0,
   PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=1;"
);


// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$ec_dir = e_PLUGIN."";
$eplug_link_url = "";


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = TCLANINS_3;


// upgrading ... //

$upgrade_add_prefs = "";

$upgrade_remove_prefs = "";

$upgrade_alter_tables = "";

$eplug_upgrade_done = "";





?>