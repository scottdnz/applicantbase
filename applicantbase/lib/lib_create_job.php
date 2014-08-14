<?php
/**
 * This file contains functions for use with the create_job web page. 
 * 
 * @author Scott Davies
 * @version 1.0
 * @package lib_create_job
 */


require("../lib/date_funcs.php");
require("../lib/validate.php");


/**
 * Manually checks/validates input values. (This could be refactored!)
 * @param string $job_title
 * @param string $date_started
 * @param string $description
 * @param string $ad_source
 * @param string $filled
 * @return array
 */
function process_main_inputs($job_title, $date_started, $description, 
							  $ad_source, $filled) {
  $error_arr = array();
  $field_vals = array(); # 2d.
  $res = validate_string("Job Title", $job_title, True);
  if (strlen($res[0]) > 0) {
  	$error_arr[] = $res[0];
  }
  $field_vals["job_title"] = $res[1];
  // Special date field.
  $res = validate_string("Date Vacancy Opened", $date_started, True,
  		                  $text_limit=10);
  if (strlen($res[0]) > 0) {
  	$error_arr[] = $res[0];
  }
  else {
  	$res = validate_date("Date Vacancy Opened", $date_started, True);
  	if (strlen($res[0]) > 0) {
      $error_arr[] = $res[0];
  	}
  	else {
  		$res = convert_to_timestamp($date_started, True, True);
      if (strlen($res[0]) > 0) {
        $error_arr[] = $res[0];
      }
  	}
  }
  $field_vals["date_started"] = $res[1];
  $res = validate_string("Description", $description, False,
  		                  $text_limit=2000);
  if (strlen($res[0]) > 0) {
  	$error_arr[] = $res[0];
  }
  $field_vals["description"] = $res[1];
  $res = validate_string("Advertised in", $ad_source, True);
  if (strlen($res[0]) > 0) {
  	$error_arr[] = $res[0];
  }
  $field_vals["ad_source"] = $res[1];
  $res = validate_string("Filled", $filled, True, $text_limit=1);
  if (strlen($res[0]) > 0) {
  	$error_arr[] = $res[0];
  }
  $field_vals["filled"] = $res[1];
  return array($error_arr, $field_vals);
}
	
	
/**
 * Manually checks/validates input values. (This could be refactored!)
 * @param string $app_filled_by
 * @param string $date_filled
 * @ return array
 */
function process_extra_inputs($app_filled_by, $date_filled) {
  $error_arr = array();
  $extra_vals = array(); #2d
  $bad_app_filled_val = "0";
  // Validate $app_filled_by
  $res = validate_string("Applicant Filled By", $app_filled_by, True);
  if (strlen($res[0]) > 0) {
  	$error_arr[] = $res[0];
  }
  $extra_vals["app_filled_by"] = $res[1];
  if ($extra_vals["app_filled_by"] == $bad_app_filled_val) {
  	$error_arr[] = "You must choose a person as the Applicant. ";
  }
  // Validate $date_filled. Special date field.
  $res = validate_string("Date Vacancy Filled", $date_filled, True,
  		                  $text_limit=10);
  if (strlen($res[0]) > 0) {
  	$error_arr[] = $res[0];
  }
  else {
  	$res = validate_date("Date Vacancy Filled", $date_filled, True);
  	if (strlen($res[0]) > 0) {
      $error_arr[] = $res[0];
  	}
  	else {
      $res = convert_to_timestamp($date_filled, True, True);
      if (strlen($res[0]) > 0) {
      	$error_arr[] = $res[0];
      }
  	}
  }
  $extra_vals["$date_filled"] = $res[1];
  return array($error_arr, $extra_vals);
}
	
	
/**
 * Takes an array of field values, and returns a SQL command string.
 * Query format: 
 * "insert into job (
	title, date_started, description, ad_source, filled 
	[, applicant_filled_by_id, date_filled]     #if filled 
	) values (varchar (64), date, text, varchar (32), bool [, int, date]);
	
 * @param array $field_vals
 * @return string
 */
function get_insert_job_sql($field_vals) {
	$job_title = $field_vals[0];
	$date_started = $field_vals[1];
	$description = $field_vals[2];
	$ad_source = $field_vals[3];
	$filled = $field_vals[4];
	$sql = "insert into job (";
	$sql .= "title, date_started, description, ad_source, filled";
	$value_strg = "'" . $job_title . "', '" . $date_started . "', ";
	$value_strg .= "'" . $description . "', '" . $ad_source . "', " . $filled;
	if ($filled > 0) {
      $app_filled_by = $field_vals[5];
      $date_filled = $field_vals[6];
      $sql .= ", applicant_filled_by_id, date_filled";
      $value_strg .= ", " . $app_filled_by . ", '" . $date_filled . "'";
	}
	$sql .= ") values (" . $value_strg . ");";
	return $sql;
}
	
?>