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

/*
One of the receiving files for the AutoArcade plugin.
Location:  e107_plugins/autogallery/arcade.php

Parts of this code have been lifted from sky-base.ch's officially unreleased "Flash Games Plugin for pnflashgames and other".
http://www.sky-base.ch - http://e107coders.org/e107_plugins/forum/forum_viewtopic.php?17276.0

*/

// Do different things when the flash game gives different requests
$sessdo = $_POST['sessdo'];

if ($sessdo != '') 
{ 
	// Session start to get the game name - $title
	$microone = $_POST['microone'];
	$score = $_POST['score'];
	$gametime = $_POST['gametime'];
	
	// Keep feeding that flash!
	switch($sessdo) 
	{ 
		case 'sessionstart': 
			// Give it some random crap it doesn't really need (the initbar figure and lastid)
			echo "&connStatus=1&gametime=$gametime&initbar=6Z4&lastid=6&val=x"; 
			exit; 
			break; 
	
		// Give it permission for... no apparent reason
		case 'permrequest': 
			// Notice $microone = $score;  -__VERY IMPORTANT__
			$microone = $score; 
			echo "&validate=1&microone=$microone"; 
			exit; 
			break; 
	
		//  Finally, here's what we do when the flash file sends its final vars to "burn"...
	
		case 'burn': 
			//Do something with the final vars (score = $microone)
			$score = $microone;
			AutoGal_AddScoreGetUser($score);
			exit; 
			break; 
	} 
} 

?>