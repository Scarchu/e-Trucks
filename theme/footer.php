<?php
include_once e_ADMIN.'ver.php';
if(isset($_SESSION['user_id']))
{
	$last_logged = time();
	$sql -> db_Update("users", "last_loggedin='".$last_logged."' WHERE userid='".$_SESSION['user_id']."'");
}
$sql -> db_Close();

$pageContents = ob_get_contents ();
ob_end_clean (); // Wipe the buffer
echo str_replace ('<!--TITLE-->', $pageTitle, $pageContents); //replace the TITLE

//////////////////////////////////////////////////////
include e_BASE."languages/Bulgarian/Bulgarian.php";
	$eTimingStop = microtime();
	global $eTimingStart;
	$rendertime = number_format($eTraffic->TimeDelta( $eTimingStart, $eTimingStop ), 4);
	$db_time    = number_format($db_time,4);
	$rinfo = '';

	$rinfo .= CORE_LAN11.$rendertime.CORE_LAN12.$db_time.CORE_LAN13;
	$rinfo .= CORE_LAN15.$sql -> db_QueryCount().". ";
	$rinfo .= CORE_LAN16.$sc_trucks->get_memory_usage();	
/////////////////////////////////////////////////////
?>
	</div>
	<hr>
	<table width="100%" border="0" cellpadding="0" class="footer">
		<tr>
			<td align="left">Потребители на линия: <b><?php online(); ?></b></td>
			<td align="right">Версия: <a href="ver.txt"><?php echo VER; ?></a></td>
		</tr>
		<tr>
		<?php 
			echo "<td class='center'>&nbsp;&nbsp;&nbsp;&nbsp;e-Камиони ".'&copy; ' . date('Y')."</td>";
		
			if(isset($_SESSION['user_id']) && USERLV == ADMIN_LEVEL)
				{
					echo "<td colspan='2' class='right' width='10%'><a href='".e_BASE."admin/'>Администрация</a></td>";
				}
				else
				{
					echo "<td class='right' width='10%'></td>";
				}
			if($pref['maintenance']) echo "<tr><td colspan='2'><h1 class='red center'>Сайтът е затворен!!!</h1></td></tr>"
		?>
		</tr>
	</table>
	<p class="center"><?php echo ($rinfo ? "\n<div style='text-align:center' class='smalltext'>{$rinfo}</div>\n" : ""); ?></p>
	<p class="right">
		<a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Валиден CSS!" /></a>
	</p>
</body>
</html>