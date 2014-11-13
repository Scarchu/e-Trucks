<!-- towari -->

<?php
global $sql;
$truck = "truck".$pr."_cg";
$sql -> db_Select("$truck", "MAX(trip)", "");
$max_trip1 = $sql -> db_Fetch();
$current_trip1 = $max_trip1['MAX(trip)'];
if($current_trip1 == "")
$current_trip1 = 0;
?>

<script>
$(function() {
var tripc = $( "#tripc" ),
road = $( "#road" ),
date2 = $( "#date2" ),
empty = $( "#empty" ),
allFields = $( [] ).add( tripc ).add( road ).add( date2 ).add( empty ),
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

$( "#dialog-formc" ).dialog({ 
autoOpen: false,
show: "blind",
hide: "explode",
height: 475,
width: 440,
modal: true,
position:['middle',70],
buttons: {
"Добави": function() {
var bValid = true;
allFields.removeClass( "ui-state-error" );
bValid = bValid && checkLength( tripc, "километража", 1, 7 );
bValid = bValid && checkLength( road, "маршрута", 0, 100 );
bValid = bValid && checkLength( date2, "датата", 9, 11 );
bValid = bValid && checkLength( empty, "празен", 0, 9 );
bValid = bValid && checkRegexp( tripc, /^([0-9])+$/, "Километражът може да е само цифри 0-9" );
bValid = bValid && checkRegexp( road, /^([0-9а-яА-Яa-zA-Z-,.ÄäÖöÜüß ])+$/, "Маршрутът не може да бъде без име!" );
bValid = bValid && checkRegexp( date2, /^([0-9a-zA-Z.])+$/, "Датата позволява само: a-z 0-9 и ." );
bValid = bValid && checkCurrent( tripc, "Километраж", <?php echo $current_trip1; ?>);
if ( bValid ) {
   $.ajax({
				type:"POST",
				url: "submit.php?mode=cargo&pr=<?php echo $pr; ?>&ui=<?php echo $_SESSION['user_id']; ?>",
				data : ({
					tripc:tripc.val(),
					road:road.val(),
					date2:date2.val(),
					empty:empty.val()
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

$( "#create-cargo" ).click(function() {
$( "#dialog-formc" ).dialog( "open" );
});

});
</script>

<script>
$(function() {
	$( "#date2" ).datepicker({ dateFormat: "dd.M.yy", firstDay: 1, monthNames: [ "Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември" ] });
});
</script>

<body>
<div id="dialog-formc" title="Въвеждане товар" style="background:#FFFFCC;">
<p class="validateTips">Полетата са задължителни.</p>
<fieldset>
<script src="handlers/calendar/scripts.js" type="text/javascript"></script>
<table border="0" cellpadding="2">
	<tr>
	<td><label class="label_j text_input" for="tripc">Километраж</label></td><td><input class="input_j" type="text" name="tripc" id="tripc" /></td>
	</tr><tr>
	<td><label class="label_j text_input" for="road">Маршрут</label></td><td><input class="input_j" type="text" name="road" id="road" value="" /></td>
	</tr><tr>
	<td><label class="label_j text_input" for="date2">Дата</label></td><td><input class="input_j" type="text" name="date2" id="date2" value="" /></td>
	</tr><tr>
	<td><label class="label_j text_input" for="empty">Празен ли е курса?</label></td><td><select id="empty"><option value="unchecked">Не</option><option value="checked">Да</option></td>
	</tr><tr>
	<td align="center"><img src="<?php echo e_THEME; ?>/Images/cargo.png" /></td>
	</tr>
</table>
</fieldset>
</div>
</body>