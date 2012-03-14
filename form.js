var errors;
var warnings;

function validateForm() {
	errors = false;
	warnings = false;
	
	// Empty the error- and warning-containers
	document.getElementById('errors').innerHTML = "";
	document.getElementById('warnings').innerHTML = "";
	document.getElementById('messages').innerHTML = "";
	makeAllFieldsNormal();
	
	// Validate the all input
	validateAllInput();
	
	// Disable the submit button if errors occured
	if (errors) {
		document.getElementById('submit_button').disabled = true;
		document.getElementById('messages').innerHTML = "The submit button will become available once you correctly enter all required data.<br/>" + document.getElementById('messages').innerHTML;
		document.getElementById('messages').innerHTML = "Click on the error message to go to the concerning field.<br/>" + document.getElementById('messages').innerHTML;
	} else {
		document.getElementById('submit_button').disabled = false;
		document.getElementById('messages').innerHTML = "Please fill out the captcha, then click the submit button to send your observation to the server.<br/>" + document.getElementById('messages').innerHTML;
	}
	
	return errors;
}


function validateAllInput() {
	// Check when-fieldset: hours/minutes/seconds/offset - check range + whether numbers
	if (isEmptyField('when_hour')) { reportError('when_hour', "Please fill in the time (hours) you saw the fireball"); }
	if (isEmptyField('when_minute')) { reportError('when_minute', "Please fill in the time (minutes) you saw the fireball"); }
	if (isEmptyField('when_offset')) { reportError('when_offset', "Please fill in your local offset to UTC"); }
	if (!isUInt('when_hour')) { reportError('when_hour', "Value for hours should be a number"); }
	if (!isUInt('when_minute')) { reportError('when_minute', "Value for minutes should be a number"); }
	if (!isEmptyField('when_second') && !isUInt('when_second')) { reportError('when_second', "Value for seconds should be a number"); }
	if (!isInt('when_offset')) { reportError('when_offset', "Value for offset should be a number"); }
	if (!isInRange('when_hour', 0, 23)) { reportError('when_hour', "Value for hours should lie in the range 0..23"); }
	if (!isInRange('when_minute', 0, 59)) { reportError('when_minute', "Value for minutes should lie in the range 0..59"); }
	if (!isInRange('when_second', 0, 59)) { reportError('when_second', "Value for seconds should lie in the range 0..59"); }
	if (!isInRange('when_offset', -12, 12)) { reportError('when_offset', "Value for offset should lie in the range -12..12"); }
	if (!validDate()) { reportError('when_day', "The date you entered does not exist."); }
	if (!dateNotInFuture()) { reportError('when_day', "You cannot report an event that has not yet taken place."); makeFieldRed('when_month'); }
	if (!timeNotInFuture()) { reportError('when_hour', "You cannot report an event that has not yet taken place."); makeFieldRed('when_minute'); }
	
	// Check where-fieldset: latitude/longitude - check range + whether numbers
	if (isEmptyField('where_latitude')) { reportError('where_latitude', "Please fill in your location (latitude) at the time you saw the fireball"); }
	if (isEmptyField('where_longitude')) { reportError('where_longitude', "Please fill in your location (longitude) at the time you saw the fireball"); }
	if (!isFloat('where_latitude')) { reportError('where_latitude', "Value for latitude should be a number"); }
	if (!isInRange('where_latitude', -180, 180)) { reportError('where_latitude', "Value for latitude should lie in the range -180..180"); }
	if (!isFloat('where_longitude')) { reportError('where_longitude', "Value for longitude should be a number"); }
	if (!isInRange('where_longitude', -90, 90)) { reportError('where_longitude', "Value for longitude should lie in the range -90..90"); }
	
	// Check what-fieldset: magnitude/duration/train/ang. vel/interval - check range + whether numbers
	if ((!isEmptyField('what_mag')) && (!isFloat('what_mag'))) { reportError('what_mag', "Value for magnitude should be a number"); }
	if ((!isEmptyField('what_mag')) && (!isInRangeNegative('what_mag', -3.0, -30.0))) { reportError('what_mag', "Value for magnitude is always negative, it should lie in the range -3..-30"); }
	if ((!isEmptyField('what_duration')) && (!isFloat('what_duration'))) { reportError('what_duration', "Value for duration should be a number"); }
	if ((!isEmptyField('what_duration')) && (!isInRange('what_duration', 0, 1000))) { reportError('what_duration', "Value for duration is always positive"); }
	if ((!isEmptyField('what_train')) && (!isFloat('what_train'))) { reportError('what_train', "Value for persistent train should be a number"); }
	if ((!isEmptyField('what_train')) && (!isInRange('what_train', 0, 1000))) { reportError('what_train', "Value for persistent train is always positive"); }
	if ((!isEmptyField('what_vel')) && (!isFloat('what_vel'))) { reportError('what_vel', "Value for angular velocity should be a number"); }
	if ((!isEmptyField('what_vel')) && (!isInRange('what_vel', 0, 1000))) { reportError('what_vel', "Value for angular velocity is always positive"); }
	if ((!isEmptyField('what_interval')) && (!isFloat('what_interval'))) { reportError('what_interval', "Value for interval between fireball and sound should be a number"); }
	if ((!isEmptyField('what_interval')) && (!isInRange('what_interval', 0, 1000))) { reportError('what_interval', "Value for interval between fireball and sound is always positive"); }
	
	// Check who-fieldset: first name/last name/country	/e-mail/phone
	if (isEmptyField('who_firstname')) { reportError('who_firstname', "Please fill in your first name"); }
	if (isEmptyField('who_lastname')) { reportError('who_lastname', "Please fill in your last name"); }
	if (isEmptyField('who_country')) { reportError('who_country', "Please fill in your country's name"); }
	if (isEmptyField('who_email')) { reportError('who_email', "Please fill in your e-mail address"); }
	if (!isEmailAddress('who_email')) { reportError('who_email', "Please fill in a valid e-mail address"); }
	if ((!isEmptyField('who_phone')) && (!isPhoneNr('who_phone'))) { reportError('who_phone', "Please fill in a valid phone number"); }
}


// Check if contents of field are empty
function isEmptyField(id) {
	var val = document.getElementById(id).value;
	return (val == "");
}


// Check if contents of field is an unsigned integer
function isUInt(id) {
	var check = document.getElementById(id).value;
	return (check.toString().search(/^[0-9]+$/) == 0);
}


// Check if contents of field is an integer
function isInt(id) {
	var check = document.getElementById(id).value;
	return (check.toString().search(/^-?[0-9]+$/) == 0);
}


// Check if contents of field is a float
function isFloat(id) {
	var check = document.getElementById(id).value;
	return (check.toString().search(/^-?[0-9]*.?[0-9]*$/) == 0);
}


// Check if contents of field lies in the specified range (inclusive)
function isInRange(id, lower, upper) {
	var val = document.getElementById(id).value;
	var lowerOK = (val >= lower);
	var upperOK = (val <= upper);
	return (lowerOK && upperOK);
}
function isInRangeNegative(id, lower, upper) {
	var val = Math.abs(document.getElementById(id).value);
	var lowerOK = (val >= Math.abs(lower));
	var upperOK = (val <= Math.abs(upper));
	return (lowerOK && upperOK);
}


// Check if entered dates are valid
function validDate() {
	// Get values from form
	var when_year = document.getElementById('when_year').value;
	var when_month = document.getElementById('when_month').value;
	var when_day = document.getElementById('when_day').value;
	// Create a date object using the input values - JS indices for months start at 0!
	var check = new Date();
	check.setFullYear(when_year, (when_month-1), when_day);
	// Check if the month that was entered (1..12) and the generated one (0..11) are the same
	// If not, date was not valid (like Feb 31)
	return ( ((check.getMonth()+1) == when_month) && (check.getDate() == when_day) );
}


// Make sure the entered date is not in the future
function dateNotInFuture() {
	// Get values from form
	var when_year = document.getElementById('when_year').value;
	var when_month = document.getElementById('when_month').value;
	var when_day = document.getElementById('when_day').value;
	// Create a current date object
	var check = new Date();
	// Input year should be the same as this one or smaller
	var yearOK = (when_year <= check.getFullYear());
	// Assume month-day combination is erroneous...
	var monthdayOK = false;
	// except when the month was before this one
	if (when_month < check.getMonth()+1) {
		monthdayOK = true;
	} 
	// except when the months are the same, but the day was this one, or before this one
	if (when_month == check.getMonth()+1) {
		// Check day
		if (when_day <= check.getDate()) {
			monthdayOK = true;
		}
	}
	return (yearOK && monthdayOK);
}


// Make sure the time is not in the future is entered date was today
function timeNotInFuture() {
	// Get values from form
	var when_year = document.getElementById('when_year').value;
	var when_month = document.getElementById('when_month').value;
	var when_day = document.getElementById('when_day').value;
	var when_hour = document.getElementById('when_hour').value;
	var when_minute = document.getElementById('when_minute').value;
	// Create a current date object
	var check = new Date();
	var valid = true;
	// Check if entered date was today
	if ((when_year == check.getFullYear()) && (when_month == check.getMonth()+1) && (when_day == check.getDate())) {
		// If the entered hour > hour now, then this time is in the future
		if (when_hour > check.getHours()) {
			valid = false;
		}
		// If the entered hour = hour now, but entered minutes > minutes now, this time is in the future
		if ((when_hour == check.getHours()) && (when_minute > check.getMinutes())) {
			valid = false;
		}
	}
	// If not, there's no problem!
	return valid;
}


// Check whether a field contains a valid e-mail address
function isEmailAddress(id) {
	var check = document.getElementById(id).value;
	return (check.toString().search(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*\.(\w{2}|(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum))$/) == 0);
}


// Check whether a field contains a valid phone nr
function isPhoneNr(id) {
	var check = document.getElementById(id).value;
	return (check.toString().search(/^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/) == 0);
}


// Report an error to the user
function reportError(id, errorString) {
	makeFieldRed(id);
	var id_array = id.split('_', 1);
	document.getElementById('errors').innerHTML += "<a href=\"#"+id_array[0]+"\"><img src=\"images/arrow_up2.png\" style=\"margin-right: 0.7em;\" /><b>Error:</b> "+errorString+"</a><br/>";
	errors = true;
}


// Make a field red
function makeFieldRed(id) {
	document.getElementById(id).style.color = '#B70000';
	document.getElementById(id).style.borderColor = '#B70000';
	document.getElementById(id).style.backgroundColor = '#F8E0E0'; 
	errors = true;
}


// Make all fields normal - how they were specified in CSS
function makeAllFieldsNormal() {
	for(i=0; i<document.report_form.elements.length; i++) {
		var id = document.report_form.elements[i].id;
		if ((id != 'validate_button') && (id != 'submit_button')) {
			document.report_form.elements[i].style.color = '';
			document.report_form.elements[i].style.borderColor = '';
			document.report_form.elements[i].style.backgroundColor = '';
		}
	}
}


function disableFields() {
	for(i=0; i<document.report_form.elements.length; i++) {
		var id = document.report_form.elements[i].id;
		if ((id != 'validate_button') && (id != 'submit_button')) {
			document.report_form.elements[i].disabled = true;
			document.report_form.elements[i].style.color = '#EEEEEE';
		}
	}
}



function enableFields() {
	for(i=0; i<document.report_form.elements.length; i++) {
		var id = document.report_form.elements[i].id;
		if ((id != 'validate_button') && (id != 'submit_button')) {
			document.report_form.elements[i].disabled = false;
			document.report_form.elements[i].style.color = '';
		}
	}
}




function showRecaptcha(recaptcha_div, recaptcha_submit) {
	Recaptcha.create("6LdEkQEAAAAAACAj6JtEnUJOVbFMttLAdBSrs07d", recaptcha_div, {theme: 'white', callback: Recaptcha.focus_response_field});
}

function destroyRecaptcha() {
	Recaptcha.destroy();
}


function finishForm() {
	if (document.getElementById('validate_button').value == "Check my inputs") {
		if (!validateForm()) {
			showRecaptcha('recaptcha_div', 'submit_button');
			disableFields();
			document.getElementById('validate_button').value = "Make changes to my inputs";
		}
	} else if (document.getElementById('validate_button').value = "Make changes to my inputs") {
		document.getElementById('submit_button').disabled = true;
		destroyRecaptcha();
		enableFields();
		document.getElementById('validate_button').value = "Check my inputs";
	}
} 


























function whenXML() {
	var when_year = document.getElementById('when_year').value;
	var when_month = document.getElementById('when_month').value;
	var when_day = document.getElementById('when_day').value;
	var when_hour = document.getElementById('when_hour').value;
	var when_minute = document.getElementById('when_minute').value;
	var when_second = document.getElementById('when_second').value;
	var when_offset = document.getElementById('when_offset').value;
	
	var str = "<when>"
				+"<when_year>"+when_year+"</when_year>"
				+"<when_month>"+when_month+"</when_month>"
				+"<when_day>"+when_day+"</when_day>"
				+"<when_hour>"+when_hour+"</when_hour>"
				+"<when_minute>"+when_minute+"</when_minute>"
				+"<when_second>"+when_second+"</when_second>"
				+"<when_offset>"+when_offset+"</when_offset>"
			 +"</when>";
	return str;
}

function whereXML() {
	var where_latitude = document.getElementById('where_latitude').value;
	var where_longitude = document.getElementById('where_longitude').value;
	var where_location = document.getElementById('where_location').value;
	
	var str = "<where>"
				+"<where_latitude>"+where_latitude+"</where_latitude>"
				+"<where_longitude>"+where_longitude+"</where_longitude>"
				+"<where_location>"+where_location+"</where_location>"
			 +"</where>";
	return str;
}

function locationXML() {
	var loc_startdirection = document.getElementById('loc_startdirection').value;
	var loc_startheight = document.getElementById('loc_startheight').value;
	var loc_enddirection = document.getElementById('loc_enddirection').value;
	var loc_endheight = document.getElementById('loc_endheight').value;
	
	var str = "<loc>"
				+"<loc_startdirection>"+loc_startdirection+"</loc_startdirection>"
				+"<loc_startheight>"+loc_startheight+"</loc_startheight>"
				+"<loc_enddirection>"+loc_enddirection+"</loc_enddirection>"
				+"<loc_endheight>"+loc_endheight+"</loc_endheight>"
			 +"</loc>";
	return str;
}

function whatXML() {
	var what_mag = document.getElementById('what_mag').value;
	var what_mag_est = document.getElementById('what_mag_est').value;
	var what_duration = document.getElementById('what_duration').value;
	var what_color = document.getElementById('what_color').value;
	var what_train = document.getElementById('what_train').value;
	var what_vel = document.getElementById('what_vel').value;
	var what_vel_est = document.getElementById('what_vel_est').value;
	var what_sound = document.getElementById('what_sound').value;
	var what_interval = document.getElementById('what_interval').value;
	var what_general = document.getElementById('what_general').value;
	var what_fragmentation = "";
	
	for (i=0; i<document.report_form.fragmentation.length; i++) {
    	if (document.report_form.fragmentation[i].checked==true) {
       		what_fragmentation = document.report_form.fragmentation[i].value;
		}
    }
	
	var str = "<what>"
			 	+"<what_mag>"+what_mag+"</what_mag>"
				 +"<what_mag_est>"+what_mag_est+"</what_mag_est>"
				 +"<what_duration>"+what_duration+"</what_duration>"
				 +"<what_color>"+what_color+"</what_color>"
				 +"<what_fragmentation>"+what_fragmentation+"</what_fragmentation>"
				 +"<what_train>"+what_train+"</what_train>"
				 +"<what_vel>"+what_vel+"</what_vel>"
				 +"<what_vel_est>"+what_vel_est+"</what_vel_est>"
				 +"<what_sound>"+what_sound+"</what_sound>"
				 +"<what_interval>"+what_interval+"</what_interval>"
				 +"<what_general>"+what_general+"</what_general>"
			 +"</what>";
	
	return str;
}

function extrasXML() {
	var fireball_other = document.getElementById('fireball_other').value;
	
	var str = "<fireball_other><fireball_remarks>"+fireball_other+"</fireball_remarks></fireball_other>";
	return str;
}

function whoXML() {
	var who_firstname = document.getElementById('who_firstname').value;
	var who_lastname = document.getElementById('who_lastname').value;
	var who_country = document.getElementById('who_country').value;
	var who_email = document.getElementById('who_email').value;
	var who_phone = document.getElementById('who_phone').value;
	
	var str = "<who>"
				+"<who_firstname>"+who_firstname+"</who_firstname>"
				+"<who_lastname>"+who_lastname+"</who_lastname>"
				+"<who_country>"+who_country+"</who_country>"
				+"<who_email>"+who_email+"</who_email>"
				+"<who_phone>"+who_phone+"</who_phone>"
			 +"</who>";
	return str;
}

function referrerXML() {
	var referrer = document.getElementById('referrer_code').value;
	var language = document.getElementById('language').value;
	var str = "<referrer><code>"+referrer+"</code><language>"+language+"</language></referrer>";
	return str;
}

function recaptchaXML() {
	var challenge = document.getElementById('recaptcha_challenge_field').value;
	var response = document.getElementById('recaptcha_response_field').value;
	var str = "<recaptcha><recaptcha_challenge_field>"+challenge+"</recaptcha_challenge_field><recaptcha_response_field>"+response+"</recaptcha_response_field></recaptcha>";
	return str;
}

function doSubmit( ) {
	var when_str = whenXML();
	var where_str = whereXML();
	var loc_str = locationXML();
	var what_str = whatXML();
	var extras_str = extrasXML();
	var who_str = whoXML();
	var ref_str = referrerXML();
	var recaptcha_str = recaptchaXML();
	
	var xmlstr = '<vfo>'+recaptcha_str+'<obs>'+when_str+where_str+loc_str+what_str+extras_str+who_str+ref_str+'</obs></vfo>';
	//alert(xmlstr);
	new Ajax.Updater( 'result', 'http://umdb.urania.be/smena/form_processor.php', { method: 'post', parameters: { xml: xmlstr } } );
	
	document.getElementById('messages').innerHTML = "";
	document.getElementById("submit_button").value="Submitted!";
	document.getElementById('validate_button').disabled = true;
	document.getElementById('submit_button').disabled = true;
}











function toggle_visibility(id) {
	var e = document.getElementById(id);
	if (e.style.display == 'block') {
		e.style.display = 'none';
		document.getElementById('arrow').src = "images/arrow_down2.png";
	} else {
		e.style.display = 'block';
		document.getElementById('arrow').src = "images/arrow_up2.png";
	}
}