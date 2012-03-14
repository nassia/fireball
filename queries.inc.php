<?php
class DBQuerier {
	private $conn;
	
	function __construct(){
		$conn = pg_connect("host=localhost port=5433 dbname=fireball user=smena password=fireball42");
		if (!$conn) {
			throw new Exception("Could not connect to database");
		}
	}
	
	function __destruct() {
		pg_close($conn);
	}
	
	function getCountConfirmedObs() {
		$query = "SELECT count(*) FROM confirmed_observations";
		$result = pg_query($connect, $query);
		if (!result) {
			throw new Exception("Error querying database, using query: " . $query . pg_last_error($connect));
		}
		$row = pg_fetch_row($result);
		$row0 = $row[0];
		return $row0;
	}
}

?>