<?php
$usemap = true;
include("header.inc.php");
include("navigation.inc.php");
?>


<script type="text/javascript" src="wz_tooltip.js"></script>
<script type="text/javascript" src="form.js"></script>


<h1>Fireball Report Form</h1>
<p>Did you see a fireball? You can report your observation to scientists using this online form!<br/>
	Only fields marked with a red star are required, but please fill out as many fields as you can to make your observation more valuable.</p>


<form name="report_form">
	<div id="report_form">		
		
		
		<!-- WHEN FIELDSET -->
		<div id="report_form_fieldset"><a name="when" />
		<fieldset><legend><b>When</b> did you see the fireball?</legend>
			<p>We collect fireball reports from all over the world. To be able to link certain events together, we store the observations in Coordinated Universal Time (UTC), commonly referred to as GMT. To make things a little easier for you, we ask you to give the <b>local</b> date and time when you saw the fireball. Please be as accurate as possible.</p>
			
			<label class="required">Local Date:</label>
				<select name="year" id="when_year">
					<script type="text/javascript">
					//<![CDATA[
							// Get current year
							var currentTime = new Date();
							var currentYear = currentTime.getFullYear();
							// Loop through all years from 1995 to now
							var year = 0;
							for (year = 1995; year < currentYear; year++) {
								document.write("<option value=\"" + year + "\">" + year + "</option>");
							}
							// Select this year by default
							document.write("<option value=\"" + currentYear + "\" selected=\"selected\">" + currentYear + "</option>");
					//]]>
					</script>
				</select>
				<select name="month" id="when_month">
					<script type="text/javascript">
					//<![CDATA[
							// Get current month
							var currentTime = new Date();
							var currentMonth = currentTime.getMonth() + 1;
							var monthNames = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
							// Loop through all months, select this month by default
							var monthNr = 1;
							for(monthNr = 1; monthNr <= 12; monthNr++) {
								var str = "<option value=\"" + monthNr + "\"";
								if (currentMonth == monthNr) { str = str + (" selected=\"selected\"") };
								str = str + (">" + monthNames[monthNr-1] + "</option>");
								document.write(str);
							}
					//]]>
					</script>
				</select>
				<select name="day" id="when_day">
					<script type="text/javascript">
					//<![CDATA[
							// Get current day
							var currentTime = new Date();
							var currentDay = currentTime.getDate();
							var dayNr = 1;
							for(dayNr = 1; dayNr <= 31; dayNr++) {
								var str = "<option value=\"" + dayNr + "\"";
								if (currentDay == dayNr) { str = str + (" selected=\"selected\"") };
								str = str + (">" + dayNr + "</option>");
								document.write(str);
							}
					//]]>
					</script>
				</select><br/>
			
			<label class="required">Local Time:</label>
				<input type="text" maxlength="2" class="report_form_text" name="hour"   id="when_hour"   size="2" value="" />h 
				<input type="text" maxlength="2" class="report_form_text" name="minute" id="when_minute" size="2" value="" />m 
				<input type="text" maxlength="2" class="report_form_text" name="second" id="when_second" size="2" value="" />s<br/>
				<small>Please use 24 hour local time format.</small>
			
			<p>The web page will automatically try to find out the difference between your local time and UTC, and will take into account Daylight Savings Time (DST). A negative value means you're ahead of UTC (e.g. in Asia), a positive value means you're behind of UTC (e.g. in the Americas).  If possible, please verify this value is correct. Also, if you saw the fireball whilst on vacation, enter time and timezone of that location!</p>
			<label class="required">Offset to UTC:</label>
				<input type="text" maxlength="3" class="report_form_text" name="offset" id="when_offset" size="3" value="" />h
					<script type="text/javascript">
					//<![CDATA[
						// Calculate difference to UTC, taking into account DST
						var currentTime = new Date();
						var offsetInHours = currentTime.getTimezoneOffset()/60;
						document.getElementById("when_offset").value = offsetInHours;
					//]]>
					</script>
				<br/>
		</fieldset>
		</div>
		
		
		<!-- WHERE FIELDSET -->
		<div id="report_form_fieldset"><a name="where" />
		<fieldset><legend><b>Where</b> were you when you saw the fireball?</legend>
			<p>We need to know as accurately as possible where you were when you saw the fireball. Just zoom in on the map and click on the right location. Please zoom in as far as possible, as it makes the location a lot more accurate.</p>
			<p>You can zoom in using the [+] and [-] buttons on the left of the map. To move to the right location, just drag the map or use the arrow buttons on the top left.</p>
			
			<label for="where_search">Search:</label>
				<input type="text" id="where_search" size="50" />
				<input type="button" value="Go!" onclick="searchAddress(); return false" />
			
			<div id="report_form_map" style="width: 700px; height: 400px"></div>
			
			<p><b>Use the interactive map above to help you fill in the fields below</b></p>
			<script type="text/javascript">
			//<![CDATA[
				// Make a new Google Map
				var map = new GMap2(document.getElementById("report_form_map"));
				map.setCenter(new GLatLng(34, 0), 1);
				map.setMapType(G_HYBRID_MAP);
				map.addControl(new GSmallMapControl());
				map.addControl(new GMapTypeControl());
				map.addControl(new GOverviewMapControl());

				var geocoder = new GClientGeocoder();
				var markerOn = 0;
				
				GEvent.addListener(map, 'click', 
					function(overlay, point) {
						if (overlay) {
							markerOn = 0;
							document.getElementById("where_latitude").value = "";
							document.getElementById("where_longitude").value = "";
							map.removeOverlay(overlay);
						} else if (point) {
							if (markerOn == 0) {
								map.addOverlay(new GMarker(point));
								document.getElementById("where_latitude").value = point.x;
								document.getElementById("where_longitude").value = point.y;
								markerOn = 1;
						}
					}
				})
			;
			
			function searchAddress() {
				var address = document.getElementById("where_search").value;
				document.getElementById("where_location").value = address;
				if (geocoder) {
					geocoder.getLatLng(
					address,
					function(point) {
						if (!point) {
							alert(address + " not found");
						} else {
							map.setCenter(point, 15);
							document.getElementById("where_latitude").value = point.x;
							document.getElementById("where_longitude").value = point.y;
							var marker = new GMarker(point);
							map.addOverlay(marker);
							marker.openInfoWindowHtml("Click me to remove me and specify another location!");
							markerOn = 1;
						}
					}
					);
				}
			}
			
			//]]>
			</script>
			
			<label class="required" for="where_latitude">Latitude:</label>
				<input type="text" maxlength="20" class="report_form_text" name="latitude" id="where_latitude" size="20" value="" /> East (enter negative value for West)<br/>
			<label class="required" for="where_longitude">Longitude:</label>
				<input type="text" maxlength="20" class="report_form_text" name="longitude" id="where_longitude" size="20" value="" /> North (enter negative value for South)<br/>
			<label for="where_longitude">Location:</label>
				<input type="text" maxlength="50" class="report_form_text" name="location" id="where_location" size="50" value="" /><br/>
				<small>Use a "Town, Country" format. The town and country names are informal only. The coordinates above are used as the reference.</small>
		</fieldset>
		</div>
		
		
		<!-- LOC FIELDSET -->
		<div id="report_form_fieldset"><a name="loc" />
		<fieldset><legend><b>Location</b> of the fireball</legend>
			<p>Describe where in the sky the fireball started, and where you saw it end by cardinal direction, and the height above the horizon (use the diagram below for estimating degrees).</p>
			<div style="width: 670px;">
				
				<div style="float: left; margin-top: 10px;">
				<fieldset style=" margin-bottom: 25px;"><legend>Where did you <b>first</b> see the fireball?</legend>
					<label for="loc_startdirection">Direction:</label>
						<select name="startdirection" id="loc_startdirection">
							<option value="">Select the direction</option>
							<option value="0">North</option>
							<option value="45">Northeast</option>
							<option value="90">East</option>
							<option value="135">Southeast</option>
							<option value="180">South</option>
							<option value="225">Southwest</option>
							<option value="270">West</option>
							<option value="315">Northwest</option>
						</select><br/>
					<label for="loc_startheight">Height:</label>
						<select name="startheight" id="loc_startheight">
							<option value="">Select the height</option>
							<option value="0">At the horizon</option>
							<option value="15">Very low (15&deg;)</option>
							<option value="30">Low (30&deg;)</option>
							<option value="45">Average (45&deg;)</option>
							<option value="60">High (60&deg;)</option>
							<option value="75">Very high (75&deg;)</option>
							<option value="90">Directly overhead</option>
						</select><br/>
				</fieldset>
			
				<fieldset style=" margin-bottom: 25px;"><legend>Where did you <b>last</b> see the fireball?</legend>
					<label for="loc_enddirection">Direction:</label>
						<select name="enddirection" id="loc_enddirection">
							<option value="">Select the direction</option>
							<option value="0">North</option>
							<option value="45">Northeast</option>
							<option value="90">East</option>
							<option value="135">Southeast</option>
							<option value="180">South</option>
							<option value="225">Southwest</option>
							<option value="270">West</option>
							<option value="315">Northwest</option>
						</select><br/>
					<label for="loc_endheight">Height:</label>
						<select name="endheight" id="loc_endheight">
							<option value="">Select the height</option>
							<option value="0">At the horizon</option>
							<option value="15">Very low (15&deg;)</option>
							<option value="30">Low (30&deg;)</option>
							<option value="45">Average (45&deg;)</option>
							<option value="60">High (60&deg;)</option>
							<option value="75">Very high (75&deg;)</option>
							<option value="90">Directly overhead</option>
						</select><br/>
				</fieldset>
				</div>
			
				<div style="width: 290px; float: right; margin-top: 10px;">
				<fieldset><legend>Measuring angles in the sky</legend>
					<img src="images/degrees.jpg" alt="How to measure angles in the sky: a clenched fist held at arm's length is about 10 degrees, an outspread hand is about 20 degrees" />
				</fieldset>
				</div>
				
			</div>
		</fieldset>
		</div>
		
		
		<!-- WHAT FIELDSET -->
		<div id="report_form_fieldset">
		<fieldset><legend><b>What</b> did it look like?</legend><a name="what" />
			<p>Try and descibe what the fireball looked like. Was it very bright? How long did the event last? Did it show any color or fragmentation? Did it move very fast? Could you hear a sound? If possible, try to fill in this data in the more precise advanced fields!</p>	
			<p><a onclick="toggle_visibility('what_advanced');" style="cursor: pointer; text-decoration: underline; color: #554;"><img id="arrow" src="images/arrow_down2.png" alt="Show/Hide advanced fields" /> Click here to show advanced fields</a></p>
			
			<div id="what_advanced" style="display:none">
			
			<p>Please fill out as many fields as you can, and specify further in the general text field below.</p>
			<label for="what_mag">Magnitude:</label>
				<input type="text" maxlength="5" class="report_form_text" name="mag" id="what_mag" size="20" value="" />
				or, brightness by comparison to Full Moon:
				<select name="mag_est" id="what_mag_est" style="width: 10em;">
					<option value="Unknown"></option>
					<option value="Brighter">Brighter</option>
					<option value="FullMoon">About the same</option>
					<option value="Fainter">Fainter</option>
				</select>
			<br/>
			
			<label for="what_duration">Duration:</label>
				<input type="text" maxlength="5" class="report_form_text" name="duration" id="what_duration" size="20" value="" />s<br/>
				
			<label for="what_color">Color(s):</label>
				<input type="text" maxlength="20" class="report_form_text" name="color" id="what_color" size="20" value="" /><br/>
				
			<label for="what_fragmentation">Fragmentation:</label>
				<input type="radio" name="fragmentation" id="what_fragmentation" value="Unknown" checked /> Unknown
				<input type="radio" name="fragmentation" id="what_fragmentation" value="Yes" /> Yes
				<input type="radio" name="fragmentation" id="what_fragmentation" value="No" /> No
				<small>(If yes, please specify in general comments text field.)</small><br/>
				
			<label for="what_train">Persistent train:</label>
				<input type="text" maxlength="5" class="report_form_text" name="train" id="what_train" size="20" value="" />s<br/>
				
			<label for="what_vel">Angular velocity:</label>
				<input type="text" maxlength="20" class="report_form_text" name="vel" id="what_vel" size="20" value="" />&deg;/s
				, or, using a scale from 1 to 5: 
				<select name="vel_est" id="what_vel_est" style="width: 10em;">
					<option value="Unknown"></option>
					<option value="0">Stationary</option>
					<option value="1">Very slow</option>
					<option value="2">Slow</option>
					<option value="3">Average</option>
					<option value="4">Fast</option>
					<option value="5">Very fast</option>
				</select>
			<br/>
				
			<label for="what_sound">Sound:</label>
				<select name="sound" id="what_sound" style="width: 14em;">
					<option value="Unknown"></option>
					<option value="None">None</option>
					<option value="Sharp">Sharp</option>
					<option value="Smooth">Smooth</option>
					<option value="Staccato">Staccato</option>
				</select>
			<br/>
				
			<label for="what_interval">Interval between fireball and sound:</label>
				<input type="text" maxlength="5" class="report_form_text" name="interval" id="what_interval" size="20" value="" />s<br/>
			<br/>
			</div>
			<label for="what_general">What did it look like?</label>
				<textarea class="report_form_text" name="general" id="what_general" rows="5" cols="75"></textarea>
		</fieldset>
		</div>
		
		
		<!-- OTHER FIELDSET -->
		<div id="report_form_fieldset"><a name="fireball" />
			<fieldset><legend>Other Remarks</legend>
				<p>Any other remarks you may have about your observation, i.e. did you see it very well, cloudiness,...</p>
				<label for="fireball_other">Remarks:</label>
				<textarea class="report_form_text" name="other" id="fireball_other" rows="5" cols="75"></textarea><br/>
			</fieldset>
		</div>
		
		
		<!-- WHO FIELDSET -->
		<div id="report_form_fieldset"><a name="who" />
		<fieldset><legend><b>Who</b> are you?</legend>
			<p>In some special cases, we might need to contact you about the event you reported. Your contact details will be kept private and no spam will be sent.</p>
			<label class="required" for="who_firstname">First name:</label>
				<input type="text" maxlength="50" class="report_form_text" name="firstname" id="who_firstname" size="50" value="" /><br/>
			<label class="required" for="who_lastname">Last name:</label>
				<input type="text" maxlength="50" class="report_form_text" name="lastname" id="who_lastname" size="50" value="" /><br/>
			<label class="required" for="who_country">Country:</label>
				<input type="text" maxlength="50" class="report_form_text" name="country" id="who_country" size="50" value="" /><br/>
			<label class="required" for="who_email">E-mail:</label>
				<input type="text" maxlength="50" class="report_form_text" name="email" id="who_email" size="50" value="" /><br/>
			<label for="who_phone">Phone:</label>
				<input type="text" maxlength="50" class="report_form_text" name="phone" id="who_phone" size="50" value="" /><br/>
		</fieldset>
		</div>
		
		
		<!-- Hidden fields, containing information about the referrer -->
		<input type="hidden" id="referrer_code" value="VFO" />
		<input type="hidden" id="language" value="en" />
		
		
		<!-- Pressing this button will check the form for correctness, if valid, show the recaptcha -->
		<input type="button" id="validate_button" value="Check my inputs" onclick="finishForm();"/>		
		
		
		<!-- Recaptcha -->
		<div id="recaptcha_div"></div>
		
		
		<!-- Errors, warnings and messages -->
		<div id="messages">The submit button will become available once you correctly enter all required data.<br/></div>
		<div id="errors"> </div>
		<div id="warnings"> </div>		
		
		
		<!-- Results from PHP form processing script -->
		<div id="result"></div>
		
		
		<!-- Submit! -->
		<input type="button" id="submit_button" value="Submit" onclick="doSubmit()" disabled />
</div>
</form>


<?php
include("footer.inc.php");
?>