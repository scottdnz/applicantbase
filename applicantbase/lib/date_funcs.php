<?php
  /**
  * This file contains functions for dealing with PHP date objects and time-
  * stamps.
  *
  * @author Scott Davies
  * @version 1.0
  * @package date_funcs
  */


  function get_date_vals($date_strg, $is_start_val) {
    $errors = "";
    $date_vals = array();
    $end_hour = 23;
    $end_min = 59;
    $end_sec = 59;
    /* Unpack a date from a string in the format "dd/mm/yyyy" */
    $date_arr = explode("/", $date_strg);
    $day = (int)$date_arr[0];
    $month = (int)$date_arr[1];
    $year = (int)$date_arr[2];
    // If the end date is today's date, set the timestamp to this exact moment.
    $now = getdate(time());
    $now_d = (int)$now["mday"];
    $now_m = (int)$now["mon"];
    $now_y = (int)$now["year"];
    if ( ($day == $now_d) && ($month == $now_m) && ($year == $now_y) ) {
      $end_hour = $now["hours"];
      $end_min = $now["minutes"];
      $end_sec = $now["seconds"];
    }
//     // Check date is valid. checkdate() function is a PHP builtin.
// 		 //	 Double handling
//     if (! checkdate($month, $day, $year)) {
//       $errors .= "An invalid date was received. It must in the format ";
//       $errors .= "dd/mm/yyyy.";
//     }
//     else {
    if ($is_start_val) {
      $date_vals = array(0, 0, 0, $month, $day, $year);
    }
    else {
      $date_vals = array($end_hour, $end_min, $end_sec, $month, $day, $year);
    }
    return array($errors, $date_vals);
  }
  

  /**
  * Converts a date formatted string to a timestamp. Returns the timestamp.
  * @param string date_strg
  * @param bool $is_start_val
  * @param bool $is_mysql_timestamp
  * @return array
  */
  function convert_to_timestamp($date_strg, $is_start_val, $is_mysql_timestamp) 
  {
    $errors = "";
    $ts = "";
    $res_arr = get_date_vals($date_strg, $is_start_val);
    if (strlen($res_arr[0]) > 0) {
      $errors .= $res_arr[0];
    }
    else {
      // Was a valid date field. Retrieve values from date_vals array.       
      $date_vals = $res_arr[1];      
      $hour = $date_vals[0];
      $min = $date_vals[1];
      $sec = $date_vals[2];
      $month = $date_vals[3];
      $day= $date_vals[4];
      $year = $date_vals[5];
      
      if ($is_mysql_timestamp) {
        /* Convert to a timestamp string for MySQL.
         * Format: "YYYY-MM-DD HH:MM:SS" or php date format "Y-m-d H-i-s". */        
        $ts .= sprintf("%s-%s-%s %s:%s:%s", $year, $month, $day, $hour, $min, 
                       $sec);
      }
      else {
        /* Convert to a timestamp int value (for date comparisons).
         * Syntax: mktime(hour, min, sec, month, day, year) */
        $ts .= mktime($hour, $min, $sec, $month, $day, $year);
      }
    }
    return array($errors, $ts);
  }
  
  
  /**
   * Takes two dates and checks whether there are any errors for using them as
   * start and end dates in a date range.
   * @param int $date_from
   * @param int $date_to
   * @param int $min
   * @param int $max
   * @return string
   */
  function check_dates_range($date_from, $date_to, $min=1230721200, $max=0)
  {
    $errors = "";
    if ($max < 1) {
      $max = time() + 60;
    }
    if ($date_from > $date_to) {
      $errors .= "The start date cannot be later than the end date.";
    }
    if (($date_from < $min) || ($date_to < $min)) {
      $display_min = date("d/m/Y", $min);
      $errors .= "The dates cannot be earlier than ". $display_min . ".";
    }
    if (($date_from > $max) || ($date_to > $max)) {
      $display_max = date("d/m/Y  H:i:s", $max);
      $errors .= "The dates cannot be later than ". $display_max . ". ";
    }
    return $errors;
  }
  
  
  /**
   * Tries to convert date input string fields to UNIX timestamp values.
   * @param array $errors_arr
   * @param string $date_from
   * @param string $date_to
   * @return array
   */
  function get_timestamps($date_from_orig, $date_to_orig) {
    $err_arr = array();
    while (1) {
      // Get valid Unix timestamps for the start and end dates, for comparison.
      $date_arr = convert_to_timestamp($date_from_orig, TRUE, FALSE);
      $errors = $date_arr[0];
      if (strlen($errors) > 0) {
        $err_arr[] = $errors;
        break;
      }
      $date_from = $date_arr[1];
      
      $date_arr = convert_to_timestamp($date_to_orig, FALSE, FALSE);
      $errors = $date_arr[0];
      if (strlen($errors) > 0) {
        $err_arr[] = $errors;
        break;
      }
      $date_to = $date_arr[1];
      
      $errors = check_dates_range($date_from, $date_to);
      if (strlen($errors) > 0) {
        $err_arr[] = $errors;
        break;
      }
      // Both good dates. Convert to MySQL timestamps, for insertion into DB.
      $res_arr = convert_to_timestamp($date_to_orig, TRUE, TRUE);
      $date_to = $res_arr[1];
      $res_arr = convert_to_timestamp($date_from_orig, FALSE, TRUE);
      $date_from = $res_arr[1]; 
      break;
    }
    return array($err_arr, $date_from, $date_to);
  }

?>