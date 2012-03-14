<?php

// Includes
require_once('recaptcha.inc.php'); // Necessary for using Recaptcha

// If someone tries to load the page without sending XML through the form, tell them to use that
if (!$_POST['xml']) {
	die ("Can't find any input. Are you using the <a href=\"queries.php\">mail form</a>?");
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
//if (!$dom->schemaValidate('obs.xsd')) {
//	die ("Oops... Something went wrong when parsing the data.");
//}

// If validation passed, check the captcha
$recaptcha_challenge = $dom->getElementsByTagName('recaptcha_challenge_field')->item(0)->nodeValue;
$recaptcha_response = $dom->getElementsByTagName('recaptcha_response_field')->item(0)->nodeValue;
$recaptcha_resp = recaptcha_check_answer ($recaptcha_privatekey, $_SERVER["REMOTE_ADDR"], $recaptcha_challenge, $recaptcha_response);
if (!$recaptcha_resp->is_valid) {
	die ("Problem with the captcha: " . $recaptcha_resp->error);
}

// If that went well, get values from the XML
$rname = $dom->getElementsByTagName('receiver_name')->item(0)->nodeValue;
$remail = $dom->getElementsByTagName('receiver_email')->item(0)->nodeValue;
$sname = $dom->getElementsByTagName('sender_name')->item(0)->nodeValue;
$semail = $dom->getElementsByTagName('sender_email')->item(0)->nodeValue;
$message = $dom->getElementsByTagName('message')->item(0)->nodeValue;


$headers = "From: Virtual Fireball Observatory <smena@umdb.urania.be>" . "\r\n" 
		 . "Reply-To: nassia@gmail.com" . "\r\n"
		 . "X-Mailer: PHP/" . phpversion() . "\r\n";

$subject = "Someone reacted to your observation!";


$mailbody = "Hi, " . $rname . "!\n"
		  . "Someone named " . $sname . " (" . $semail . ")" . " left a comment on your observation: \n\n"
		  . $message . "\n\n"
		  . "Greetings,\n The VFO mailing pigeon";

mail ($remail, $subject, $mailbody, $headers) or die ("Problem sending e-mail.");

echo "Sent mail!";


?>