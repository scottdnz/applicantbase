<?php
/**
 * This file contains functions for connecting to and running direct SQL 
 * queries on a MySQL database.
 * 
 * @author Scott Davies
 * @version 1.0
 * @package db_funcs
 */


/**
 * Returns a connection to the database and/or an error result message.
 * @return array $result_arr
 */
function get_connection() {
  $result = "";
  /* Get database connection. If there is a connection problem, exit.
   * Syntax: mysqli(host, user, password, db) */ 
  $conn = new mysqli("localhost", "appbaseuser", "s2redRac", "appbase"); 
  if (mysqli_connect_errno()) {
    $result .= "Problem connecting: " . mysqli_connect_error();
    $conn = ""; 
  }
  $result_arr = array($result, $conn);
  return $result_arr;
}
  
  
/*
 * Processes the array of results returned by a SQL query. Returns an array
 * of any messages needing display. 
 * @param array $query_result_arr
 * @return array $q_errors
 */
function check_for_result_problems($query_result_arr) {
  $q_errors = array();
  // Check whether the array of query results list is empty.
  if ( (! isset($query_result_arr)) || (count($query_result_arr) < 1) ) {
    $q_errors[] = "No records found for query";
  }
  return $q_errors;
}
  
  
/**
 * Attempts to run a query on the database. Returns an array of log
 * messages and an array of any query result rows found in the db. 
 * @param "database connection object" $conn
 * @param string $sql
 * @return array $result_arr
 */
function run_query($conn, $sql, $res_type=MYSQLI_ASSOC) {
  $result_msg_arr = array();
  $query_result_arr = array();
  
  $query_result = mysqli_query($conn, $sql);
  if ($query_result != TRUE) {
    // Bad query result: error.
    $result = "There was a problem running query: " . $sql . ". ";
    $result .= "Error: " . mysqli_error($conn);
    $result_msg_arr[] = $result;
  }
  else {
    // Good-ish query result: fetch results as an associative array (dict).
    while($row = mysqli_fetch_array($query_result, $res_type)) {
      $query_result_arr[] = $row;
    }
    // Only do result checking on the first non-empty record of query result
//     if (count($query_result_arr) > 1) {
//       $query_result_slice = $query_result_arr[0]; 
//     }
//     else {
//       $query_result_slice = $query_result_arr;
//     }
    #$result_msg_arr[] = check_for_result_problems($query_result_slice);
  }
  $result_arr = array($result_msg_arr, $query_result_arr);
  return $result_arr;
}
  
  
/**
 * Runs a query on the database that modifies it, e.g. creates table,
 * inserts data values, updates. 
 * @param "database connection object" $conn
 * @param string $sql
 * @param array 
 */
function run_modify_query($conn, $sql) {
  $result_msg = "";
  $query_result = mysqli_query($conn, $sql);
  if (! $query_result) {
    // Bad query result: error.
    $result = "There was a problem running query: " . $sql . ". ";
    $result .= "Error: " . mysqli_error($conn);
    $result_msg .= $result;
  }
  return array($result_msg, $query_result);
}
  
  
/**
* Converts an array to a string representation, i.e. "(item1, item2, ...)".
 * @param array $arr
* @return string $in_strg
*/
//   function get_mysql_in_strg_from_arr($arr) {
//     $in_strg = "";
//     $len_arr = count($arr);
//     for ($i = 0; $i < $len_arr; $i++) {
//     $in_strg .= (string)$arr[$i];
//     if ($i < ($len_arr - 1)) {
//     $in_strg .= ", ";
//     }
//     }
//     $in_strg = "(" . $in_strg . ")";
//     return $in_strg;
//   }

?>