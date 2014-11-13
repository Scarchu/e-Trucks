<?php
include_once "../../class.php";
include_once (HEADERF);
$pageTitle .= "::Форум";

if(isset($_GET['parent']))
{
	$id = intval($_GET['parent']);	
	$qry1 = 'SELECT COUNT(c.id)
			AS nb1, c.name,count(t.id)
			AS topics from #categories
			AS c left join #topics
			AS t on t.parent="'.$id.'" WHERE c.id="'.$id.'" GROUP BY c.id';
			
	$sql -> db_Select_gen($qry1);
	$dn1 = $sql -> db_Fetch();
	if($dn1['nb1']>0)
	{
		echo '<div class="content_forum">';
		echo '	<div class="box">
				<div class="box_left">
					<a href="'.e_PLUGINS.'forum/">Форум</a> > <a href="list_topics.php?parent='.$id.'">'.htmlentities($dn1["name"], ENT_QUOTES, "UTF-8").'</a>
				</div>
				<div class="clean"></div>
			</div>
			<a href="action.php?mode=new_topic&parent='.$id.'" class="button">Нова тема</a>
			';		
		$qry2 = 'SELECT t.id, t.title, t.authorid, u.username
				AS author, count(r.id)
				AS replies from #topics
				AS t left join #topics
				AS r on r.parent="'.$id.'" and r.id=t.id and r.id2!=1 left join #users
				AS u on u.userid=t.authorid
				WHERE t.parent="'.$id.'" and t.id2=1
				GROUP BY t.id
				ORDER BY t.timestamp2 DESC';
		
		$sql -> db_Select_gen($qry2);
		if(($dn2 = $sql2 -> db_Select_gen($qry2)) > 0)
		{
			echo'
			<table class="table_pm">
				<tr>
					<th class="forum_tops">Тема</th>
					<th class="forum_auth">Автор</th>
					<th class="forum_nrep">Отговори</th>
					';
					if(USERID and USERLV == ADMIN_LEVEL)
					{
						echo'<th class="forum_act">Действие</th>';
					}
				echo'</tr>';
				while ($dnn2 = $sql -> db_Fetch())
				{
					echo'
					<tr>
						<td class="forum_tops"><a href="read_topic.php?id='.$dnn2["id"].'">'.htmlentities($dnn2["title"], ENT_QUOTES, "UTF-8").'</a></td>
						<td class="center"><a href="profile.php?id='.$dnn2["authorid"].'">'.htmlentities($dnn2["author"], ENT_QUOTES, "UTF-8").'</a></td>
						<td class="center">'.$dnn2["replies"].'</td>
						';
						if($_SESSION['user_id'] == 1)
						{
							echo'<td class="center"><a href="action.php?mode=del_topic&id='.$dnn2["id"].'"><img src="'.e_THEME.'Images/delete.png" alt="Delete" /></a></td>';
						}
					echo'</tr>';
				}
			echo'</table>';
		}
		else
		{
			echo'<div class="message">Тази категория няма теми.</div>';
		}
		echo'<a href="action.php?mode=new_topic&parent='.$id.'" class="button">Нова тема</a>';
	echo'</div>';
}
else
{
	echo '<h2 class="message">Категорията не съществува!</h2><br />';
}
}
else
{
	echo '<h2>The ID of the category you want to visit is not defined.</h2>';
}
require_once(FOOTERF);
?>