<!-- goriwo -->

<?php
global $sql;
$truck = "truck".$pr."_lt";
$sql -> db_Select("$truck", "MAX(trip)", "");
$max_trip = $sql -> db_Fetch();
$current_trip = $max_trip['MAX(trip)'];
if($current_trip == "") { $current_trip = 0; }

$modes = array();

	if($mod == "frigo")
	{
		$modes = array(
			0 => 'var trip = $( "#trip" ),liters = $( "#liters" ),literst = $( "#literst" ),date1 = $( "#date1" ),cash = $( "#cash" ),full = $( "#full" ),allFields = $( [] ).add( trip ).add( liters ).add( literst ).add( date1 ).add( cash ).add(full),',
			1 => '<tr><td><label class="label_j text_input" for="literst">Фриго Литри</label></td><td><input class="input_j" type="text" name="literst" id="literst" value="" /></td></tr>',
			2 => 'bValid = bValid && checkRegexp( literst, /^([0-9])+$/, "Фриго Литри позволява само цифри 0-9" );',
			3 => 'literst:literst.val(),',
			4 => 'bValid = bValid && checkLength( literst, "Фриго литри", 0, 4 );'
			
		);
	}
	elseif($mod == "frigo_adblue")
	{
		$modes = array(
			0 => 'var trip = $( "#trip" ),liters = $( "#liters" ),literst = $( "#literst" ),adblue = $( "#adblue" ),date1 = $( "#date1" ),cash = $( "#cash" ),full = $( "#full" ),allFields = $( [] ).add( trip ).add( liters ).add( literst ).add( adblue ).add( date1 ).add( cash ).add(full),',
			1 => '<tr><td><label class="label_j text_input" for="literst">Фриго Литри</label></td><td><input class="input_j" type="text" name="literst" id="literst" value="" /></td></tr><tr><td><label class="label_j text_input" for="adblue">AdBlue Литри</label></td><td><input class="input_j" type="text" name="adblue" id="adblue" value="" /></td></tr>',
			2 => 'bValid = bValid && checkRegexp( literst, /^([0-9])+$/, "Фриго Литри позволява само цифри 0-9" ); bValid = bValid && checkRegexp( adblue, /^([0-9])+$/, "AdBlue позволява само цифри 0-9" );',
			3 => 'literst:literst.val(),adblue:adblue.val(),',
			4 => 'bValid = bValid && checkLength( literst, "Фриго литри", 0, 4 ); bValid = bValid && checkLength( adblue, "AdBlue", 0, 4 );'
		);
	}
	elseif($mod == "adblue")
	{
		$modes = array(
			0 => 'var trip = $( "#trip" ),liters = $( "#liters" ),adblue = $( "#adblue" ),date1 = $( "#date1" ),cash = $( "#cash" ),full = $( "#full" ),allFields = $( [] ).add( trip ).add( liters ).add( adblue ).add( date1 ).add( cash ).add(full),',
			1 => '<tr><td><label class="label_j text_input" for="adblue">AdBlue Литри</label></td><td><input class="input_j" type="text" name="adblue" id="adblue" value="" /></td></tr>',
			2 => 'bValid = bValid && checkRegexp( adblue, /^([0-9])+$/, "AdBlue позволява само цифри 0-9" );',
			3 => 'adblue:adblue.val(),',
			4 => 'bValid = bValid && checkLength( adblue, "AdBlue", 0, 4 );'
		);
	}
	elseif($mod == "fuel")
	{
		$modes = array(
			0 => 'var trip = $( "#trip" ),liters = $( "#liters" ),date1 = $( "#date1" ),cash = $( "#cash" ),full = $( "#full" ),allFields = $( [] ).add( trip ).add( liters ).add( date1 ).add( cash ).add(full),',
			1 => '',
			2 => '',
			3 => '',
			4 => ''
		);
	}

?>

<script>
$(document).ready(function() {
<?php echo $modes[0]; ?>
tips = $( ".validateTips" );
function updateTips( t ) {
tips
.text( t )
.addClass( "ui-state-highlight" );
setTimeout(function() {
tips.removeClass( "ui-state-highlight", 1500 );
}, 500 );
}

function checkCurrent(o, n, curr) {
if ( o.val() < curr ) {
o.addClass( "ui-state-error" );
updateTips( "Километражът не може да е по-малък от " + curr + " km!" );
return false;
} else {
return true;
}
}
function checkLength( o, n, min, max ) {
if ( o.val().length > max || o.val().length < min ) {
o.addClass( "ui-state-error" );
updateTips( "Дължината на " + n + " трябва да бъде между " +
min + " и " + max + " символа." );
return false;
} else {
return true;
}
}
function checkRegexp( o, regexp, z ) {
if ( !( regexp.test( o.val() ) ) ) {
o.addClass( "ui-state-error" );
updateTips( z );
return false;
} else {
return true;
}
}
$.fx.speeds._default = 590;

<!-- ========================================Start goriwo======================================= -->

$( "#dialog-form" ).dialog({
autoOpen: false,
show: "blind",
hide: "explode",
height: 500,
width: 440,
modal: true,
position:['middle',70],
buttons: {
"Добави": function() {
var bValid = true;
allFields.removeClass( "ui-state-error" );
bValid = bValid && checkLength( trip, "километража", 1, 7 );
bValid = bValid && checkLength( liters, "Камион литри", 0, 4 );
<?php echo $modes[4]; ?>
bValid = bValid && checkLength( date1, "датата", 9, 11 );
bValid = bValid && checkRegexp( trip, /^([0-9])+$/, "Километражът може да е само цифри 0-9" );
bValid = bValid && checkRegexp( liters, /^([0-9])+$/, "Камион Литри позволява само цифри 0-9" );
<?php echo $modes[2]; ?>
bValid = bValid && checkRegexp( date1, /^([0-9a-zA-Z.])+$/, "Датата позволява само: a-z 0-9 и ." );
bValid = bValid && checkCurrent( trip, "Километраж", <?php echo $current_trip; ?>);

if ( bValid ) {
   $.ajax({
                type:"POST",
                url: "submit.php?mode=fuel&pr=<?php echo $pr; ?>&ui=<?php echo $_SESSION['user_id']; ?>",
                data : ({
                    trip:trip.val(),
                    liters:liters.val(),
                    <?php echo $modes[3]; ?>
                    date1:date1.val(),
					cash:cash.val(),
					full:full.val()
                }) 
  });
$( this ).dialog( "close" );
}
},
Назад: function() {
$( this ).dialog( "close" );
}
},
close: function() {
allFields.val( "" ).removeClass( "ui-state-error" );
window.location.reload();
}
});

$( "#create-fuel" ).click(function() {
$( "#dialog-form" ).dialog( "open" );
});

});
</script>

<script>
$(function() {
	$( "#date1" ).datepicker({ dateFormat: "dd.M.yy", firstDay: 1, monthNames: [ "Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември" ] });
});
</script>

<div id="dialog-form" title="Въвеждане гориво" style="background:#DAFFEC;">
<p class="validateTips">Полетата са задължителни.</p>
<fieldset>
<table border="0" cellpadding="2">
	<tr>
	<td><label class="label_j text_input" for="trip">Километраж</label></td><td><input class="input_j" type="text" name="trip" id="trip" /></td>
	</tr><tr>
	<td><label class="label_j text_input" for="liter">Камион Литри</label></td><td><input class="input_j" type="text" name="liters" id="liters" value="" /></td>
	</tr>
	<?php echo $modes[1]; ?>
	<tr>
	<td><label class="label_j text_input" for="date1">Дата</label></td><td><input class="input_j" type="text" name="date1" id="date1" value="" /></td>
	</tr><tr>
	<td><label class="label_j text_input" for="cash">Кеш ли е горивото?</label></td><td><select class="text_input" id="cash"><option value="unchecked">Не</option><option value="checked">Да</option></td>
	</tr><tr>
	<td><label class="label_j text_input" for="cash">Пълни ли са резервоарите?</label></td><td><select class="text_input" id="full"><option value="checked">Да</option><option value="unchecked">Не</option></td>
	</tr><tr>
	<td align="center"><img src="<?php echo e_THEME; ?>/Images/fuel.jpeg" /></td>
	</tr>
</table>
</fieldset>
</div>