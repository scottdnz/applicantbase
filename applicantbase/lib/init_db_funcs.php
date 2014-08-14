<?php 
	/**
	 * This file creates tables with their fields in the database.
	 * 
	 * @author Scott Davies
	 * @version 1.0
	 * @package
	 */

	
	/**
	 * Drops all tables in the database, if required.
	 * @param "database connection object" $conn
	 */
	function drop_all_tables($conn) {
		$sql = "show tables;";
		$result_msg_arr = run_query($conn, $sql);
		$query_result_arr = $result_msg_arr[1];
		foreach($query_result_arr as $row) {
			$tbl = $row["Tables_in_appbase"];
			$sql = "drop table if exists ". $tbl . ";";
			$result_msg_arr = run_modify_query($conn, $sql);
			if (strlen($result_msg_arr[0]) < 1) {
				echo "Table " . $tbl . " dropped.\n";
			}
			else {
				echo "Problem, table " . $tbl . " NOT dropped.\n";
			}
		}
		return;
	}
	
	
	/**
	 * Returns a 1 or 0 depending on whether a table already exists. 
	 * @param "database connection object" $conn
	 * @param string $tbl
	 * @return bool $tbl_exists
	 */
	function check_table_exists($conn, $tbl) {
		$sql = "select count(*) AS count from information_schema.tables ";
		$sql .= "where table_schema = 'appbase' and table_name = '" . $tbl . "';";
		$result_arr = run_query($conn, $sql);
		if (count($result_arr[0]) > 0) {
      // Possible error found. Ignore?
		}
		$query_result_arr = $result_arr[1];
		$tbl_exists = $query_result_arr[0]["count"];
		return $tbl_exists;
	}
?>