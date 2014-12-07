<?php
require_once "class.php";

$ask = explode(".", e_QUERY);

if($ask[0] == "avvie")
{
	include "languages/Bulgarian/lan_avvie.php";
echo '
	<html>
	<head>
		<link href="'.e_THEME.'style.css" rel="stylesheet" type="text/css">
		<link rel="icon" type="image/png" href="'.e_THEME.'favicon.ico" />
		<meta http-equiv="Content-Type" content="text/css;charset=UTF-8">
		<title><!--TITLE--></title>
	</head>
	<body>
	<script>
		$(document).ready(function()
		{
			$(\'.file\').preimage();
		});
	</script>

	<style>
		.prev_container{
			overflow: auto;
			width: 200px;
			height: 170px;
		}

		.prev_thumb{
			margin: 5px;
			height: 150px;
		}
	</style>

	<body>
		<form name="avvie" method="post" enctype="multipart/form-data" action="handlers/avatar_upload.php?mode=select&uid='.$ask[1].'">
			<h3 align="center">'.LAN_AVVIE_2.'</h3>
			<br />
			<input class="file" id="file1" type="file" name="photoimg" multiple />
			<fieldset><div id="prev_file1"></fieldset></div><br/>
			<input type="submit" name="test" value="'.LAN_AVVIE_3.'" onclick="this.forms.avvie.submit();window.parent.location.reload();" />
		</form>
	</body>
	</html>
';
}

if($ask[0] == "chgpass")
{
	include "languages/Bulgarian/lan_changepass.php";
	if(isset($ask[2]) && $ask[2] == "chg")
	{
		$new_password = trim($_POST['password']);
		$hash = PwdHash($new_password);
		$sql -> db_Update("users", " password = '".$hash."' WHERE userid = '".$ask[1]."'");
		session_destroy();
		echo "<script type='text/javascript'>
				window.opener.location.reload();
				window.close();
			</script>";
	}
	$text ='
		<html>
			<head>
				<link href="'. e_THEME.'style.css" rel="stylesheet" type="text/css">
				<link rel="icon" type="image/png" href="'.e_THEME.'favicon.ico" />
				<meta http-equiv="Content-Type" content="text/css;charset=UTF-8">
				<title><!--TITLE--></title>
			</head>
			<body>
				<style>
					#message
					{
						background:url("'.e_THEME.'Images/black_40p.png");
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
							if (passwordVal == "") {
								$("#password").after(\'<span class="error">'.LAN_CHGPAS_2.'</span>\');
								hasError = true;
							} else if (checkVal == "") {
								$("#password-check").after(\'<span class="error">'.LAN_CHGPAS_3.'</span>\');
								hasError = true;
							} else if (passwordVal != checkVal ) {
								$("#password-check").after(\'<span class="error">'.LAN_CHGPAS_4.'</span>\');
								hasError = true;
							}
							if(hasError == true) {return false;}
						});
					});
				</script>
	';
	
	$text .='
		<div id="message">
			<form method="post" name="form1" id="form-password" action="'.e_SELF.'?'.e_QUERY.'.chg">
				<table border="0" style="margin-left:auto; margin-right:auto;">
					<tr>
						<td align="center"><input type="password" name="password" id="password" placeholder="'.LAN_CHGPAS_5.'" value="" size="20" /></td>
					</tr><tr>
						<td align="center"><input type="password" name="password-check" id="password-check" placeholder="'.LAN_CHGPAS_6.'" value="" size="20" /></td>
					</tr>
				</table>
				<br />
				<input type="hidden" name="uid" value="<?php echo $user_id ?>" />
				<input type="submit" value="'.LAN_CHGPAS_7.'" id="submit" name="submit" />
			</form>
		</div>
	';
	
	$ns -> tablerender('<h2 style="text-align:center;">'.LAN_CHGPAS_1.'</h2>', $text);
}


?>
