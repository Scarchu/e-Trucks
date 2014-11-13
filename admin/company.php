<?php
require_once "../class.php";
require_once(HEADERF);

if(isset($_POST['company_selection']))
{
	$company_id = $_POST['company_selection'];
	$sql -> db_Select("companies", "*", "id='".$company_id."'");
	$result = $sql -> db_Fetch();
	$result1 = unserialize($result['trucks']);
	//------------------------//
	$sql2 -> db_Select("companies", "trucks", "id<>'".$company_id."' ORDER BY id ASC");
	while($result2 = $sql2 -> db_Fetch())
	{
		foreach($result2 as $key => $value)
		{
			if($key == "truck")
			{
				$reserved[] = unserialize($value);
			}
		}
	}
		
	function allowed_checking($array, $key)
	{
		foreach ($array as $item)
        if (isset($item[$key]))
			return true;
		return false;
	}

	//------------------------//
	//$available_trucks = $pref['broi_trucks'];
	$company_name = $result['name'];
	$company_vat = $result['VAT_number'];
	$company_location = $result['location'];
		
	echo '
		<h1>Избрана фирма за корекция: <u>'.$result["name"].'</u></h1>
		<br />
		<div id="comp_edit">
		<form method="post" action="'.e_SELF.'">
			<table border="1" width="100%">
				<tr>
					<th>Свободни камиони</th>
				</tr>
				<tr>
					<td  valign="top">
						<table border="0" width="100%">
						';
						for($i=1; $i<=$pref['broi_trucks']; $i++)
						{
							echo'
								<tr>
									<td>'.$pref['trucks_plate'][$i-1].'</td>
									<td width="10%"><input type="checkbox" name="select_trucks['.$i.']" '; if(isset($result1[$i])) { echo "checked "; } if(allowed_checking($reserved, $i)) { echo "disabled"; } echo' /></td>
								</tr>
							';
						}
						echo'
						</table>
					</td>
				</tr>
			</table>
			<br />
			<table border="1" width="100%">
				<tr>
					<th>Име на фирмата</th>
					<td style="text-align: right;"><input type="text" name="comp_name" value="'.$company_name.'" /></td>
				</tr>
				<tr>
					<th>ДДС номер</th>
					<td style="text-align: right;"><input type="text" name="comp_VAT" value="'.$company_vat.'" /></td>
				</tr>
				<tr>
					<th>Местонахождение</th>
					<td style="text-align: right;"><textarea style="resize: none;" rows="2 " cols="20" name="comp_location">'.$company_location.'</textarea></td>
				</tr>
			</table>
			<br />
			<input type="hidden" name="id" value="'.$company_id.'" />
			<input type="submit" name="company_save" value="Запиши" />
			<input type="button" value="Назад" onClick="history.go(-1);return true;">
		</form>
		</div>
	';
	require_once (FOOTERF);
}
elseif(isset($_POST['company_save']))
{
	$company_id = $_POST['id'];
	$company_name = $_POST['comp_name'];
	$company_vat = $_POST['comp_VAT'];
	$company_location = $tp -> toForm($_POST['comp_location']);
	$selected_trucks = (!empty($_POST['select_trucks'])) ? serialize($_POST['select_trucks']) : '';
	$sql2 -> db_Update("companies", "name='$company_name', VAT_number='$company_vat', location='$company_location', trucks='$selected_trucks' WHERE id='".$company_id."'");
	header("Location: ".e_ADMIN."index.php?mode=companies");
	exit;
}
else
{
	echo "Станала е някаква грешка!";
}
?>