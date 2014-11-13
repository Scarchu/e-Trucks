<?php
require_once "../../class.php";

$mode = isset($_GET['mode']) ? $_GET['mode'] : '';

if($mode == "view")
{
	$sql -> db_Select("chatbox", "*", "ORDER BY id DESC LIMIT ".$pref['chat_lines']."", "no-where");
	while($result = $sql -> db_Fetch())
	{
		
		//$text = $emotter->filterEmotes(html_entity_decode($result['message']));
		$text = $tp -> toHTML($result['message']);
		echo "<div class='msgln'>(".date('d.m/H:i', $result['posted_on']+3600).") <b>".$result['user']."</b>: ".$text."<br></div>";
	}
}
else
{
	//$text = stripslashes(htmlspecialchars($_POST['text']));
	$text = $tp -> toText($_POST['text']);
	$time_posted = time();
	$sql->db_Insert("chatbox", "'', '".USERFN."', '$text', '$time_posted'");
}

?>