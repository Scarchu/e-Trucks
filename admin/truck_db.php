<?php
include_once "../class.php";

if (!defined('eTR_INIT')) { exit; }

$mode = $_GET['mode'];
$pr = $_GET['pr'];
if($pr > $pref['broi_trucks'])
{
	include_once (HEADERF);
	echo "Опит за хакване или генерална грешка!";
	include_once (FOOTERF);
	exit;
}
$k = ($pr - 1);

if($mode == "create") //Syzdavane na tablici za nov kamion
{
	if($pr != '')
	{
		$query1 = "
			CREATE TABLE IF NOT EXISTS ".MPREFIX."truck".$pr."_cg (
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`date` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
			`road` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			`trip` int(30) NOT NULL,
			`empty` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
			`userid` int(10) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";

		$query2 = "
			CREATE TABLE IF NOT EXISTS ".MPREFIX."truck".$pr."_lt (
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`date` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
			`liters` int(30) NOT NULL,
			`literst` int(10) NOT NULL,
			`adblue` int(10) NOT NULL,
			`trip` int(30) NOT NULL,
			`cash` text COLLATE utf8_unicode_ci NOT NULL,
			`full` text COLLATE utf8_unicode_ci NOT NULL,
			`userid` int(10) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		$sql -> db_Select_gen($query1);
		$sql -> db_Select_gen($query2);
		$pref['trucks_plate'][$k] = '';
		$pref['modules']['frigo'][$pr]	= 0;
		$pref['modules']['chmr'][$pr] 	= 0;
		$pref['modules']['taho'][$pr] 	= 0;
		$pref['modules']['gorivo'][$pr] = 1;
		$pref['modules']['tovar'][$pr] 	= 1;
		$pref['modules']['AdBlue'][$pr] = 0;
		save_prefs();
		header( "refresh:0;url=".e_ADMIN."index.php?mode=truck" );
	}
	else
	{
		include_once (HEADERF);
		echo "<strong>Невалиден служебен номер на камион!</strong>";
		include_once (FOOTERF);
		exit;
	}
}

elseif($mode == "delete") //Iztrivane na tablici na kamion
{
	$query1 = "DROP TABLE IF EXISTS ".MPREFIX ."truck".$pr."_cg";
	$query2 = "DROP TABLE IF EXISTS ".MPREFIX ."truck".$pr."_lt";
	$sql -> db_Select_gen($query1);
	$sql -> db_Select_gen($query2);
	header( "refresh:0;url=".e_ADMIN."index.php?mode=truck" );
}
else
{
	include_once (HEADERF);
	echo "<strong>Невалидно обръщение!</strong>";
	include_once (FOOTERF);
	exit;
}
?>