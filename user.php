<?php

require_once "class.php";
include "languages/Bulgarian/lan_profile.php";

echo '<script language="javascript">
        var newwindow;

        function popit(url){
            newwindow = window.open(url, \'\', "status=yes, height=350px, width=450px, resizeable=1");
        }
</script>';


$qs = explode(".", e_QUERY);
$self_page =($qs[0] == 'id' && intval($qs[1]) == USERID);

require_once(HEADERF);

$sql -> db_Select("users", "*", "userid='".$qs[1]."'");
$result = $sql->db_Fetch();
if($result["avatar"] == "")
{
	$result["avatar"] = e_THEME."Images/default_avatar.png";
}

$text ='
		<table border="1" align="center"width="90%">
			<tr>
				<td rowspan="5" width="60%">
					<table border="0" width="100%">
						<tr>
							<td colspan="2" align="center"><h2>'.$result["username"].'</h2></td>
						</tr>
						<tr>
							<td colspan="2" align="center">'.avatar($result["userid"], 240, 320).'</td>
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
if($self_page)
{
	$text .='
		<table border="0" align="center">
		<tr>
			<td><button onclick="popit(\'upopup.php?avvie.'.$qs[1].'\')">'.LAN_PRO_2.'</button></td>
			<td><button onclick="popit(\'upopup.php?chgpass.'.$qs[1].'\')">'.LAN_PRO_3.'</button></td>
		</tr>
	</table>
	';
}

$ns -> tablerender('<h1>'.LAN_PRO_5.'</h1>', $text);

require_once(FOOTERF);
?>