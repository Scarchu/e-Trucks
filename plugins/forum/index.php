<?php
include_once "../../class.php";
include_once (HEADERF);
$pageTitle .= "::Форум";

echo'
<div class="content_forum">
	<div class="box">
		<div class="box_left">
			<a href="'.e_PLUGINS.'forum/">Форум</a>
		</div>
		<div class="clean"></div>
	</div>
';
//$admin = "admin";
if(USERLV == ADMIN_LEVEL)
{
	echo'<a href="action.php?mode=new_cat" class="button">Нова Категория</a>';
}
echo'
<table class="table_forum">
	<tr>
    	<th class="forum_cat">Категория</th>
    	<th class="forum_ntop">Теми</th>
    	<th class="forum_nrep">Отговори</th>
';
if(USERLV == ADMIN_LEVEL)
{
    	echo'<th class="forum_act">Действия</th>';
}
	echo'</tr>';
	
$qry = 'SELECT c.id, c.name, c.description, c.position,
		(SELECT COUNT(t.id) from #topics as t where t.parent=c.id and t.id2=1)
		AS topics,
		(SELECT COUNT(t2.id) from #topics as t2 where t2.parent=c.id and t2.id2!=1)
		AS replies from #categories
		AS c
		GROUP BY c.id
		ORDER BY c.position ASC';
$sql -> db_Select_gen($qry);
$nb_cats = $sql2 -> db_Count("categories", "(id)");
while($dnn1 = $sql -> db_Fetch())
{
?>
	<tr>
    	<td class="forum_cat"><a href="list_topics.php?parent=<?php echo $dnn1['id']; ?>" class="title"><?php echo htmlentities($dnn1['name'], ENT_QUOTES, 'UTF-8'); ?></a>
        <div class="description"><?php echo $dnn1['description']; ?></div></td>
    	<td class="center"><?php echo $dnn1['topics']; ?></td>
    	<td class="center"><?php echo $dnn1['replies']; ?></td>
<?php

if($_SESSION['user_level'] == 5)
{
	echo'<td><a href="action.php?mode=del_cat&id='.$dnn1["id"].'"><img src="'.e_THEME.'Images/delete.png" alt="Delete" /></a>';
	if($dnn1['position']>1)
	{
		echo'<a href="action.php?mode=move_cat&action=up&id='.$dnn1["id"].'"><img src="'.e_THEME.'Images/up.png" alt="Move Up" /></a>';
	}
	if($dnn1['position']<$nb_cats)
	{
		echo'<a href="action.php?mode=move_cat&action=down&id='.$dnn1["id"].'"><img src="'.e_THEME.'Images/down.png" alt="Move Down" /></a>';
	}
	echo'<a href="action.php?mode=edit_cat&id='.$dnn1["id"].'"><img src="'.e_THEME.'Images/edit.png" alt="Edit" /></a></td>';

}
echo '
    </tr>
';
}
echo '
</table>
';
if($_SESSION['user_level'] == 5)
{
	echo'<a href="action.php?mode=new_cat" class="button">Нова Категория</a>';
}
echo '
</div>
';
require_once (FOOTERF);
?>