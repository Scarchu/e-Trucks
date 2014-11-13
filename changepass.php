<?php
require_once "class.php";
//require_once "handlers/java_includes.php";
include "languages/Bulgarian/lan_changepass.php";
$pageTitle .= "::".LAN_CHGPAS_1;
?>
<html>
<head>
<link href="<?php echo e_THEME; ?>style.css" rel="stylesheet" type="text/css">
<link rel="icon" type="image/png" href="<?php echo e_THEME; ?>favicon.ico" />
<meta http-equiv="Content-Type" content="text/css;charset=UTF-8">
<title><!--TITLE--></title>
</head>
<body>
<style>
#message
{
	background:url('<?php echo e_THEME; ?>Images/black_40p.png');
	width: 40%;
	position: relative;
	-moz-border-radius:20px;
	-webkit-border-radius:20px;
	border-radius:20px;
	margin:auto;
	padding:20;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #CC2900;
	text-align: center;
	font-weight: bold;
}

.error
{
	font-size:12px;
	color: #B2F0FF;
	font-weight:bold;
}
</style>
<script>
jQuery(function(){
        $("#submit").click(function(){
        $(".error").hide();
        var hasError = false;
        var passwordVal = $("#password").val();
        var checkVal = $("#password-check").val();
        if (passwordVal == '') {
            $("#password").after('<span class="error"><?php echo LAN_CHGPAS_2; ?></span>');
            hasError = true;
        } else if (checkVal == '') {
            $("#password-check").after('<span class="error"><?php echo LAN_CHGPAS_3; ?></span>');
            hasError = true;
        } else if (passwordVal != checkVal ) {
            $("#password-check").after('<span class="error"><?php echo LAN_CHGPAS_4; ?></span>');
            hasError = true;
        }
        if(hasError == true) {return false;}
    });
});
</script>

<?php

if(isset($_POST['submit']))
{
	$uid = $_POST['uid'];
	$new_password = trim($_POST['password']);
	$hash = PwdHash($new_password);
	$sql -> db_Update("users", " password = '".$hash."' WHERE userid = '".$uid."'");
	session_destroy();
	echo "<script type='text/javascript'>
				window.opener.location.reload();
				window.close();
			</script>";
}

if(isset($_GET['mode']) && $_GET['mode'] == "chgpwd")
{
	$user_id = $_GET['uid'];

?>
<h2 style="text-align:center;"><?php echo LAN_CHGPAS_1; ?></h2>
<div id="message">
<form method="post" name="form1" id="form-password" action="<?php echo e_SELF; ?>">
	<table border="0" style="margin-left:auto; margin-right:auto;">
		<tr>
			<td align="center"><input type="password" name="password" id="password" placeholder="<?php echo LAN_CHGPAS_5; ?>" value="" size="20" /></td>
		</tr><tr>
			<td align="center"><input type="password" name="password-check" id="password-check" placeholder="<?php echo LAN_CHGPAS_6; ?>" value="" size="20" /></td>
		</tr>
	<table>
	<br />
	<input type="hidden" name="uid" value="<?php echo $user_id ?>" />
    <input type="submit" value="<?php echo LAN_CHGPAS_7; ?>" id="submit" name="submit" />
</form>
</div>
<?php 
}

$pageContents = ob_get_contents ();
ob_end_clean (); // Wipe the buffer
echo str_replace ('<!--TITLE-->', $pageTitle, $pageContents); //replace the TITLE
?>