<?php
include_once "../../class.php";
include_once (HEADERF);

	if(isset($_GET['id']))
	{
		$id = intval($_GET['id']);
		$qry = 'SELECT title, user1, user2 FROM #pm WHERE id="'.$id.'" AND id2="1"';
		$sql -> db_select_gen($qry);
		$dn1 = $sql -> db_Fetch();
		
		if($sql2 -> db_Count("pm", "(*)", "where id='".$id."' and id2='1'"))
		{
			//We check if the user have the right to read this discussion
			if($dn1['user1'] == USERID or $dn1['user2'] == USERID)
			{
				//The discussion will be placed in read messages
				if($dn1['user1']==$_SESSION['user_id'])
				{
					$sql -> db_Update("pm", "user1read='yes' where id='".$id."' and id2='1'");
					$user_partic = 2;
				}
				else
				{
					$sql -> db_Update("pm", "user2read='yes' where id='".$id."' and id2='1'");
					$user_partic = 1;
				}
				if(isset($_POST['message']) and $_POST['message']!='')
				{
					$message = $_POST['message'];
					//We remove slashes depending on the configuration
					if(get_magic_quotes_gpc())
					{
						$message = stripslashes($message);
					}
					//We protect the variables
					$message = mysql_real_escape_string(nl2br(htmlentities($message, ENT_QUOTES, 'UTF-8')));
					$conv_count = $sql2 -> db_Count('pm', '(*)', 'where id="'.$id.'"') + 1;
					
					//We send the message and we change the status of the discussion to unread for the recipient
					if($sql2 -> db_Insert('pm', '"'.$id.'", "'.$conv_count.'", "", "'.$_SESSION['user_id'].'", "", "'.$message.'", "'.time().'", "", ""') and $sql2 -> db_Update('pm', 'user'.$user_partic.'read="no" where id="'.$id.'" and id2="1"'))
					{
						echo '
						<div class="message">Съобщението е изпратено успешно.<br />
							<a href="read_pm.php?id='.$id.'">Върни се към дискусията</a></div>
						';
					}
					else
					{
						echo '
						<div class="message">Имаше грешка при изпращане на съобщението.<br />
							<a href="read_pm.php?id='.$id.'">Върни се към дискусията</a></div>
						';
					}
				}
				else
				{
					//We display the messages
					?>
					<link href="theme/pmstyle.css" rel="stylesheet" type="text/css">
					<div class="content_pm">
						<h1 class="center"><?php echo $dn1['title']; ?></h1>
						<table class="messages_table">
							<tr>
								<th class="rounding_left_top">Потребител</th>
								<th class="rounding_right_top">Съобщение</th>
							</tr>
							<?php
							$qry = 'select pm.timestamp, pm.message, users.userid
									as userid, users.username, users.avatar
									from #pm as pm, #users as users
									where pm.id="'.$id.'"
									and users.userid=pm.user1
									order by pm.id2';
							$sql2 -> db_Select_gen($qry);
							while($dn2 = $sql2 -> db_Fetch())
							{
								echo '
								<tr>
									<td class="author center">
									<a href="'.e_BASE.'profile.php?uid='.$dn2["userid"].'">'.$dn2["username"].'</a><br />';
									if($dn2['avatar']!='')
									{
										echo '<img src="'.e_BASE.''.htmlentities($dn2['avatar']).'" alt="аватар" style="max-width:120px;max-height:120px;" />';
									}
									else
									{
										echo  '<img src="'.e_THEME.'Images/default_avatar.png" alt="няма аватар" style="max-width:120px;max-height:120px;" />';
									}
									?><br /><br />
									</td>
									<td class="left"><div class="date">Изпратено: <?php echo bg_date('d/M/y H:i' ,$dn2['timestamp']); ?></div>
									<?php
									
										echo $tp -> toHTML(html_entity_decode(($dn2['message'])));
										//echo $text;
									
									?></td>
								</tr>
								<?php
							}
							//We display the reply form
							?>
					</table><br />
					<h2>Отговори</h2>
					<div class="center">
						<form action="read_pm.php?id=<?php echo $id; ?>" method="post">
							<label for="message" class="center">Съобщение</label><br />
							<textarea cols="40" rows="5" name="message" id="message"></textarea><br />
							<input type="submit" value="Изпрати" />
						</form>
					</div>
				</div>
					<?php
				}
			}
			else
			{
				echo '<div class="message">You dont have the rights to access this page.</div>';
			}
		}
		else
		{
			echo '<div class="message">This discussion does not exists.</div>';
		}
	}
	else
	{
		echo '<div class="message">The discussion ID is not defined.</div>';
	}

include(FOOTERF);
?>