<?php
include_once "../../class.php";
include_once (HEADERF);

$form = true;
$otitle = '';
$orecip = '';
$omessage = '';
//We check if the form has been sent
if(isset($_POST['title'], $_POST['recip'], $_POST['message']))
{
	$otitle = $_POST['title'];
	$orecip = $_POST['recip'];
	$omessage = $_POST['message'];
	//We remove slashes depending on the configuration
	if(get_magic_quotes_gpc())
	{
		$otitle = stripslashes($otitle);
		$orecip = stripslashes($orecip);
		$omessage = stripslashes($omessage);
	}
	//We check if all the fields are filled
	if($_POST['title']!='' and $_POST['recip']!='' and $_POST['message']!='')
	{
		//We protect the variables
		$title = mysql_real_escape_string($otitle);
		$recip = mysql_real_escape_string($orecip);
		$message = mysql_real_escape_string(nl2br(htmlentities($omessage, ENT_QUOTES, 'UTF-8')));
		//We check if the recipient exists
		$qry = 'select count(userid)
				as recip, userid
				as recipid,
				(select count(*) from #pm)
				as npm
				from #users
				where username="'.$recip.'"';
		$sql -> db_Select_gen($qry);
		$dn1 = $sql -> db_Fetch();
		if($dn1['recip']==1)
		{
			$id = $dn1['npm']+1;
			//We send the message
			if($sql -> db_Insert('pm', '"'.$id.'", "1", "'.$title.'", "'.USERID.'", "'.$dn1['recipid'].'", "'.$message.'", "'.(time()+3600).'", "yes", "no"'))
			{
				echo'
				<div class="message">Съобщението бе пратено успешно.<br />
				<a href="list_pm.php">Върни се към листинга</a></div>
				';
				$form = false;
			}
			else
			{
				//Otherwise, we say that an error occured
				$error = 'Появи се грешка при изпращането';
			}
		}
		else
		{
			//Otherwise, we say the recipient does not exists
			$error = 'Получателя не съществува.';
		}
	}
	else
	{
		//Otherwise, we say a field is empty
		$error = 'Има празно поле. Моля, попълни всички полета.';
	}
}
elseif(isset($_GET['recip']))
{
	//We get the username for the recipient if available
	$orecip = $_GET['recip'];
}
if($form)
{
//We display a message if necessary
if(isset($error))
{
	echo '<div class="message">'.$error.'</div>';
}
/*-------------------------------------------------------------------*/

$sql -> db_Select("users", "*");
$options="";
while ($row = $sql -> db_Fetch())
{
    $username=$row["username"];
	if ($row["banned"] !== "1")
	{
		if($username !== USERNM)
		{
			$options.="<option VALUE=\"$username\">".$row['first_name']." ".$row['last_name']."</option>";
		}
	}
}	

/*-------------------------------------------------------------------*/
?>

<div class="content_pm">
	<h1 class="center">Ново лично съобщение</h1>
    <form action="new_pm.php" method="post">
		<h3>Моля, попълнете полетата, задължителни са!</h3>
        <label for="title" >Заглавие </label><input type="text" value="<?php echo htmlentities($otitle, ENT_QUOTES, 'UTF-8'); ?>" id="title" name="title" /><br /><br />
        <label for="recip">Получател</label><select name=recip><option value=0>Избери<?php echo $options; ?></select><br /><br />
        <label for="message">Съобщение</label><textarea cols="40" rows="5" id="message" name="message"><?php echo htmlentities($omessage, ENT_QUOTES, 'UTF-8'); ?></textarea><br />
        <input type="submit" value="Изпрати" />
    </form>
</div>
<?php
}

include(FOOTERF);
?>