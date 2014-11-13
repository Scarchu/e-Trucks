<?php
$grpdeb=0;
echo'
	<table width="100%"  border="'.$grpdeb.'" cellpadding="3" cellspacing="0">
		<br>
		<tr>
			<td width="18%"></td>
			<td width="7%" class="help1"><b> Изход:  </b></td>
			<td width="60%" class="help2"> Този бутон деактивира текущият потребител. </td>
		</tr>
		<tr>
			<td width="18%"></td>
			<td width="7%" class="help1"><b> Начало:  </b></td>
			<td width="60%" class="help2"> Този бутон Ви връща към този екран. </td>
		</tr>
		<tr>
			<td width="18%"></td>
			<td width="7%" class="help1"><b> СА******:  </b></td>
			<td width="60%" class="help2"> Тези бутони избират камиона върху който ще работите. </td>
		</tr>
		<tr>
			<td width="18%"></td>
			<td width="7%" class="help1"><b> Допълнителни:  </b></td>
			<td width="60%" class="help2"> Този бутон показва информация за изпратените ЧМР-та и Тахо файлове. </td>
		</tr>
		<tr>
	</table>
	<table width="80%"  border="'.$grpdeb.'" cellpadding="1" cellspacing="0" align="center">
		<tr>
			<td><hr color="green"></td>
		</tr>
		<br />
	</table>
	<br />
	<table width="100%"  border="'.$grpdeb.'" cellpadding="3" cellspacing="0">
		<tr>
			<td width="17%"></td>
			<td width="7%" class="help1"><b> Гориво:  </b></td>
			<td width="60%" class="help2"> За въвеждане на информацията за сипаното гориво. </td>
		</tr>
		<tr>
			<td width="17%"></td>
			<td width="7%" class="help1"><b> Товар:  </b></td>
			<td width="60%" class="help2"> За въвеждане на информацията за товара и дестинацията. </td>
		</tr>
		<tr>
			<td width="17%"></td>
			<td width="7%" class="help1"><b> ЧМР:  </b></td>
			<td width="60%" class="help2"> За автоматично изпращане на шасито и номера на ремаркето. (изключено)</td>
		</tr>
		<tr>
			<td width="17%"></td>
			<td width="7%" class="help1"><b> ТАХО:  </b></td>
			<td width="60%" class="help2"> (само за дигитални тахографи) Качване на свалените от картата и тахографа файлове. </td>
		</tr>
		<tr>
	</table>
	<table width="80%"  border="'.$grpdeb.'" cellpadding="1" cellspacing="0" align="center">
		<tr>
			<td><hr color="green"></td>
		</tr>
		<br />
	</table>
	<br />
	<table width="100%"  border="'.$grpdeb.'" cellpadding="3" cellspacing="1">
		<tr>
			<td width="17%"></td>
			<td width="7%" class="help1"><b> Пълни ли са резервоарите?:  </b></td>
			<td width="60%" class="help2"> ДА - когато със сипаното гориво резервоарите са се напълнили до горе.<br />НЕ - когато със сипаното гориво резервоарите не са се напълнили. </td>
		</tr>
		<tr>
			<td width="17%"></td>
			<td width="7%" class="help1" bgcolor='.$pref['color_cash'].'><b>Оцветено в</b></td>
			<td width="60%" class="help2">Гориво, платено в кеш.</td>
		</tr>
		<tr>
			<td width="17%"></td>
			<td width="7%" class="help1" bgcolor='.$pref['color_empty'].'><b>Оцветено в</b></td>
			<td width="60%" class="help2">Празен курс.</td>
		</tr>
		<tr>
			<td width="17%"></td>
			<td width="7%" class="help1"><b></b></td>
			<td width="60%" class="help2"></td>
		</tr>
	</table>
';
?>