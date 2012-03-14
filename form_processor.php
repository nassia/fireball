<?php

// Includes
require_once('recaptcha.inc.php'); // Necessary for using Recaptcha
require_once('pgsql.inc.php');     // Include parameters for database access


// Turn off PHP Errors/Warnings when not in debugging mode, of course provide more userfriendly ones :)
$debug = true;
if (!$debug) error_reporting(E_NONE);





// Gets time zone information from a webservice
function timezone_info($lat, $long) {
	$url = "http://www.earthtools.org/timezone-1.1/" . $long . "/" . $lat;
	$dom = new DOMDocument('1.0', 'iso-8859-1');
	$dom->load($url);

	$utc_offset = $dom->getElementsByTagName('offset')->item(0)->nodeValue;
	$dst_info = $dom->getElementsByTagName('dst')->item(0)->nodeValue;
	
	if ($dst_info == "True") {
		// Make it negative, for compatibility with the javascript timezone format
		return (-1) * ((int)$utc_offset + 1);
	} elseif ($dst_info == "False") {
		// Make it negative, for compatibility with the javascript timezone format
		return (-1) * (int)$utc_offset;
	} else {
		throw new Exception("DST value from webservice was either unknown, or webservice unavailable.");
	}
}

// Formats separate parameters to a valid datetime string (using ISO 8601 format)
function format_datetime($when_year, $when_month, $when_day, $when_hour, $when_minute, $when_second, $when_offset) {
	// For values under 10, prepend them with zeroes to make sure the string has length 2
	if($when_month < 10)   { $when_month   = "0" . (string)$when_month; }
	if($when_day < 10)     { $when_day     = "0" . (string)$when_day; }
	if($when_hour_UT < 10) { $when_hour_UT = "0" . (string)$when_hour_UT; }
	if($when_minute < 10)  { $when_minute  = "0" . (string)$when_minute; }
	if($when_second < 10)  { $when_second  = "0" . (string)$when_second; }
	
	// First, format the date
	$when_date = (string)$when_year . "-" . (string)$when_month . "-" . (string)$when_day;
	
	// Calculate hour with respect to UTC/DST
	$when_hour_UT = ($when_hour + $when_offset);
	if ($when_hour_UT < 0) {
		$when_hour_UT += 24;
		// Also, the observation took place a day earlier than the user specified!
		// Let php handle this - it's more reliable (end of the month, year, leap year,...)
		$tomorrow = new DateTime($when_date);
		$tomorrow->modify("-1 day");
		$when_date = $tomorrow->format("Y-m-d");
	}
	elseif ($when_hour_UT > 23) {
		// Take it modulo 24 to be sure it lies in the range 0..23
		$when_hour_UT = $when_hour_UT % 24;
		// Also, the observation took place a day later than the user specified!
		// Let php handle this - it's more reliable (end of the month, year, leap year,...)
		$tomorrow = new DateTime($when_date);
		$tomorrow->modify("+1 day");
		$when_date = $tomorrow->format("Y-m-d");
	}
	
	// Format the time
	$when_time = (string)$when_hour_UT . ":" .  (string)$when_minute . ":" .  (string)$when_second;
	
	// Return datetime
	$when_datetime = (string)$when_date . " " . (string)$when_time;
	return $when_datetime;
}


// Formats empty or unknown input as '' for insertion into database, and if input was given, surround it with quotes
function format_input($input) {
	if (($input == "") || ($input == "Unknown")) {
		$input = "NULL";
	}
	else {
		$input = "'" . $input . "'";
	}
	return $input;
}





// If someone tries to load the page without sending XML through the form, tell them to use that
if (!$_POST['xml']) {
	die ("Can't find any input. Are you using the <a href=\"form.php\">report form</a>?");
}

// Get the XML string and use it in a DOM Document
$xml_str = $_POST['xml'];
$dom = new DOMDocument('1.0', 'iso-8859-1');
$dom->loadXML($xml_str);

// Check if that worked
if (!$dom) {
	die ("Oops... Something went wrong when receiving the data.");
}

// Validate the XML-DOM with the obs.xsd schema
if (!$dom->schemaValidate('obs.xsd')) {
	die ("Oops... Something went wrong when parsing the data.");
}

// If validation passed, check the captcha
$recaptcha_challenge = $dom->getElementsByTagName('recaptcha_challenge_field')->item(0)->nodeValue;
$recaptcha_response = $dom->getElementsByTagName('recaptcha_response_field')->item(0)->nodeValue;
$recaptcha_resp = recaptcha_check_answer ($recaptcha_privatekey, $_SERVER["REMOTE_ADDR"], $recaptcha_challenge, $recaptcha_response);
if (!$recaptcha_resp->is_valid) {
	die ("Problem with the captcha: " . $recaptcha_resp->error);
}

// If that went well, get values from the XML
// When - don't use format_input() here, there is a seperate function for dates and times
$when_year = $dom->getElementsByTagName('when_year')->item(0)->nodeValue;
$when_month = $dom->getElementsByTagName('when_month')->item(0)->nodeValue;
$when_day = $dom->getElementsByTagName('when_day')->item(0)->nodeValue;
$when_hour = $dom->getElementsByTagName('when_hour')->item(0)->nodeValue;
$when_minute = $dom->getElementsByTagName('when_minute')->item(0)->nodeValue;
$when_second = $dom->getElementsByTagName('when_second')->item(0)->nodeValue;
$when_offset = $dom->getElementsByTagName('when_offset')->item(0)->nodeValue;
// Where
$where_latitude = $dom->getElementsByTagName('where_latitude')->item(0)->nodeValue;
$where_longitude = $dom->getElementsByTagName('where_longitude')->item(0)->nodeValue;
$where_location = format_input($dom->getElementsByTagName('where_location')->item(0)->nodeValue);
// Location
$loc_startdirection = format_input($dom->getElementsByTagName('loc_startdirection')->item(0)->nodeValue);
$loc_startheight = format_input($dom->getElementsByTagName('loc_startheight')->item(0)->nodeValue);
$loc_enddirection = format_input($dom->getElementsByTagName('loc_enddirection')->item(0)->nodeValue);
$loc_endheight = format_input($dom->getElementsByTagName('loc_endheight')->item(0)->nodeValue);
// What
$what_mag = format_input($dom->getElementsByTagName('what_mag')->item(0)->nodeValue);
$what_mag_est = format_input($dom->getElementsByTagName('what_mag_est')->item(0)->nodeValue);
$what_duration = format_input($dom->getElementsByTagName('what_duration')->item(0)->nodeValue);
$what_color = format_input($dom->getElementsByTagName('what_color')->item(0)->nodeValue);
$what_fragmentation = format_input($dom->getElementsByTagName('what_fragmentation')->item(0)->nodeValue);
$what_train = format_input($dom->getElementsByTagName('what_train')->item(0)->nodeValue);
$what_vel = format_input($dom->getElementsByTagName('what_vel')->item(0)->nodeValue);
$what_vel_est = format_input($dom->getElementsByTagName('what_vel_est')->item(0)->nodeValue);
$what_sound = format_input($dom->getElementsByTagName('what_sound')->item(0)->nodeValue);
$what_interval = format_input($dom->getElementsByTagName('what_interval')->item(0)->nodeValue);
$what_general = format_input($dom->getElementsByTagName('what_general')->item(0)->nodeValue);
// Other remarks
$fireball_other = format_input($dom->getElementsByTagName('fireball_other')->item(0)->nodeValue);
// Who
$who_firstname = format_input($dom->getElementsByTagName('who_firstname')->item(0)->nodeValue);
$who_lastname = format_input($dom->getElementsByTagName('who_lastname')->item(0)->nodeValue);
$who_country = format_input($dom->getElementsByTagName('who_country')->item(0)->nodeValue);
$who_email = format_input($dom->getElementsByTagName('who_email')->item(0)->nodeValue);
$who_phone = format_input($dom->getElementsByTagName('who_phone')->item(0)->nodeValue);
// Referrer
$referrer = format_input($dom->getElementsByTagName('code')->item(0)->nodeValue);
$language = format_input($dom->getElementsByTagName('language')->item(0)->nodeValue);

$calc_offset = "";
// Try and get the offset to UTC and UTC from a webservice
try {
	$calc_offset = timezone_info($where_latitude, $where_longitude);
} catch (Exception $e) {
	// This may not work, use the lesser reliable value from the user then
	$calc_offset = $when_offset;
}

// Format date and time
$when_datetime = format_datetime($when_year, $when_month, $when_day, $when_hour, $when_minute, $when_second, $when_offset);

// Generate a code for this observation
$obs_confirm_check = md5(uniqid(rand(), true));
$obs_confirm_check_formatted = format_input($obs_confirm_check);

// Connect to the database
$connect = pg_connect($pgsql_connect_str) or die ("Could not connect to the database.");

// Insert values
$insert_query = "INSERT INTO observations(when_datetime, where_latitude, where_longitude, where_location, "
	. "loc_startdirection, loc_startheight, loc_enddirection, loc_endheight, what_mag, what_mag_est, what_duration, "
	. "what_color, what_fragmentation, what_train, what_vel, what_vel_est, what_sound, what_interval, what_general, "
	. "fireball_other, who_firstname, who_lastname, who_country, who_email, who_phone, referrer, confirm_check) "
	. "VALUES (TIMESTAMP '" . $when_datetime . "', " . $where_latitude . ", " . $where_longitude . ", " . $where_location . ", " 
	. $loc_startdirection . ", " . $loc_startheight . ", " . $loc_enddirection . ", " . $loc_endheight . ", " 
	. $what_mag . ", " . $what_mag_est . ", " . $what_duration . ", " . $what_color . ", " . $what_fragmentation . ", " 
	. $what_train . ", " . $what_vel . ", " . $what_vel_est . ", " . $what_sound . ", " . $what_interval . ", " 
	. $what_general . ", " . $fireball_other . ", " . $who_firstname . ", " . $who_lastname . ", " . $who_country . ", " 
	. $who_email . ", " . $who_phone . ", " . $referrer . ", " . $obs_confirm_check_formatted . ")";
$result = pg_query($connect, $insert_query) or die ("Error inserting into database, using query: " . $insert_query . pg_last_error($connect));
// Get last observation's ID - currval only works after an insert
$get_id_query = "SELECT currval('observations_id_seq')";
$obs_id_result = pg_query($connect, $get_id_query) or die ("Error getting id from database, using query: " . $get_id_query . pg_last_error($connect));
$obs_id_row = pg_fetch_row($obs_id_result);
$obs_id = $obs_id_row[0];
pg_close($connect);

echo "Your observation was succesfully processed! We will send you an e-mail to confirm it.";

$headers = "From: Virtual Fireball Observatory <smena@umdb.urania.be>" . "\r\n" 
		 . "Reply-To: nassia@gmail.com" . "\r\n"
		 . "X-Mailer: PHP/" . phpversion() . "\r\n";

// Generate the body for the e-mail
function get_email_body_text($firstname, $lastname, $obs_id, $obs_confirm_check) {
	// Each line should be separated with a LF (\n). Lines should not be larger than 70 characters.
	$msg = "Hi " . $firstname . " " . $lastname . ",\n\n"
		 . "Thanks for submitting your fireball observation to the VFO!\n\n"
		 . "Your observation with code '". $obs_id . "' will be submitted "
		 . "to our database once you confirm by clicking this link:\n"
		 . "http://umdb.urania.be/smena/confirm.php?obs_id=" 
		 . $obs_id . "&obs_check=" . $obs_confirm_check . "\n"
		 . "When the above link isn't clickable, you can manually "
		 . "enter your observation code (together with you "
		 . "confirmation code '" . $obs_confirm_check . "') in the form on "
		 . "the confirmation page at our website.\n\n"
		 . "Greetings and have a nice day,\nThe VFO mailing pigeon";
	return $msg;
}

$to = trim($who_email, "'");
$firstname = trim($who_firstname, "'");
$lastname = trim($who_lastname, "'");
$subject = "Your observation";
$message = get_email_body_text($firstname, $lastname, $obs_id, $obs_confirm_check);

mail ($to, $subject, $message, $headers) or die ("Problem sending e-mail.");

// Save to a file
$filename = "obs_xml_files/obs_" . $obs_id . ".xml";

// Protect privacy: do not write names, e-mails and phone numbers to file
$vfo_doc = $dom->documentElement;
$vfo_doc->getElementsByTagName('obs')->item(0)->removeChild($vfo_doc->getElementsByTagName('obs')->item(0)->getElementsByTagName('who')->item(0));
// Remove recaptcha info too
$vfo_doc->removeChild($vfo_doc->getElementsByTagName('recaptcha')->item(0));

$dom->save($filename);

?>