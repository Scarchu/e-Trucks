<?php 
include_once "class.php";
require (HEADERF);

include "languages/Bulgarian/lan_index.php";

$pageTitle .= LAN_INDEX_1;

if(USERLV == 0) {header("Location: ".e_PLUGINS."forum/");}		//if the user is guest -> goto forum

$truck = '';

if(e_QUERY)
{
	$truck = explode("=", e_QUERY);
	$truck = $truck[1];
}
else
{
	$query = "SELECT * FROM #users ORDER BY last_loggedin DESC LIMIT ".$pref['latest_usersc']."";
	$sql -> db_Select_gen($query);
		
	echo "
		<table border='0' width='100%'>
		<tr>
			<td width='30%' align='center'>";
				include(e_PLUGINS."chat/index.php"); 
			echo "</td>
			<td align='center' valign='top'><div id='mainp-wrapper'>
			<div id='up_fill'><p>".LAN_INDEX_2."</p></div>
			<div id='ins_box'>";
			
			while ($row = $sql -> db_Fetch())
			{
				$last_logged_date = bg_date("d.M.y G:i", $row['last_loggedin']+10800);
				$last_logged_name = "<b>{$row['first_name']} {$row['last_name']}</b>";
				echo "<img src='".e_THEME."Images/bullet3.png' />&nbsp;".$last_logged_name."&nbsp;| ".$last_logged_date."<br />";
			}
			/***************************/
			/* posledni temi ot foruma */
			/***************************/
			echo " </div></div></td>
			<td align='center' valign='top'><div id='mainp-wrapper'>
			<div id='up_fill'><p>".LAN_INDEX_3."</p></div>
			<div id='ins_box'>";
			
			$sql -> db_Select("categories", "*", "ORDER BY last_timestamp DESC", false);
			while($row = $sql -> db_Fetch())
			{
				$query_lff = 'SELECT t.id, t.title, t.authorid, u.username
					AS author, count(r.id)
					AS replies from #topics
					AS t left join #topics
					AS r on r.parent="'.$row["id"].'" and r.id=t.id and r.id2!=1 left join #users
					AS u on u.userid=t.authorid
					WHERE t.parent="'.$row["id"].'" and t.id2=1
					GROUP BY t.id
					ORDER BY t.timestamp2 DESC
					LIMIT '.$pref['forum_cnew'].'';
				$sql2 -> db_Select_gen($query_lff);
			
				while ($row2 = $sql2 -> db_Fetch())
				{
					echo "<img src='".e_THEME."Images/bullet3.png' />&nbsp;<a href='".e_PLUGINS."forum/read_topic.php?id={$row2['id']}'>{$row2['title']}</a><i> ".LAN_INDEX_4." {$row2['replies']}".LAN_INDEX_5."</i><br />";
				}
			}
			echo " </div></div></td>
		</tr>
		</table>
	";
}

if($truck > $pref['broi_trucks'])
{
	error_display(LAN_INDEX_6, LAN_INDEX_7);
	include(FOOTERF);
	die;
}

truck_select($truck); //Loads trucks selection

include(FOOTERF);
?>