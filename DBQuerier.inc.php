<?php

// Object for querying the database

class DBQuerier {
	private $conn;
	
	
	function __construct(){
		$this->conn = pg_connect("host=localhost port=5433 dbname=fireball user=smena password=fireball42");
		if (!$this->conn) {
			throw new Exception("Could not connect to database");
		}
	}
	
	
	function __destruct() {
		pg_close($this->conn);
	}
	
	
	function getCountConfirmedObs() {
		$query = "SELECT count(*) FROM confirmed_observations";
		$result = pg_query($this->conn, $query);
		if (!$result) { throw new Exception("Error querying database, using query: " . $query . pg_last_error($this->conn)); }
		$row = pg_fetch_row($result);
		$row0 = $row[0];
		return trim($row0);
	}
	
	
	function existsInDB($check_id) {
		$query = "SELECT id FROM confirmed_observations ORDER BY id ASC";
		$result = pg_query($this->conn, $query);
		if (!$result) { throw new Exception("Error querying database, using query: " . $query . pg_last_error($this->conn)); }
		while ($row = pg_fetch_assoc($result)) {
			if ($check_id == $row['id']) {
				return true;
			}
		}
		return false;
	}
	
	
	function getObsIds() {
		$ids = array();
		$query = "SELECT id FROM confirmed_observations ORDER BY id ASC";
		$result = pg_query($this->conn, $query);
		if (!$result) { throw new Exception("Error querying database, using query: " . $query . pg_last_error($this->conn)); }
		return pg_fetch_assoc($result);
	}
	
	
	function getObsIdsDropdown() {
		$ids = array();
		$query = "SELECT id FROM confirmed_observations ORDER BY id ASC";
		$result = pg_query($this->conn, $query);
		if (!$result) { throw new Exception("Error querying database, using query: " . $query . pg_last_error($this->conn)); }
		echo "<select name=\"obs_by_id\" id=\"obs_by_id\">\n";
		echo "<option value=\"\"></option>\n";
		while ($row = pg_fetch_assoc($result)) {
			echo "<option value=\"" . $row['id'] . "\">" . $row['id'] . "</option>\n";
		}
		echo "</select>\n\n";
	}
	
	
	function getObsById($id) {
		$query = "SELECT * FROM confirmed_observations WHERE id=" . $id;
		$result = pg_query($this->conn, $query);
		if (!$result) { throw new Exception("Error querying database, using query: " . $query . pg_last_error($this->conn)); }
		while ($row = pg_fetch_assoc($result)) {
			$this->formatWhenInfo($row);
			$this->formatWhereInfo($row);
			$this->formatLocationInfo($row);
			$this->formatWhatInfoTable($row);
			$this->formatOtherInfo($row);
			$this->formatWhoInfo($id);
			$this->getSimilarObs($id);
		}		
	}
	
	
	function formatObsTable($title, $value) {
		if ($value != NULL) {
			echo "<tr><th style=\"text-align: left;\">" . $title . "</th><td>" . $value . "</td></tr>\n";
		}
	}
	
	
	function formatObsTable2($title, $value) {
		if ($value != NULL) {
			return ("<tr><th style=\"text-align: left;\">" . $title . "</th><td>" . $value . "</td></tr>\n");
		}
	}
	
	
	function formatWhenInfo($row) {
		echo "<h2>When was the fireball seen?</h2>";
		$date = date_parse($row['when_datetime']);
		$monthNames = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
		$month = $monthNames[$date['month']-1];
		echo "<p>The event reported was seen on " . $month . " " . $date['day'] . ", " . $date['year'] . " at " . $date['hour'] . "h" . $date['minute'] . "m" . " UTC.</p>";
	}
	
	
	function formatWhereInfo($row) {
		echo "<h2>Where was the fireball seen?</h2>";
		?>
		<div id="map" style="width: 700px; height: 400px"></div>
		
		<script type="text/javascript">
		//<![CDATA[
			var map = new GMap2(document.getElementById("map"));
			map.setCenter(new GLatLng(<?php echo $row['where_longitude'] ?>, <?php echo $row['where_latitude'] ?>), 7);
			map.setMapType(G_HYBRID_MAP);
			map.addControl(new GSmallMapControl());
			map.addControl(new GMapTypeControl());
			map.addControl(new GOverviewMapControl());
			var marker = new GMarker(map.getCenter());
			map.openInfoWindowHtml(map.getCenter(), <?php echo "'The fireball was observed at " . $row['where_location'] . "<br/>Longitude: " . $row['where_longitude'] . "<br/>Latitude: " . $row['where_latitude'] . "'" ?>); 
			map.addOverlay(marker);
		//]]>
		</script>
		<?php
	}
	
	
	function formatLocationInfo($row) {
		echo "<h2>Where in the sky was the fireball seen?</h2>";
		if (($row['loc_startdirection'] == NULL) && ($row['loc_startheight'] == NULL) && ($row['loc_enddirection'] == NULL) && ($row['loc_endheight'] == NULL)) {
			echo "No information about the location of the fireball was entered.";
		} else {
			echo "<table>\n";
			$this->formatObsTable("Starting point", ($row['loc_startdirection'] . " at " . $row['loc_startheight'] . "&deg;"));
			$this->formatObsTable("Ending point", ($row['loc_enddirection'] . " at " . $row['loc_endheight'] . "&deg;"));
			echo "</table>\n\n";
		}
	}
	
	
	function formatWhatInfoTable($row) {
		echo "<h2>What did the fireball look like?</h2>";
		if (($row['what_general'] == NULL) && ($row['what_mag'] == NULL) && ($row['what_mag_est'] == NULL) 
				&& ($row['what_duration'] == NULL) && ($row['what_color'] == NULL) && ($row['what_fragmentation'] == NULL)
				&& ($row['what_train'] == NULL) &&  ($row['what_vel'] == NULL) &&  ($row['what_vel_est'] == NULL)
				&& ($row['what_sound'] == NULL) && ($row['what_interval'] == NULL)) {
					echo "No extra information about the fireball was entered.";
		} else {
			echo "<table>\n";
			$this->formatObsTable("General remarks", $row['what_general']);
			$this->formatObsTable("Magnitude", $row['what_mag']);
			$this->formatObsTable("Estimated magnitude", $row['what_mag_est']);
			$this->formatObsTable("Duration of the event", $row['what_duration']);
			$this->formatObsTable("Color", $row['what_color']);
			$this->formatObsTable("Fragmentation", $row['what_fragmentation']);
			$this->formatObsTable("Persistent train", $row['what_train']);
			$this->formatObsTable("Velocity", $row['what_vel']);
			$this->formatObsTable("Estimated velocity", $row['what_vel_est']);
			$this->formatObsTable("Sound", $row['what_sound']);
			$this->formatObsTable("Interval", $row['what_interval']);
			echo "</table>\n\n";
		}
	}
	
	
	function formatOtherInfo($row) {
		echo "<h2>Other remarks</h2>";
		if ($row['fireball_other'] == NULL) {
			echo "No other remarks were entered.";
		} else {
			echo $row['fireball_other'];
		}
	}
	
	
	function formatWhoInfo($id) {
		echo "<h2>Contact the observer</h2>";
		echo "<p><a onclick=\"toggle_visibility('mail_div');\" style=\"cursor: pointer; font-weight:bold; text-decoration: underline;\">Click here to send an e-mail to the observer.</a></p>";
		echo "<div id=\"mail_div\" style=\"display:none\">";
		$this->showMailFormById($id);
		echo "</div>";
	}
	
	
	function showMailFormById($id) {
		$query = "SELECT who_firstname, who_lastname, who_email FROM confirmed_observations WHERE id=" . $id;
		$result = pg_query($this->conn, $query);
		if (!$result) { throw new Exception("Error querying database, using query: " . $query . pg_last_error($this->conn)); }
		while ($row = pg_fetch_assoc($result)) {
			$fname = $row['who_firstname'];
			$lname = $row['who_lastname'];
			$email = $row['who_email'];
			$this->showMailForm($fname, $lname, $email);
		}
	}
	
	
	function showMailForm($fname, $lname, $email) {
		?>
		<form name="report_form">
		<div id="report_form">
			<fieldset>
				<label class="required" for="name">Name:</label>
					<input type="text" maxlength="50" class="report_form_text" id="name" size="50" value="" /><br/>
				<label class="required" for="email">E-mail address:</label>
					<input type="text" maxlength="50" class="report_form_text" id="email" size="50" value="" /><br/>
				<label class="required" for="message">Message:</label>
					<textarea class="report_form_text" id="message" rows="5" cols="75"></textarea><br/>
			</fieldset>
			<div id="recaptcha_div"></div>
			<div id="errors"> </div>
			<div id="result"> </div>
			<input type="button" id="submit_button" value="Send mail" onclick="submitMailForm()" />
		</div>
		</form>
		
		<script type="text/javascript">
			
			showRecaptcha('recaptcha_div', 'submit_button');
			
			function submitMailForm() {
				var errors = false;
				makeAllFieldsNormal();
				document.getElementById('errors').innerHTML = "";
				
				if (isEmptyField('name')) { errors = true; reportError('name', "Please fill in your name"); }
				if (isEmptyField('email')) { errors = true; reportError('email', "Please fill in your e-mail address"); }
				if (!isEmailAddress('email')) { errors = true; reportError('email', "Please fill in a valid e-mail address"); }
				if (isEmptyField('message')) { errors = true; reportError('message', "Please write a message"); }
				
				if (!errors) {										
					var sname = document.getElementById('name').value;
					var semail = document.getElementById('email').value;
					var message = document.getElementById('message').value;
					var recaptcha = recaptchaXML();
					
					var xmlstr = '<vfo>'+recaptcha+'<obsmailer><receiver_name>'+<?php echo ("'" . $fname . " " . $lname . "'"); ?>+'</receiver_name><receiver_email>'+<?php echo ("'" . $email . "'"); ?>+'</receiver_email><sender_name>'+sname+'</sender_name><sender_email>'+semail+'</sender_email><message>'+message+'</message></obsmailer></vfo>';
										
					new Ajax.Updater( 'result', 'mail_processor.php', { method: 'post', parameters: { xml: xmlstr } } );
					document.getElementById('submit_button').disabled = true;
					document.getElementById('submit_button').value = "Sent mail!";
				}
			}
		</script>
		<?php
	}
	
	
	function dateSQLtoPHP($sql_date) {
		$year =   pg_fetch_row(pg_query($this->conn, "SELECT EXTRACT(year FROM TIMESTAMP '" . $sql_date . "');"), 0);
		$month =  pg_fetch_row(pg_query($this->conn, "SELECT EXTRACT(month FROM TIMESTAMP '" . $sql_date . "');"), 0);
		$day =    pg_fetch_row(pg_query($this->conn, "SELECT EXTRACT(day FROM TIMESTAMP '" . $sql_date . "');"), 0);
		$hour =   pg_fetch_row(pg_query($this->conn, "SELECT EXTRACT(hour FROM TIMESTAMP '" . $sql_date . "');"), 0);
		$minute = pg_fetch_row(pg_query($this->conn, "SELECT EXTRACT(minute FROM TIMESTAMP '" . $sql_date . "');"), 0);
		$php_date = new DateTime();
		$php_date->setDate($year[0], $month[0], $day[0]);
		$php_date->setTime($hour[0], $minute[0]);
		return $php_date;
	}
	
	
	function getSimilarObs($id) {
		$similar_obs = array();
				
		// First get the info on the reference observation
		$query_ref_obs = "SELECT * FROM confirmed_observations WHERE id=" . $id;
		$ref_obs = pg_query($this->conn, $query_ref_obs);
		if (!$ref_obs) { throw new Exception("Error querying database, using query: " . $query_ref_obs . pg_last_error($this->conn)); }
		// Get all other observations
		$query_other_obs = "SELECT * FROM observations WHERE NOT(id=" . $id . ")";
		$other_obs = pg_query($this->conn, $query_other_obs);
		if (!$other_obs) { throw new Exception("Error querying database, using query: " . $query_other_obs . pg_last_error($this->conn)); }
		
		// Get interesting values from the reference observation
		while ($ref_row = pg_fetch_assoc($ref_obs)) {
			// Get the time
			$ref_date = $this->dateSQLtoPHP($ref_row['when_datetime']);
			
			// Bounds - string representations
			$ref_date_str = strtotime($ref_date->format("Y-m-d H:i"));
			$ref_date_lowerbound1_str = $ref_date_str - 5*60;
			$ref_date_upperbound1_str = $ref_date_str + 5*60;
			$ref_date_lowerbound2_str = $ref_date_str - 15*60;
			$ref_date_upperbound2_str = $ref_date_str + 15*60;
			$ref_date_lowerbound3_str = $ref_date_str - 30*60;
			$ref_date_upperbound3_str = $ref_date_str + 30*60;
			$ref_date_lowerbound4_str = $ref_date_str - 60*60;
			$ref_date_upperbound4_str = $ref_date_str + 60*60;
			
			$ref_latitude = $ref_row['where_latitude'];
			$ref_longitude = $ref_row['where_longitude'];
			$ref_latitude_lowerbound = abs($ref_latitude) - (500/90 * cos(deg2rad($ref_latitude)));
			$ref_latitude_upperbound = abs($ref_latitude) + (500/90 * cos(deg2rad($ref_latitude)));
			$ref_longitude_lowerbound = abs($ref_longitude) - (500/90 * sin(deg2rad($ref_longitude)));
			$ref_longitude_upperbound = abs($ref_longitude) + (500/90 * sin(deg2rad($ref_longitude)));
			
			// Get observations to compare the reference to
			while ($compare_row = pg_fetch_assoc($other_obs)) {
				// Keep a score for this observation
				$score = 0;
				// ID for this observation
				$compare_id = $compare_row['id'];
				
				// --- Check by Date/Time ---
				// Check if the year-month-day lies in the same 24-hour range
				$compare_date = $this->dateSQLtoPHP($compare_row['when_datetime']);
				$compare_date_str = strtotime($compare_date->format("Y-m-d H:i"));
								
				// Check
				if (abs($compare_date_str - $ref_date_str) <= 24*60*60) {
					
					if ( (($ref_date_upperbound1_str - $compare_date_str) >= 0) && (($compare_date_str - $ref_date_lowerbound1_str) >= 0) ) {
						//echo $compare_id . ": " . $ref_date->format("Y-m-d H:i") . " vs " . $compare_date->format("Y-m-d H:i") . " - Score 10<br>";
						$score += 10;
					}
					else if ( (($ref_date_upperbound2_str - $compare_date_str) >= 0) && (($compare_date_str - $ref_date_lowerbound2_str) >= 0) ) {
						//echo $compare_id . ": " . $ref_date->format("Y-m-d H:i") . " vs " . $compare_date->format("Y-m-d H:i") . " - Score 7<br>";
						$score += 7;
					}
					else if ( (($ref_date_upperbound3_str - $compare_date_str) >= 0) && (($compare_date_str - $ref_date_lowerbound3_str) >= 0) ) {
						//echo $compare_id . ": " . $ref_date->format("Y-m-d H:i") . " vs " . $compare_date->format("Y-m-d H:i") . " - Score 5<br>";
						$score += 5;
					}
					else if ( (($ref_date_upperbound4_str - $compare_date_str) >= 0) && (($compare_date_str - $ref_date_lowerbound4_str) >= 0) ) {
						//echo $compare_id . ": " . $ref_date->format("Y-m-d H:i") . " vs " . $compare_date->format("Y-m-d H:i") . " - Score 4<br>";
						$score += 4;
					}
					else {
						//echo $compare_id . ": " . $ref_date->format("Y-m-d H:i") . " vs " . $compare_date->format("Y-m-d H:i") . " - Score 2<br>";
						$score += 2;
					}
				}
				
				// --- Check by Coordinates ---
				$compare_latitude = abs($compare_row['where_latitude']);
				$compare_longitude = abs($compare_row['where_longitude']);
				
				//echo "lat: " . $ref_latitude . " - " . $ref_latitude_lowerbound . " - " . $compare_latitude . " - " . $ref_latitude_upperbound . "<br>";
				//echo "long: " . $ref_longitude . " - " . $ref_longitude_lowerbound . " - " . $compare_longitude . " - " . $ref_longitude_upperbound . "<br><br>";
				
				if (   (($ref_latitude_upperbound - $compare_latitude) >= 0) 
				    && (($compare_latitude - $ref_latitude_lowerbound) >= 0)
				    && (($compare_longitude - $ref_longitude_lowerbound) >= 0)
					&& (($compare_longitude - $ref_longitude_lowerbound) >= 0) ) {
						$score += 8;
				}
				
				// Check score, if it's 5 or larger, it's the same event
				if ($score >= 12) {
					// Keep associative array with id as an index, score as value
					$similar_obs[$compare_id] = $score;
				}
			}
		}
		// We now have all IDs + scores of similar observations
		echo "<h2>Similar Observations</h2>";
		if (count($similar_obs) == 0) {
			echo "<p>No similar observations were found.</p>\n";
		} else {
			echo "<p>Similar observations:<p>\n";
			$max_score = 18;
			arsort($similar_obs);
			foreach ($similar_obs as $id => $score) {
				$matching_percentage = round((($score / $max_score) * 100), 2);
		    	echo "<a href=\"http://umdb.urania.be/smena/queries.php?obs_by_id=$id\">Observation $id</a> (" . $matching_percentage . "%)<br/>\n";
			}
		}
	}
	
	
	function searchNameInDB($name) {
		$query = "SELECT id FROM observations WHERE who_lastname ~* '" . $name . "' AND confirmed=true";
		$result = pg_query($this->conn, $query);
		if (!$result) { throw new Exception("Error querying database, using query: " . $query . pg_last_error($this->conn)); }
		while ($row = pg_fetch_assoc($result)) {
			$id = $row['id'];
			return $id;
		}
		return -1;
	}
	
}

?>