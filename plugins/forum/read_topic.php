<?php
include_once "../../class.php";
include_once (HEADERF);
$pageTitle .= "::Форум";

if(isset($_GET['id']))
{
	$id = intval($_GET['id']);
	$qry = 'SELECT COUNT(t.id)
	AS nb1, t.title, t.parent, count(t2.id)
	AS nb2, c.name from #topics
	AS t, #topics
	AS t2, #categories
	AS c where t.id="'.$id.'" and t.id2=1 and t2.id="'.$id.'" and c.id=t.parent
	GROUP BY t.id';
	$sql -> db_Select_gen($qry);
	$dn1 = $sql -> db_Fetch();
	if($dn1['nb1']>0)
	{
		echo'
		<div class="content_forum">
			<div class="box">
				<div class="box_left">
					<a href="'.e_PLUGINS.'forum/">Форум</a> &gt; <a href="list_topics.php?parent='.$dn1["parent"].'">'.htmlentities($dn1["name"], ENT_QUOTES, "UTF-8").'</a> &gt; <a href="read_topic.php?id='.$id.'">'.htmlentities($dn1["title"], ENT_QUOTES, "UTF-8").'</a> &gt;
				</div>
				<div class="clean"></div>
			</div>
			<h1>'.$dn1["title"].'</h1>
			<a href="action.php?mode=new_reply&id='.$id.'" class="button">Отговор</a>
			';
						
			$qry = 'SELECT t.id2, t.authorid, t.message, t.timestamp, u.username
					AS author, u.avatar, u.info FROM #topics
					AS t, #users
					AS u WHERE t.id="'.$id.'" AND u.userid=t.authorid
					ORDER BY t.timestamp ASC';
			
			echo'
			<table class="messages_table">
				<tr>
					<th class="rounding_left_top">Автор</th>
					<th class="rounding_right_top">Съобщение</th>
				</tr>
				';
				$sql2 -> db_Select_gen($qry);
				while($dnn2 = $sql2 -> db_Fetch())
				{
					echo'
					<tr>
						<td class="author center" rowspan="2">
						<a href="'.e_BASE.'profile.php?uid='.$dnn2["authorid"].'">'.$dnn2["author"].'</a><br />
						';
						if($dnn2['avatar']!='')
						{
							echo '<img src="'.e_BASE.''.htmlentities($dnn2['avatar']).'" alt="аватар" style="max-width:120px;max-height:120px;" />';
						}
						else
						{
							echo  '<img src="'.e_THEME.'Images/default_avatar.png" alt="няма аватар" style="max-width:120px;max-height:120px;" />';
						}
						echo'<br />'.$dnn2["info"].'</td>';
						echo'<td class="left topic_head">';
						if(USERNM == $dnn2['author'] or USERLV == ADMIN_LEVEL)
						{
							echo'<div class="edit"><a href="action.php?mode=edit_message&id='.$id.'&id2='.$dnn2["id2"].'"><img src="'.e_THEME.'Images/edit.png" alt="Промени" /></a></div>';
						}
						echo'<div class="date">Публикувано: '.bg_date('d/M/y H:i' ,$dnn2['timestamp']).'</div>';
						echo'<div class="clean"></div>';
						echo'</tr><tr><td>';
						echo $tp -> toHTML(html_entity_decode($dnn2['message']));
						echo'</td>
					</tr>';
				}
				echo'
			</table>
			<a href="action.php?mode=new_reply&id='.$id.'" class="button">Отговор</a>
		</div>';
	}
	else
	{
		echo '<h2>This topic doesn\'t exist.</h2>';
	}
}
else
{
	echo '<h2>The ID of this topic is not defined.</h2>';
}

require_once (FOOTERF);
?>
