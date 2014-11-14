<?php
require_once ("class.php");
require_once (HEADERF);
include "languages/Bulgarian/lan_profile.php";
$pageTitle .= "::".LAN_PRO_1;

?>

<script language="javascript">
        var newwindow;

        function popit(url){
            newwindow = window.open(url, '', "status=yes, height=350px, width=450px, resizeable=1");
        }
</script>

<?php
$uid = $_GET['uid'];
require_once e_HANDLER.'userprofile_class.php';
$userprof = new userprofile;
$userprof -> user = $uid;
$userprof -> user_parse();
if(USERID == $uid)
{
?>
	<table border="0" align="center">
		<tr>
			<td><button onclick="popit('avvie.php?uid=<?php echo $uid ?>')"><?php echo LAN_PRO_2; ?></button></td>
			<td><button onclick="popit('changepass.php?mode=chgpwd&uid=<?php echo $uid ?>')"><?php echo LAN_PRO_3; ?></button></td>
			<?php
			if(USERID == 1)
			{
			?>
				<td><button onclick="window.location.href='<?php echo e_PLUGINS; ?>t_c/trucks_control.php'"><?php echo LAN_PRO_4; ?></button></td>
			<?php
			}
			?>
		</tr>
	</table>
<?php
}

require_once (FOOTERF);
?>

