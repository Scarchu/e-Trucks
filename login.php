<?php
require_once 'class.php';
include_once (HEADERF);

$err = array();

foreach($_GET as $key => $value)
{
	$get[$key] = filter($value); //get variables are filtered.
}

if (isset($_POST['doLogin']) =='Login')
{
	foreach($_POST as $key => $value)
	{
		$data[$key] = filter($value); // post variables are filtered
	}
	$user_email = $data['usr_email'];
	$pass = $data['pwd'];
	if (strpos($user_email,'@') === false)
	{
		$user_cond = "username='".$user_email."'";
	}
	else
	{
		$user_cond = "user_email='".$user_email."'";
	}
	$num = $sql -> db_Count("users", "(*)", "WHERE ".$user_cond."");
	// Match row found with more than 1 results  - the user is authenticated. 
	if ( $num > 0 )
	{
		$sql -> db_Select("users", "*", "".$user_cond."");
		while ($result = $sql -> db_Fetch())
		{
			$id = $result['userid'];
			$pwd = $result['password'];
			$full_name = $result['username'];
			$approved = $result['approved'];
			$user_level = $result['user_level'];
			$banned = $result['banned'];
			$avatar = $result['avatar'];
		}
		if($banned)
		{
			$err[] = "Потребителят е изключен!";
		}
		 //check against salt
		if ($pwd === PwdHash($pass,substr($pwd,0,9)))
		{ 
			if(empty($err))
			{
				// this sets session and logs user in  
				//if (session_status() == PHP_SESSION_NONE) { session_start(); }
				if(session_id() == '') { session_start();  };
				session_regenerate_id (true); //prevent against session fixation attacks.
				// this sets variables in the session 
				$_SESSION['user_id']= $id;  
				$_SESSION['user_name'] = $full_name;
				$_SESSION['user_level'] = $user_level;
				$_SESSION['avatar'] = $avatar;
				$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
				//update the timestamp and key for cookie
				$stamp = time();
				$ckey = GenKey();
				$ip = $sc_trucks -> getip();
				$sql -> db_Update("users", "ctime='$stamp', ckey='$ckey', user_ip='$ip' WHERE userid='$id'");
				//set a cookie 
				if(isset($_POST['remember']))
				{
					setcookie("userid", $_SESSION['user_id'], time()+60*60*24*COOKIE_TIME_OUT, "/");
					setcookie("userkey", sha1($ckey), time()+60*60*24*COOKIE_TIME_OUT, "/");
					setcookie("username",$_SESSION['user_name'], time()+60*60*24*COOKIE_TIME_OUT, "/");
					setcookie("userlevel", $_SESSION['user_level'], time()+60*60*24*COOKIE_TIME_OUT, "/");
				}
				if ($pref['maintenance'] == 1 and $_SESSION['user_level'] <= 4)
				{
					echo $modal_var;
					exit;
				}
				if ($_SESSION['user_level'] > 0)
				header("Location:" .e_BASE."index.php");
				else
				header("Location:" .e_PLUGINS."forum/index.php");
			}
		}
		else
		{
			$err[] = "Невалидна парола!";
		}
	}
	else
	{
		$err[] = "Грешка! Несъществуващ потребител!";
	}		
}

//******************** ERROR MESSAGES***************************************//
//				  This code is to show error messages 						//
//**************************************************************************//
if(!empty($err))
{
	echo "<div class=\"msg\">";
	foreach ($err as $e)
	{
		error_display("Внимание", $e);
	}
	echo "</div>";	
}
	  /******************************* END ********************************/	
		  
echo'
<br />
<div align="center" class="login">
	<form action="'.e_SELF.'" method="post" name="logForm">
		<table width="50%"  align="center" border="0" cellpadding="2" cellspacing="0">
			<tr class="text">
				<td width="25%" align="right"><b>Потребител:</b></td>
				<td height="25" align="center"><input name="usr_email" type="text" class="text" size="25" autofocus></td>
			</tr>
			<tr class="text">
				<td align="right"><b>Парола:</b></td>
				<td height="25" align="center"><input name="pwd" type="password" class="text" size="25"></td>
			</tr>
			<tr>
				<td colspan="2"><p align="center">Запомни ме за 30 дни <input name="remember" type="checkbox" value="true" /></p></td>
			</tr>
			<tr align="center">
				<td height="25" colspan="2">
				<input name="doLogin" type="submit" class="text" value="Вход"></td>
			</tr>
		</table>
	</form>
</div>
<br />
';
include_once(FOOTERF);
?>