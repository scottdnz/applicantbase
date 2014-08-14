<?php
	/**
	 * This file reads a series of CSV files, and inserts the contents into the
	 * relevant database tables. 
	 * 
	 * @author Scott Davies
	 * @version 1.0
	 * @package
	 */


	require("db_funcs.php");
	require("init_db_funcs.php");
	require("settings.php");
	require("encryption.php");
	
	
	/**
	 * Reads a specified CSV file, stores the row elements in an array. Returns
	 * the array.
	 * @param string $data_dir
	 * @param string $tbl
	 * @return array $csv_rows
	 */
	function get_csv_rows($data_dir, $tbl) {
		$dataf = $data_dir . "/" . $tbl . ".csv";
		$csv_rows = array();
		// Open file & read it in chunks.
		if (($f_to_read = fopen($dataf, "r")) !== FALSE) {
	    while ( ($row = fgetcsv($f_to_read, 50, ",")) !== FALSE ) {
	      $row_arr = array();
	      for ($cell = 0; $cell < count($row); $cell++) {
    			$row_arr[] = $row[$cell];    
	    	}
	    	$csv_rows[] = $row_arr; 
	    }
	    fclose($f_to_read);
		}
		return $csv_rows;
	}
	
	
	/**
	 * Takes the table name and array of header rows. Returns the start of a
	 * SQL statement, and quoting ('') information about each cell in a row.
	 * @param string $tbl
	 * $param array $hdr_rows
	 * @return array
	 */
	function get_header_rows_info($tbl, $hdr_rows) {
		$cell_quote_info = array();
		$base_sql = "insert into " . $tbl . " (";
		// Loop through each of the 2 header rows that should be there.
		for ($h = 0; $h < 2; $h++) {
			$len_row = count($hdr_rows[$h]);
			// Loop through each cell in the row.
			for ($i = 0; $i < $len_row; $i++) {
				$cur_cell = $hdr_rows[$h][$i];
				if ($h == 0) {	// It's the first header row.
					if ($i < ($len_row - 1)) {
						$base_sql .= $cur_cell . ", ";
					}
					else {
						$base_sql .= $cur_cell . ") values (";
					}
				}
				else if ($h == 1) {	// It's the second quote information row.
					$cell_quote_info[$i] = $cur_cell;
				}
			}
		}
		return array($base_sql, $cell_quote_info);
	}
	
	
	/**
	 * Processes the data rows. Returns an array containing a series of SQL 
	 * statements, one for each data row.
	 * @param array $data_rows
	 * @param string $base_sql
	 * @param array $cell_quote_info
	 * @return array $tbl_sql
	 */
	function get_table_sql($data_rows, $base_sql, $cell_quote_info) {
		$tbl_sql = array();
		// Loop through each row.
		foreach ($data_rows as $row) {
			$sql = "";
			$len_row = count($row);
			// Loop through each cell in the row.
			for ($i = 0; $i < $len_row; $i++) {
				$cur_cell = $row[$i];
				// Store the information in the cell as quoted or not.
				if ($i < ($len_row - 1)) {		// Not the last cell in the data row.
					if ($cell_quote_info[$i] == "quoted") {
						$sql .= "'" . $cur_cell . "', ";
					}
					else {
						$sql .= $cur_cell . ", ";
					}
				}
				else {	// Last cell in the data row.
					if ($cell_quote_info[$i] == "quoted") {
						$sql .= "'" . $cur_cell . "');\n";
					}
					else {
						$sql .= $cur_cell . ");\n";
					}
					// End of row.
				}
			}
			$tbl_sql[] = $base_sql . $sql;	
		}
		return $tbl_sql;		
	}
	
	
	/**
	 * For each of the tables that needs to be filled by data in CSV file:
	 * read the CSV file, get the header rows info, process the data rows,
	 * then run SQL queries for inserting each row of data.
	 * @param "database connection object" $conn
	 */
	function insert_csv_info_into_tables($conn) {
		$tbls = array("application_source", "flag_future", "position_applied_for",
		  						"reject_notification_sent" , "status_interview",
								  "status_screening", "status_shortlisting");
		$data_dir = "../static/data";	
		foreach ($tbls as $tbl) {
			$csv_rows = get_csv_rows($data_dir, $tbl);
			$hdr_rows = array_slice($csv_rows, 0, 2);
			$data_rows = array_slice($csv_rows, 2);
			$tbl_sql = "";
			
			$hdr_info = get_header_rows_info($tbl, $hdr_rows);
			$base_sql = $hdr_info[0];
			$cell_quote_info = $hdr_info[1];
			
			$tbl_sql = get_table_sql($data_rows, $base_sql, $cell_quote_info);
			// Run all the SQL queries needed for a table.
			foreach ($tbl_sql as $query) {
				$result_msg_arr = run_modify_query($conn, $query);
				if (count($result_msg_arr) > 0) {
					echo $result_msg_arr[0];
				}
			}
		}
	}  // End of function
	
	
	/**
	 * Fills the user table in the db with values. 
	 * @param "database connection object" $conn
	 * @param array $init_passwords
	 */
	function fill_user_table($conn, $init_passwords) {
  	foreach ($init_passwords as $user => $password) {
  	  $enc_password = get_enc_password($password);
  	  $valid = 1;
  	  $sql = "insert into user (username, password_enc, valid) values (";
  	  $sql .= "'" . $user . "', '" . $enc_password . "', " . $valid . ");";
  	  $result_msg_arr = run_modify_query($conn, $sql);
  	  if (count($result_msg_arr) > 0) {
  	    echo $result_msg_arr[0];
  	  }
  	}
	}
	
	
	/**
	 * Main flow of program.
	 */
	$result_arr = get_connection();
	$result = $result_arr[0];
	if (strlen($result) > 0) {
		// Possible error found.
		echo $result;
		exit();
	}
	$conn = $result_arr[1];
	
	insert_csv_info_into_tables($conn);
	fill_user_table($conn, $init_passwords);
	
	// user table is special
	$unenc_pw = "9uHa4as5aphu9rev";
	
?>