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
					$qry = 'select pm.timestamp, pm.message, users.userid
						as userid, users.username, users.avatar
						from #pm as pm, #users as users
						where pm.id="'.$id.'"
						and users.userid=pm.user1
						order by pm.id2';
					$sql2 -> db_Select_gen($qry);
					//We display the messages
					$head = '<h1 class="center">'.$dn1["title"].'</h1>';
					$text ='
					<link href="theme/pmstyle.css" rel="stylesheet" type="text/css">
					<div class="content_pm">
						<table class="messages_table">
							<tr>
								<th class="rounding_left_top">Потребител</th>
								<th class="rounding_right_top">Съобщение</th>
							</tr>';

							while($dn2 = $sql2 -> db_Fetch())
							{
								$text .= '
								<tr>
									<td class="author center">
									<a href="'.e_BASE.'user.php?id.'.$dn2["userid"].'">'.$dn2["username"].'</a><br />
									'.avatar($dn2["userid"]).'
									<br /><br />
									</td>
									<td class="left"><div class="date">Изпратено: '.bg_date('d/M/y H:i' ,$dn2['timestamp']).'</div>
									'.$tp -> toHTML(html_entity_decode(($dn2['message']))).'									
									</td>
								</tr>';
							}
							//reply form
							$text .='
					</table><br />
					<h2 class="center">Отговори</h2>
					<div class="center">
						<form action="read_pm.php?id='.$id.'" method="post">
							<label for="message" class="center">Съобщение</label><br />
							<textarea cols="40" rows="5" name="message" id="message"></textarea><br />
							<input type="submit" value="Изпрати" />
						</form>
					</div>
				</div>';
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

$ns -> tablerender($head, $text);	
require_once(FOOTERF);
?>