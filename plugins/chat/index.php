<?php
if (!defined('eTR_INIT')) { exit; }

include "languages/Bulgarian/lan_chat.php";
?>

<link type="text/css" rel="stylesheet" href="<?php echo e_PLUGINS_ABS; ?>chat/style.css" />
<!-- <script src="e107.js"></script> -->

<div id="wrapper">
	<div id="menu">
		<p class="welcome"><?php echo LAN_CHAT_1; ?></p>
		<div style="clear:both"></div>
	</div>	
	<div id="chatbox"><img style="margin:0 auto; text-align:center;" src="<?php echo e_THEME; ?>Images/ajax-loader.gif" /></div>
	<form name="message" action="">
		<textarea id='usermsg' name='usermsg' wrap='hard' class='tbox chatbox' cols='15' rows='5' style='overflow: auto' onselect='storeCaret(this);' onclick='storeCaret(this);' onfocus='storeCaret(this);' onkeyup='storeCaret(this);'></textarea>
		<input name="submitmsg" type="submit"  id="submitmsg" value="<?php echo LAN_CHAT_2; ?>" />
		<input class='' type ='button' style='cursor:pointer' size='30' value='<?php echo LAN_CHAT_3; ?>' onclick="expandit('cb2_emote');this.form.usermsg.focus();" <?php if(!$pref["smiley_activate"]) echo "disabled"?> />
		<div style='display:none' id='cb2_emote'><?php echo r_emote(); ?></div>
	</form>
</div>

<script type="text/javascript">
$(document).ready(function(){
	//If user submits the form
	$("#submitmsg").click(function(){	
		var clientmsg = $("#usermsg").val();
		$.post("<?php echo e_PLUGINS; ?>chat/post.php", {text: clientmsg});				
		$("#usermsg").attr("value", "");
		return false;
	});
	
	//Load the file containing the chat log
	function loadLog(){		
		var oldscrollHeight = $("#chatbox").attr("scrollHeight") - 20;
		$.ajax({
			type: "GET",
			url: "<?php echo e_PLUGINS; ?>chat/post.php?mode=view",
			cache: false,
			success: function(html){		
				$("#chatbox").html(html); //Insert chat log into the #chatbox div				
				var newscrollHeight = $("#chatbox").attr("scrollHeight") - 20;
				if(newscrollHeight > oldscrollHeight){
					$("#chatbox").animate({ scrollTop: newscrollHeight }, 'normal'); //Autoscroll to bottom of div
				}				
		  	},
		});
	}
	setInterval (loadLog, 4000);	//Reload file every 10 seconds
});
</script>