<?php
/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     2.xx
 * DESCRIPTION: A very simple media gallery, where galleries are based on a directory
 *              structure. For the e107 CMS (http://e107.org)
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 *              Kevin Finnin (finnin.net) - e107 search integration
 * DATE:        13/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

require_once(dirname(__FILE__)."/def_core.php");

$search_info[] = array
(
   'sfile'     => e_PLUGINS.'autogallery/search.php',
   'qtype'     => AUTOGAL_TITLE,
   'refpage'   => basename(AUTOGAL_AUTOGALLERY),
);

?>