<?php
require_once "../class.php";

$uid = $_GET['uid'];
$mode = $_GET['mode'];

$sql -> db_Select("users", "*", "userid=".$uid."");
$user = $sql -> db_Fetch();

if($mode == "select")
{
	include_once "img_resize_class.php";
	if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
	{
		$filename = $_FILES['photoimg']['tmp_name']; //get the temporary uploaded image name
		$valid_formats = array("jpg", "png", "gif", "bmp", "jpeg","GIF","JPG","PNG"); //add the formats you want to upload
		$name = $_FILES['photoimg']['name']; //get the name of the image
		$size = $_FILES['photoimg']['size']; //get the size of the image
		if(strlen($name)) //check if the file is selected or cancelled after pressing the browse button. 
		{
		
			list($txt, $ext) = explode(".", $name); //extract the name and extension of the image
			if(in_array($ext,$valid_formats)) //if the file is valid go on.
			{
				if($size < 6098888) // check if the file size is more than 6 mb
				{
					$actual_image_name =  str_replace(" ", "_", $txt)."_".time().".".$ext; //actual image name going to store in your folder
					$tmp = $_FILES['photoimg']['tmp_name'];
					if(move_uploaded_file($tmp, e_UPLOADS."avatars/".$actual_image_name)) //check the path if it is fine
					{
						move_uploaded_file($tmp, e_UPLOADS."avatars/".$actual_image_name); //move the file to the folder
						$resizeObj = new resize(e_UPLOADS."avatars/".$actual_image_name); //start resizing
						$resizeObj -> resizeImage(240, 240, "auto");
						$resizeObj -> saveImage(e_UPLOADS."avatars/".$user['username'].'_avatar.'.$ext, 100);
						unlink(e_UPLOADS."avatars/".$actual_image_name);
						$avvie = $user['username']."_avatar.".$ext."";
						$sql2 -> db_Update("users", "avatar='".$avvie."' WHERE userid='".$uid."'");
						echo "<script>window.close();</script>";
						echo "<script>window.opener.location.reload(false);</script>";
					}
					else
					{
						echo "Грешка";
					}
				}
				else
				{
					echo "Файла не трябва да надвишава 6MB";					
				}
			}
			else
			{
				echo "Невалиден формат на файла";	
			}
		}
		else
		{		
			echo "Моля, изберете картинка!";
		}		
		exit;
	}
	else
	{
		echo "Тотална грешка";
	}
}
elseif($mode == "update")
{
	$result = $_POST['result'];
	$sql -> db_Update("users", "avatar='' WHERE userid='".$uid."'");
}
?>