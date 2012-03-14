<?php
// Include lay-out
include("header.inc.php");
include("navigation.inc.php");

// Include parameters for database access
require_once('pgsql.inc.php');

function display_form() {
	echo "<form action=\"confirm.php\" method=\"get\">\n";
	echo "<div id=\"confirmation_form\">\n";
	echo "<fieldset><legend>Manual ID confirmation</legend>\n";
	echo "<p>Please specify a valid observation ID. It should have been sent to you in an e-mail after submitting the form.</p>\n";
	echo "<label for=\"obs_id\">Observation ID:</label><input type=\"text\" class=\"report_form_text\" name=\"obs_id\" id=\"obs_id\" size=\"10\" value=\"\" /><br />\n";
	echo "<label for=\"obs_check\">Confirmation code:</label><input type=\"text\" class=\"report_form_text\" name=\"obs_check\" id=\"obs_check\" size=\"32\" value=\"\" /><br />\n";
	echo "<input type=\"submit\" value=\"Confirm my observation!\" />\n";
	echo "</fieldset>\n";
	echo "</div>\n";
	echo "</form>\n";
}

echo "<h1>Confirm your observation</h1>";

if (!$_GET) {
	display_form();
}
else {
	$obs_id = $_GET['obs_id'];
	$obs_check = $_GET['obs_check'];

	// Connect to the database
	$connect = pg_connect($pgsql_connect_str) or die ("Could not connect to the database.");
	$query = "UPDATE observations SET confirmed=true WHERE (id=" . $obs_id . " AND confirm_check='" . $obs_check . "')";
	$result = pg_query($connect, $query) or die ("Error confirming observation, using query: " . $query . pg_last_error($connect));
	if ($result) echo "Your observation was added to our database!";
	pg_close($connect);
}

include("footer.inc.php");
?>