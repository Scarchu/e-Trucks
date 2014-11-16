<?php

class userprofile
{
	var $user;
	var $company;
	
	function user_parse()
	{
		global $sql;
		$sql -> db_Select("users", "*", "userid='".$this->user."'");
		$result = $sql->db_Fetch();
		if($result["avatar"] == "")
		{
			$result["avatar"] = e_THEME."Images/default_avatar.png";
		}
		echo'
		<h1>'.LAN_PRO_5.'</h1>
		<br />
		<table border="1" align="center"width="90%">
			<tr>
				<td rowspan="5" width="60%">
					<table border="0" width="100%">
						<tr>
							<td colspan="2" align="center"><h2>'.$result["username"].'</h2></td>
						</tr>
						<tr>
							<td colspan="2" align="center"><img src="'.$result["avatar"].'"></td>
						</tr>
					</table>
				</td>
				<th width="14%">'.LAN_PRO_6.'</th>
				<td class="right">'.$result["first_name"].'</td>
			</tr>
			<tr>
				<th>'.LAN_PRO_7.'</th>
				<td class="right">'.$result["last_name"].'</td>
			</tr>
			<tr>
				<th>'.LAN_PRO_8.'</th>
				<td class="right">'.$result["user_email"].'</td>
			</tr>
			<tr>
				<th>'.LAN_PRO_9.'</th>
				<td class="right">'.$result["info"].'</td>
			</tr>
			<tr>
				<th>'.LAN_PRO_10.'</th>
				<td class="right">'.$result["user_level"].'</td>
			</tr>
		</table>
		';				
	}
	
/*	function truck_to_driver()
	{
		global $sql;
		$sql -> db_Select("users", "*", "company=".$this->company."");
		while($result = $sql -> db_Fetch())
		{
			echo $result["userid"].'<br />';
		}
	}*/
}
?>