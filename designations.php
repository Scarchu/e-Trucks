<?php
require_once "class.php";
require_once(HEADERF);

$sql -> db_Select("users", "*", "company=".USERCO." ORDER by userid");

if(isset($_POST['useraction']))
{
	foreach($_POST['useraction'] as $key=>$value)
	{
		$sql2 -> db_Update("users", "trucks='$value' WHERE userid='".$key."'");
	}
}

$text = '
<div class="center">
<h1>НАЗНАЧЕНИЯ</h1>
<br />
<p>Чрез назначенията се настройват потребителите към кой камион да имат право на отчет.</p>
<br />
<form name = "useraction" method="post" action="'.e_SELF.'">
<table border="1" style="margin: 0 auto;">
	<tr>
		<th>Потребител</th>
		<th>Камион</th>
	</tr>
';

while($result = $sql -> db_Fetch())
{
	if($result['user_level'] != 4 and $result['user_level'] != 5 and $result['banned'] == false)
	{
		$text .= '<tr>
					<td>'.$result["first_name"].'&nbsp;'.$result["last_name"].'</td>
					<td>
						<input type="hidden" name="userid[]" value="'.$result["userid"].'" />
						<select name="useraction['.$result['userid'].']" onchange="this.form.submit()">
				';
						foreach($user_trucks_array as $key=>$value)
						{
							$sql2 -> db_Select("users", "*", "userid=".$result['userid']."");
							$result2 = $sql2 -> db_Fetch();
							$checked = $key == $result2['trucks'] ? "selected" : '';
							$text .='<option value="'.$key.'" '.$checked.' >'.$pref["trucks_plate"][$key-1].'</option>';
						}
				$text .='
						</select>
					</td>
				</tr>	
';
	}
}
$text .='</table></form></div><br />';

echo $text;





require_once(FOOTERF);
?>