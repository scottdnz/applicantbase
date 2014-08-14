<?php
  /**
   * This file contains functions for use with the view jobs web page. 
   * 
   * @author Scott Davies
   * @version 1.0
   * @package lib_view jobs
   */


// require("../lib/settings.php");
require("../lib/validate.php");


/**
 * Takes a string in format "yyyy-mm-dd" and returns it in the format 
 * "dd/mm/yyyy".
 * @param string $mysql_date_strg
 * @return array
 */
function get_disp_date($mysql_date_strg) {
  $dt = explode("-", $mysql_date_strg);
  return sprintf("%s/%s/%s", $dt[2], $dt[1], $dt[0]);
}


/**
 * Retrieves fields from the job table.
 * @param "database connection object" $conn
 * @param string title
 * @param string date_started
 * @param string date_filled
 * @return array
 */
function get_jobs($conn, $title="", $date_started="", $date_filled="") {
  $errors = "";
  $results = array();
  $sql = "select title, filled, date_started, date_filled from job;";
  $res = run_query($conn, $sql);
  if (count($res[0]) > 0) {
    foreach ($res[0] as $err) {
      $errors .= $err;
    }
  }
  else {
    $results = $res[1];
    for ($i = 0; $i < count($results); $i++) {
      // Get an image file name to insert for "filled".
      if ($results[$i]["filled"] == 1) {
        $results[$i]["filledPic"] = "yes.png";
      }
      else {
        $results[$i]["filledPic"] = "no.png";
      }
      // Get a different date display string for "date_started".
      $results[$i]["date_started"] = get_disp_date($results[$i]["date_started"]
      );
      if (isset($results[$i]["date_ended"])) {
        $results[$i]["date_ended"] = get_disp_date($results[$i]["date_ended"]);
      }
    }
  }
  return array($errors, $results);
}


/**
 * Checks the strings in the 2d $inp array for errors. 
 * @param unknown $inp
 * @return multitype:string
 */
function validate_jobs_inputs_strings($inp) {
  $errors = array();
  $date_fields = array("start", "end");
  // Validate date fields strings.
  foreach ($date_fields as $df) {
    if (! array_key_exists($df, $inp)) {
      continue;
    }
    $cur_df = $inp[$df]["val"];
    //     echo $cur_df . "\n";
    if ( ($cur_df) && (! validate_date_strg($cur_df)) ) {
      $errors[] = "Problem with datetime field: " . $inp[$df]["lbl"] . ". ";
    }
  }
  // Validate bool field string.
  if (array_key_exists("filled", $inp)) {
    if (! validate_bool_strg($inp["filled"]["val"])) {
      $errors[] = "Problem with boolean field: " . $inp["filled"]["lbl"] . ". ";
    }
  }
  return $errors;
}


/**
 * Typecasts the input strings to their proper types.
 * @param unknown $d_format
 * @param string $start_strg
 * @param string $end_strg
 * @param string $filled_strg
 * @return multitype:boolean number NULL
 */
function cast_jobs_inputs($d_format, $start_strg="", $end_strg="",
    $filled_strg="") {
  $f = array("start"=> "", "end"=> "", "filled"=> "");
  // Typecast.
  if (count($start_strg) > 0) {
    $f["start"] = DateTime::createFromFormat($d_format, $start_strg);
  }
  if (count($end_strg) > 0) {
    $f["end"] = DateTime::createFromFormat($d_format, $end_strg);
  }
  if ($filled_strg != "") {
    $f["filled"] = (int)$filled_strg;
  }
  return $f;
}


/**
 * Runs a query on the database, and returns the results.
 * @param unknown $conn
 * @param unknown $d_format
 * @param string $start
 * @param string $end
 * @param string $filled
 * @return unknown
 */
function search_for_jobs($conn, $d_format, $start="", $end="", $filled="") {
  $sql = "select id, title, date_started, filled, applicant_filled_by_id, date_filled from job";
  $clauses = array();
  if (count($start) > 0) {
    $clauses[] = "date_started >= '" . $start->format($d_format) . "'";
  }
  if (count($end) > 0) {
    $clauses[] = "date_started <= '" . $end->format($d_format) . "'";
  }
  if ($filled != "") {
    $clauses[] = "filled = " . $filled;
    //     $clauses[] = "date_filled <= '" . $end->format($d_format) . "'";
  }
  if (count($clauses) > 0) {
    $sql .= " where " . implode(" and ", $clauses) . ";";
  }
  echo $sql;
  $res = run_query($conn, $sql);
  return $res;
}


function data_for_filters() {
//   $start_opts, $end_opts, $filled_opts, "val", "lbl"
//   $disp_d_fmt = "d/m/Y";
  $d_format = "Y-m-d-H-i-s";
  $sixty_days = new DateInterval("P60D");
  $thirty_days = new DateInterval("P30D");
  $six_months = new DateInterval("P06M");
  
  $defaults = array("start"=> "90 days ago", "end"=> "Not selected", 
      "filled"=> "Not selected");
  
  $cur_dt = new DateTime(); // Now.
  
  $start_opts = array("Not selected"=> "");
  $end_opts = array("Not selected"=> $cur_dt->format($d_format));
  
  $cur_dt->sub($thirty_days);
  $start_opts["30 days ago"] = $cur_dt->format($d_format);  
  $end_opts["30 days ago"] = $cur_dt->format($d_format);
  $cur_dt->sub($sixty_days);
  $start_opts["90 days ago"] = $cur_dt->format($d_format);
  $end_opts["90 days ago"] = $cur_dt->format($d_format);
  
  $cur_dt->sub($six_months);
  $end_opts["6 months ago"] = $cur_dt->format($d_format);
  $cur_dt->sub($six_months);
  $end_opts["1 year ago"] = $cur_dt->format($d_format);
  $cur_dt->sub($six_months);
  $end_opts["18 months ago"] = $cur_dt->format($d_format);
  $cur_dt->sub($six_months);
  $end_opts["2 years ago"] = $cur_dt->format($d_format);
  // Bool.
  $filled_opts = array("Not selected"=> "", "No"=> "0", "Yes"=> "1");
  
  return array("start_opts"=> $start_opts, "end_opts"=> $end_opts, 
      "filled_opts"=> $filled_opts, "defaults"=> $defaults);
}


function applicant_filled_details($conn, $applicant_ids) {
  $sql = "select id, first_name, surname from applicant where id in ("; 
  $sql .= implode(", ", $applicant_ids) . ");";
  $res = run_query($conn, $sql);
  return $res;  
}


function format_jobs_rows_for_display($conn, $rows) {
//   id, title, date_started, filled, applicant_filled_by_id, date_filled from job
  $errors = array();
  $disp_d_fmt = "d/m/Y";
  $d_format_from_mysql = "Y-m-d H:i:s";
  // Build an array of these for a query.
  $applicant_ids_for_filled_jobs = array();
  for ($i = 0; $i < count($rows); $i++) {
    $temp_dt_started = DateTime::createFromFormat($d_format_from_mysql, 
                                              $rows[$i]["date_started"]);
    $rows[$i]["date_started"] = $temp_dt_started->format($disp_d_fmt);
//     echo "date_filled: " . $rows[$i]["date_filled"] . "<br />";
    // false can be "" or NULL
    if ($rows[$i]["date_filled"] == false) {
      $rows[$i]["date_filled"] = "n/a";
    }
    else {
      $temp_dt_filled = DateTime::createFromFormat($d_format_from_mysql, 
                                            $rows[$i]["date_filled"]);
      $rows[$i]["date_filled"] = $temp_dt_filled->format($disp_d_fmt);
    }
    if (! is_null($rows[$i]["applicant_filled_by_id"])) {
      $applicant_ids_for_filled_jobs[] = $rows[$i]["applicant_filled_by_id"];
    }
  }
  
  if ($applicant_ids_for_filled_jobs) {
    $res = applicant_filled_details($conn, $applicant_ids_for_filled_jobs);
    $errors = array_merge($errors, $res[0]);
//     echo print_r($res[1]);
    
    if (! $errors) {
      foreach ($res[1] as $appct_row) {
        for ($i = 0; $i < count($rows); $i++) {
          if ($rows[$i]["applicant_filled_by_id"] == $appct_row["id"]) {
            $rows[$i]["applicant_name"] = $appct_row["first_name"] . " " . $appct_row["surname"]; 
          }
        }
      }
      
    }
  }
  
  return array($errors, $rows);
}

?>