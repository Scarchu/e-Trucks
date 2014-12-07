<?php
include_once "class.php";
include_once (HEADERF);

$pr = (isset($_GET['pr']) ? $_GET['pr'] : '');
$allowtype = array('rar', 'zip', 'esm', 'ddd', 'ESM', 'DDD');        // allowed extensions

if(isset($_FILES['fileup']) && strlen($_FILES['fileup']['name']) > 1)
{
	if($sql -> db_Select("tacho", "*", "fname='".basename( $_FILES['fileup']['name'])."'"))
	{
		error_display("ВНИМАНИЕ", "Такъв файл вече съществува!!!");
		echo '<img src="'.e_THEME.'Images/ajax-loader.gif" />';
		require_once(FOOTERF);
		exit();
	}
	$pr = $_POST['pr'];
	$upload_path = e_UPLOADS."tacho_files/".basename($_FILES['fileup']['name']);       // gets the file name
	$sepext = explode('.', strtolower($_FILES['fileup']['name']));
	$type = end($sepext);       // gets extension
	//list($width, $height) = getimagesize($_FILES['fileup']['tmp_name']);     // gets image width and height
	$err = '';
	// Checks if the file has allowed type, size, width and height (for images)
	if(!in_array($type, $allowtype)) $err .= '<p class="center">Файлът: <b>'. $_FILES['fileup']['name']. '</b> не е с разрешено разширение.</p>';
	if($_FILES['fileup']['size'] > $pref['upload_size']*1000) $err .= '<p class="center"><br/>Максималният размер на файла трябва да е: '. $pref['upload_size']. ' KB.</p>';
	if($err == '')
	{
		if(move_uploaded_file($_FILES['fileup']['tmp_name'], $upload_path))
		{
			$success_text = '
				<div class="center"><br />Файлът: <b>'. basename( $_FILES['fileup']['name']). '</b> е качен успешно!
				<br/>Вид: <b>'. $_FILES['fileup']['type'] .'</b>
				<br />Размер: <b>'. number_format($_FILES['fileup']['size']/1024, 3, '.', '') .'</b> KB</div>
			';
			error_display("УСПЕХ", $success_text, 640);
			echo '<img src="'.e_THEME.'Images/ajax-loader.gif" />';
			$date=date("d.M.y");
			$filename = basename( $_FILES['fileup']['name']);
			$fsize = number_format($_FILES['fileup']['size']/1024, 3, '.', '');
			$sql -> db_Insert("tacho", "'', '$date', '".USERID."', '$filename', '$fsize'");
		}
		else 
		echo '<b>Файлът не може да бъде качен!</b>';
	}
	else 
	{
		error_display("Внимание!", $err);
		echo '<p class="center"><img src="'.e_THEME.'Images/warning!.png" /></p>';
		echo '<script type="text/javascript">setTimeout(function() {history.go(-1);},3000);</script>';
	}
	include_once(FOOTERF);
	exit;
}
?> 
<div style="margin:1em auto; width:80%; text-align:center;" id="">
<h1>Качване на информацията от тахографа и тахо-картата</h1>
<br />
<br />
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data"> 
Качи файл: <input type="file" name="fileup" />
<br /><br />
<input type="button" value="Назад" onClick="history.go(-1);return true;">
<input type="hidden" name="pr" value="<?php echo $pr; ?>" />
<input type="submit" name='submit' value="Качи" />
</form>
<?php
echo "<b><p style='color:red;'>Позволени формати: </b>" ;
foreach($allowtype 	 as $value)
{
	echo ' ';
	print_r($value);
	echo ' ';
}
echo "<br><b> Максимален размер на файла: </b>";
echo "" . $pref['upload_size'] . " KB</p>";
?>
</div>
<?php

/*---------------------------------------------------------------------------*/
include(FOOTERF);