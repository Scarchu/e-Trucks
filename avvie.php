<?php
require_once "class.php";
include "languages/Bulgarian/lan_avvie.php";
$pageTitle .= "::".LAN_AVVIE_1;
$uid = $_GET['uid'];
?>
<html>
<head>
<link href="<?php echo e_THEME; ?>style.css" rel="stylesheet" type="text/css">
<link rel="icon" type="image/png" href="<?php echo e_THEME; ?>favicon.ico" />
<meta http-equiv="Content-Type" content="text/css;charset=UTF-8">
<title><!--TITLE--></title>
</head>
<body>
<script>
	$(document).ready(function()
	{
        $('.file').preimage();
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
	<form name="avvie" method="post" enctype="multipart/form-data" action='handlers/avatar_upload.php?mode=select&uid=<?php echo $uid ?>'>
		<h3 align="center"><?php echo LAN_AVVIE_2; ?></h3>
		<br />
		<input class="file" id="file1" type='file' name="photoimg" multiple />
		<fieldset><div id="prev_file1"></fieldset></div><br/>
		<input type="submit" name="test" value="<?php echo LAN_AVVIE_3; ?>" onclick="this.forms.avvie.submit();window.parent.location.reload();" />
	</form>
</body>
</html>

<?php
$pageContents = ob_get_contents ();
ob_end_clean (); // Wipe the buffer
echo str_replace ('<!--TITLE-->', $pageTitle, $pageContents); //replace the TITLE