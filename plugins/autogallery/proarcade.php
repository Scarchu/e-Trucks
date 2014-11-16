<?php

/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery - Arcade Integration
 * VERSION:     2.xx
 * DESCRIPTION: Integration with arcade high scores. Special thanks to SpooK (Rob Fay) for 
 *              his code and assistance. 
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 *              SpooK (http://rob3rt.net)
 * DATE:        02/11/2006
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/
 
require_once(dirname(__FILE__)."/def.php");

if (!isset($_POST['score'])) exit;
$points = $_POST['score'];

AutoGal_AddScoreGetUser($points);

?>