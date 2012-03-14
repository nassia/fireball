<?php

require_once("DBQuerier.inc.php");

// If someone tries to load the page without sending XML through the form, tell them to use that
if (!$_POST['xml']) {
	die ("Can't find any input. Are you using the <a href=\"queries.php\">queries form</a>?");
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

// If that went well, get values from the XML
$id = $dom->getElementsByTagName('obs_by_id')->item(0)->nodeValue;

$dbquerier = new DBQuerier;


$dbquerier->getObsById($id);

?>