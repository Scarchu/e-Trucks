<?php 
include_once "class.php";
include_once (HEADERF);
require "handlers/email.php";

if(!isset($_GET['pr']) and !isset($_POST['pr'])) { echo '<p class="message">Станала е някаква грешка или <br /> това е опит за хакване!</p><br />'; include (FOOTERF); exit; }
$pr = isset($_GET["pr"]) ? $_GET["pr"] : $_POST["pr"]; 

//if(isset($errrno)) $error = $errno;      //za dooprawiane

if (isset($_POST['Mail']))
{
	$pr=$_POST["pr"]; 
	$numb=trim($_POST["numb"]);
	$chass=trim($_POST["chass"]);
	$date=$_POST["date"];
	$numb_lenght = strlen($numb);
	$chass_lenght = strlen($chass);

	if ((empty($numb)) | (empty($chass)) | (empty($date))) { $error = "Има непопълнено поле!"; }
	elseif(($numb_lenght < 6) or ($numb_lenght > 6)) { $error = "Некоректна дължина на Номера!"; }
	elseif(($chass_lenght < 17) or ($chass_lenght > 17)) { $error = "Некоректна дължина на Шасито!"; }
	else
	{
		$sql -> db_Insert("cmr", "'', '$date', '$pr', '$numb', '$chass'");
		//header("location: ../index.php?truck=$trid");
		
		$mail = new EMail;
		$mail->Username = $pref['chmr_username'];
		$mail->Password = $pref['chmr_password'];
		$mail->SetFrom($pref['chmr_from'],"");		// Name is optional
		$mail->AddTo($pref['chmr_to1'],"");			// Name is optional
		$mail->AddTo($pref['chmr_to2'],"");			// Name is optional
		$mail->Subject = $pref['chmr_subject'];
		$mail->Message = "Trailer number: ".$numb."<br />Trailer chassis: ".$chass."";
		//Optional stuff	
		//$mail->AddCc("someother3@address.com","name 3"); 	// Set a CC if needed, name optional
		$mail->ContentType = "text/html; charset=UTF8";        		// Defaults to "text/plain; charset=iso-8859-1"
		//$mail->Headers['X-SomeHeader'] = 'abcde';		// Set some extra headers if required
		$mail->ConnectTimeout = 30;		// Socket connect timeout (sec)
		$mail->ResponseTimeout = 8;		// CMD response timeout (sec)
		$success = $mail->Send();
		echo "<center><h1>Номера и Шасито са изпратени успешно </h1></center><br>";	
		//echo "<script type='text/javascript'>setTimeout(function() {history.go(-2);},1850);</script>";
	}
}
?>

<script>
$(function() {
	$( "#date" ).datepicker({ dateFormat: "dd.M.yy", firstDay: 1, monthNames: [ "Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември" ] });
});
</script>

<h1><b>Въвеждане на ремарке</b></h1>
<br />
<?php if(isset($error)) { echo '<p class="message">'.$error.'</p><br />'; } ?>
<div id="chmr">
	<form name="form" method="post" action="<?php echo e_SELF; ?>">
		<table width="99%"  border="1" cellpadding="3" cellspacing="0" style="margin-left:auto; margin-right:auto;">
			<tr class="text">
				<th>Номер</th><td><input type="text" name="numb" /></td>
			</tr><tr>
				<th>Шаси</th><td><input type="text" name="chass" /></td>
			</tr><tr>
				<th>Дата</th><td><input type="text" name="date" id="date" /></td>
			</tr>
		</table>
		<br />
		<input type="hidden" name="pr" value="<?php echo $pr ?>" />
		<input type="button" value="Назад" onClick="history.go(-1);return true;">
		<input type="submit" name="Mail" value="Въвеждане" autofocus />
	</form>
</div>
<br />
<p style="text-align: center; color: red;">Номерът и шасито трябва да бъдат изписани слято, без празни пространства,<br />също така, Номерът трябва да е от 6 символа, а Шасито от 17.</p>
<br />

<?php 
include_once(FOOTERF);
?>