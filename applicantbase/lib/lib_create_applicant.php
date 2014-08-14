<?php
/**
 * This file contains functions for use with the create_job web page. 
 * 
 * @author Scott Davies
 * @version 1.0
 * @package lib_create_job
 */
	

/**
 * Performs a number of SQL queries for blank Get page, and returns the results 
 * in a 2d array.
 * @param mysqli connection object $conn
 * @return array $res
 */
function get_new_app_field_vals($conn) {
  // $res 2d array to hold query results.
  $res = array("applied_for" => array(),
    "position" => array(),
    "application_source" => array(),
    "status_shortlisting" => array(),
    "reject_notification_sent" => array(),
    "status_screening" => array(),
    "status_interview" => array(),
    "flag_future" => array(),
    "error_arr" => array()
  );
  while (TRUE) {
    // Query for applied_for table.
    $sql = "select id, applied_for, deflt from position_applied_for where ";
    $sql .= "valid = 1;";
    $res_arr = run_query($conn, $sql);
    if (count($res_arr[0]) > 0) {
      $res["error_arr"][] = $res_arr[0];
      break; 
    }
    // Form values for applied_for retrieved.
    $res["applied_for"] = $res_arr[1]; 
    
    // Query for job table.
    $sql = "select id, title from job where filled = 0";
    $res_arr = run_query($conn, $sql);
    if (count($res_arr[0]) > 0) {
      $res["error_arr"][] = $res_arr[0];
      break;
    }
    // Form values for application_source retrieved.
    $res["position"] = $res_arr[1];
  
    // Query for application_source table.
    $sql = "select id, source, deflt from application_source where valid = 1;";
    $res_arr = run_query($conn, $sql);
    if (count($res_arr[0]) > 0) {
      $res["error_arr"][] = $res_arr[0];
      break; 
    }
    // Form values for application_source retrieved.
    $res["application_source"] = $res_arr[1];
  		
    // Query for status_shortlisting table.
    $sql = "select id, status, deflt from status_shortlisting where valid = 1;"; 
    $res_arr = run_query($conn, $sql);
    if (count($res_arr[0]) > 0) {
      $res["error_arr"][] = $res_arr[0];
      break; 
    }
    // Form values for status_shortlisting retrieved.
    $res["status_shortlisting"] = $res_arr[1];
  		
    // Query for reject_notification_sent table.
    $sql = "select id, sent, deflt from reject_notification_sent where valid ";
    $sql .= "= 1;";
    $res_arr = run_query($conn, $sql);
    if (count($res_arr[0]) > 0) {
      $res["error_arr"][] = $res_arr[0];
      break; 
    }
    // Form values for reject_notification_sent retrieved.
    $res["reject_notification_sent"] = $res_arr[1];
  		
    // Query for status_screening table.
    $sql = "select id, status, deflt from status_screening where valid = 1;"; 
    $res_arr = run_query($conn, $sql);
    if (count($res_arr[0]) > 0) {
      $res["error_arr"][] = $res_arr[0];
      break; 
    }
    // Form values for status_screening retrieved.
    $res["status_screening"] = $res_arr[1];
  		
    // Query for status_interview table.
    $sql = "select id, status, deflt from status_interview where valid = 1;"; 
    $res_arr = run_query($conn, $sql);
    if (count($res_arr[0]) > 0) {
      $res["error_arr"][] = $res_arr[0];
      break; 
    }
    // Form values for status_interview retrieved.
    $res["status_interview"] = $res_arr[1];
  		
    // Query for reject_notification_sent table.
    $sql = "select id, sent, deflt from reject_notification_sent where valid ";
    $sql .= "= 1;";
    $res_arr = run_query($conn, $sql);
    if (count($res_arr[0]) > 0) {
      $res["error_arr"][] = $res_arr[0];
      break; 
    }
    // Form values for reject_notification_sent retrieved.
    $res["reject_notification_sent"] = $res_arr[1];
  		
    // Query for flag_future table.
    $sql = "select id, flag, deflt from flag_future where valid = 1;";
    $res_arr = run_query($conn, $sql);
    if (count($res_arr[0]) > 0) {
      $res["error_arr"][] = $res_arr[0];
      break; 
    }
    // Form values for reject_notification_sent retrieved.
    $res["flag_future"] = $res_arr[1];
    
    break;
  } // End of while.
  return $res;
}

?>