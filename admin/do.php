<?php 
include '../class.php';

if($_GET['cmd'] == 'ban')
{
	$sql -> db_Update("users", "banned='1' WHERE userid='".$_GET['id']."' AND username <> 'admin'" );
	echo "Не";
	exit();
}

if($_GET['cmd'] == 'unban')
{
	$sql -> db_Update("users", "banned='0' WHERE userid='".$_GET['id']."'");
	echo "Да";
	exit();
}

/* Editing users*/

if($_GET['cmd'] == 'edit')
{
	/* Duplicate user name check */
	$query = "SELECT COUNT(*) AS total FROM #users WHERE username='".$_GET['user_name']."' and userid != '".$_GET['id']."'";
	$sql -> db_Select_gen($query);
	$count = $sql -> db_Fetch();
	if ($count['total'] > 0)
	{
		echo "<strong>! Това име вече е регистрирано !</strong>";
		exit;
	}
	
	/* Duplicate email check */	
	//$rs_eml_duplicate = mysql_query("select count(*) as total from `users` where `user_email`='$get[user_email]' and `id` != '$get[id]'") or die(mysql_error());
	//list($eml_total) = mysql_fetch_row($rs_eml_duplicate);
	$query = "SELECT COUNT(*) AS total FROM #users WHERE user_email='".$_GET['user_email']."' AND userid != '".$_GET['id']."'";
	$sql -> db_Select_gen($query);
	$count = $sql -> db_Fetch();
	if ($count['total'] > 0)
	{
		echo "Sorry! user email already registered.";
		exit;
	}
	
	/* Now update user data*/	
	//mysql_query("update users set `user_name`='$get[user_name]', `user_email`='$get[user_email]',`user_level`='$get[user_level]'where `id`='$get[id]'") or die(mysql_error());
	$sql -> db_Update("users", "username = '".$_GET['user_name']."', first_name = '".$_GET['first_name']."', last_name = '".$_GET['last_name']."', user_email = '".$_GET['user_email']."',company = '".$_GET['company']."' , user_level = '".$_GET['user_level']."' WHERE userid = '".$_GET['id']."'");
	
	if(!empty($_GET['pass']))
	{
		$hash = PwdHash($_GET['pass']);
		//mysql_query("update users set `pwd` = '$hash' where `id`='$get[id]'") or die(mysql_error());
		$sql -> db_Update("users", " password = '".$hash."' WHERE userid = '".$_GET['id']."'");
	}

	echo "Промените извършени Успешно";
	exit();
}
?>