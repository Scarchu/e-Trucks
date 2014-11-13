<?php
include_once "../../class.php";
include_once (HEADERF);

$qry1 = 'SELECT m1.id, m1.title, m1.timestamp, count(m1.id)
		as reps, users.userid
		as userid, users.username
		from #pm as m1, #users
		as users, #pm as m2
		WHERE ((m1.user1="'.USERID.'"
		AND m1.user1read="no"
		AND users.userid=m1.user2)
		OR (m1.user2="'.USERID.'"
		AND m1.user2read="no"
		AND users.userid=m1.user2))
		AND m1.id2="1"
		AND m2.id=m1.id
		GROUP BY m1.id
		ORDER BY m1.id desc';
		
$qry2 = 'SELECT m1.id, m1.title, m1.timestamp, count(m1.id)
		as reps, users.userid
		as userid, users.username
		from #pm as m1, #users
		as users, #pm as m2
		WHERE ((m1.user1="'.USERID.'"
		AND m1.user1read="yes"
		AND users.userid=m1.user2)
		OR (m1.user2="'.USERID.'"
		AND m1.user2read="yes"
		AND users.userid=m1.user2))
		AND m1.id2="1"
		AND m2.id=m1.id
		GROUP BY m1.id
		ORDER BY m1.id desc';

$not_read = $sql2 -> db_Count("pm", "(user1read)", "WHERE (user1='".USERID."' OR user2='".USERID."') AND user1read='yes' AND user2read='no'");
$sql3 = new db;
$read = $sql3 -> db_Count("pm", "(user1read)", "WHERE (user1='".USERID."' OR user2='".USERID."') AND user1read='yes' AND user2read='yes'");

echo '
<div class="content_pm">
<br />
<a href="new_pm.php" class="link_new_pm">Ново Съобщение</a><br />
<h3 class="center">Непрочетени Съобщения ('.$not_read.'):</h3>
<table class="table_pm">
	<tr>
    	<th class="rounding_left_top">Заглавие</th>
        <th>Брой отговори</th>
        <th>От</th>
        <th class="rounding_right_top">Дата</th>
    </tr>
';
//We display the list of unread messages
$sql -> db_Select_gen($qry1);
while($dn1 = $sql -> db_Fetch())
{
?>
	<tr>
    	<td class="center"><a href="read_pm.php?id=<?php echo $dn1['id']; ?>"><?php echo htmlentities($dn1['title'], ENT_QUOTES, 'UTF-8'); ?></a></td>
    	<td class="center"><?php echo $dn1['reps']-1; ?></td>
    	<td class="center"><!--<a href="profile.php?id=<?php echo $dn1['userid']; ?>">--><?php echo htmlentities($dn1['username'], ENT_QUOTES, 'UTF-8'); ?></a></td>
    	<td class="center"><?php echo bg_date('d/M/y H:i' ,$dn1['timestamp']); ?></td>
    </tr>
<?php
}
//If there is no unread message we notice it
//if(intval(mysql_num_rows($req1)) == 0)
//echo $sql2 -> db_Count("pm", "(*)", "WHERE user1 = '".$_SESSION['user_id']."' AND user1read = 'yes'");
if($not_read == 0)
{
?>
	<tr>
    	<td colspan="4" class="center">Нямате непрочетени съобщения.</td>
    </tr>
<?php
}
?>
</table>
<br />
<h3 class="center">Прочетени Съобщения (<?php echo $read; ?>):</h3>
<table class="table_pm">
	<tr>
    	<th class="rounding_left_top">Заглавие</th>
        <th>Брой отговори</th>
        <th>От</th>
        <th class="rounding_right_top">Дата</th>
    </tr>
<?php
//We display the list of read messages
//while($dn2 = mysql_fetch_array($req2))
$sql2 -> db_Select_gen($qry2);
while($dn2 = $sql2 -> db_Fetch())
{
?>
	<tr>
    	<td class="center"><a href="read_pm.php?id=<?php echo $dn2['id']; ?>"><?php echo htmlentities($dn2['title'], ENT_QUOTES, 'UTF-8'); ?></a></td>
    	<td class="center"><?php echo $dn2['reps']-1; ?></td>
    	<td class="center"><!--<a href="profile.php?id=<?php echo $dn2['userid']; ?>">--><?php echo htmlentities($dn2['username'], ENT_QUOTES, 'UTF-8'); ?></a></td>
    	<td class="center"><?php echo bg_date('d/M/y H:i' ,$dn2['timestamp']); ?></td>
    </tr>
<?php
}
//If there is no read message we notice it
if($read == 0)
{
echo '
	<tr>
    	<td colspan="4" class="center">Нямате прочетени съобщения.</td>
    </tr>
';
}
echo '
</table>
</div>
';
include(FOOTERF);
?>