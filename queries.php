<?php
include("header.inc.php");
include("navigation.inc.php");

require_once("DBQuerier.inc.php");

echo "<h1>Query the database</h1>";

try {
	$dbquerier = new DBQuerier;
	echo "<p>Ask about events in our database! Currently, there are " . $dbquerier->getCountConfirmedObs() . " confirmed observations.<p>";
	?>
	
	<form name="query_form" action="queries.php" method="GET">
	<div id="query_form">
		<h2>View an observation</h2>
		<fieldset>
			<label for="obs_by_id">by ID: </label>
				<?php $dbquerier->getObsIdsDropdown(); ?>
				<br/>
			<label for="obs_by_name">by Name: </label>
				<input type="text" maxlength="50" class="report_form_text" id="obs_by_name" name="obs_by_name" size="20" value="" />
				<br/>
		</fieldset>
		<input type="submit" id="query_submit_button" value="Query" />
	</div>
	</form>
	
	<p>&nbsp;</p>
		
	<?php
	
	if ($_GET['obs_by_id']) {
		$id = $_GET['obs_by_id'];
		
		echo "<h1>Result</h1>";
		if ($dbquerier->existsInDB($id)) {
			echo "<p>You are now viewing observation $id. <a href=\"http://umdb.urania.be/smena/obs_xml_files/obs_$id.xml\">Download as XML.</a></p>";
			echo "<div id=\"query_result\">\n";
			$dbquerier->getObsById($id);
			echo "</div>\n";
		} else {
			echo "<p>This ID does not exist in the database.</p>";
		}
	}
	if ($_GET['obs_by_name']) {
		$name = $_GET['obs_by_name'];
		
		echo "<h1>Result</h1>";
		if ($id = $dbquerier->searchNameInDB($name)) {
			if ($id == -1) {
				echo "<p>This ID does not exist in the database.</p>";
			} else {
				echo "<p>You are now viewing observation $id. <a href=\"http://umdb.urania.be/smena/obs_xml_files/obs_$id.xml\">Download as XML.</a></p>";
				echo "<div id=\"query_result\">\n";
				$dbquerier->getObsById($id);
				echo "</div>\n";
			}
		} else {
			echo "<p>This name does not exist in the database.</p>";
		}
	}
	
} catch (Exception $e) {
	echo $e->getMessage();
}

include("footer.inc.php");
?>