</div>

<div id="sidebar">
	<h2>Current status</h2>
		<p>Working on cross site forms</p>
		<p>
			<?php
				require_once("DBQuerier.inc.php");
				$dbquerier = new DBQuerier;
				echo $dbquerier->getCountConfirmedObs();
			?>
		 confirmed observations</p>
		
	<h2>Interesting links</h2>
		<ul>
			<li><a href="http://www.imo.net">IMO</a></li>
			<li><a href="http://www.rssd.esa.int/index.php?project=METEOR&amp;page=vmo">ESA Virtual Meteor Observatory</a></li>
		</ul>
	<h2>Contact the developer</h2>
		<ul>
			<li><a href="mailto:nassia@gmail.com?subject=VFO">E-mail</a></li>
		</ul>