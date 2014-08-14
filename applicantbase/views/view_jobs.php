<?php
/**
 * This file contains the view instructions for the View Jobs page.  
 */


require("../lib/smarty_start.php");
require("../lib/db_funcs.php");
require("../lib/common_lib.php");
require("../lib/settings.php");
require("../lib/lib_view_jobs.php");


$page_title = "View Jobs";
$show_head_menu = 1;
$error_arr = array();
$extra_css_list = array("css/common.css", "css/view_jobs.css");
$extra_js_list = array("js/jquery.js", "js/common.js", "js/view_jobs.js");
$jobs_rows = array();
// 	$test_arr = array();

// Check auth.
session_start();
$authorised_arr = check_auth();
if ($authorised_arr[1] < 1) {
  header("Location: login.php");
}
// Get a DB connection.
$conn_res = get_connection();
if (strlen($conn_res[0]) > 0) {
	$error_arr[] = $conn_res[0];
}
else {
  // DB connection is ok.
  $conn = $conn_res[1];
}
  

// Default empty inputs array;
$inp = array("start"=> array("lbl"=> "Start Date",
                            "val"=> ""),
    "end"=> array("lbl"=> "End Date",
                  "val"=> ""),
    "filled"=> array("lbl"=> "Filled",
                      "val"=> "") );


if ($_SERVER["REQUEST_METHOD"] == "GET") {
  // Defaults.
  $ninety_days_ago = new DateInterval("P90D");
  $start_dt = new DateTime();
  $start_dt->sub($ninety_days_ago);
  $end_dt = new DateTime();
  $inp["start"]["val"] = $start_dt->format(D_FORMAT);
  $inp["end"]["val"] = $end_dt->format(D_FORMAT);
  $inp["filled"]["val"] = "";
  
  $f = cast_jobs_inputs(D_FORMAT, $inp["start"]["val"], $inp["end"]["val"],
      $inp["filled"]["val"]);
  $res = search_for_jobs($conn, D_FORMAT, $f["start"], $f["end"], $f["filled"]);
  $error_arr = array_merge($error_arr, $res[0]);
  if (! $error_arr) {
    $res = format_jobs_rows_for_display($conn, $res[1]);
    $error_arr = array_merge($error_arr, $res[0]);
  }
  if (! $error_arr) {
    $jobs_rows = $res[1];
  }
  
  // Data for select boxes query filter options.
  $filter_opts = data_for_filters();
  
  $smarty->assign("title", $page_title);
  $smarty->assign("show_head_menu", $show_head_menu);
  $smarty->assign("extra_css", $extra_css_list);
  $smarty->assign("extra_js", $extra_js_list);
  $smarty->assign("error_arr", $error_arr);
  $smarty->assign("jobs_rows", $jobs_rows);
  $smarty->assign("start_opts", $filter_opts["start_opts"]);
  $smarty->assign("end_opts", $filter_opts["end_opts"]);
  $smarty->assign("filled_opts", $filter_opts["filled_opts"]);
  $smarty->assign("defaults", $filter_opts["defaults"]);
  
  $smarty->display("view_jobs.html");
  
} // End of GET request processing.
  

elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
  $resp = array("jobs_rows"=> array(), "errors"=> array());
  //   $errors = array();
  $inp["start"]["val"] = filter_input(INPUT_POST, "start", FILTER_SANITIZE_STRING);
  $inp["end"]["val"] = filter_input(INPUT_POST, "end", FILTER_SANITIZE_STRING);
  $inp["filled"]["val"] = filter_input(INPUT_POST, "filled", FILTER_SANITIZE_STRING);
//   echo "<pre>" . print_r($inp) . "</pre><br />";
  
  
  // Validate inputs as strings.
  $resp["errors"] = array_merge($resp["errors"], validate_jobs_inputs_strings($inp));
  if (! $resp["errors"]) {
    // Convert inputs to proper types.
    $f = cast_jobs_inputs($d_format, $inp["start"]["val"], $inp["end"]["val"],
                                $inp["filled"]["val"]);
    // Validate datetime objects.
    $date_keys = array("start", "end");
    $dt_fields = array();
    foreach($date_keys as $d_key) {
      if (array_key_exists($d_key, $f)) {
        $dt_fields[$d_key] = $f[$d_key];
      }
    }
    // Testing
    $resp["errors"] = array_merge($resp["errors"], 
                                  array("dt_fields", $dt_fields));
    $f_msg = array("f!");
    foreach ($f as $k=> $v) {
      $f_msg[] = $k . ": " . gettype($v);
    }
    
    $resp["errors"] = array_merge($resp["errors"],
                                array("f", $f_msg));
//     $resp["errors"] = array_merge($resp["errors"],
//                                   array("inp", $inp));
    
    
    $resp["errors"] = array_merge($resp["errors"], 
                                  validate_dt_objects($dt_fields, $settings));
  }
  if (! $resp["errors"]) {
    // Validate start/end.
    if (count(array_intersect($date_keys, array_keys($inp))) == count($dt_fields)) {
      // Both date fields present.
      if (! validate_date_range($inp["start"]["val"], $inp["end"]["val"])) {
        $resp["errors"][] = $dt_fields["start"]["lbl"] . " must be before " . $dt_fields["end"]["lbl"];
      }
    }
  }
  if (! $resp["errors"]) {
    // Finally search.
    $res = search_for_jobs($conn, $d_format, $f["start"], $f["end"], $f["filled"]);
    $errors = array_merge($errors, $res[0]);
    $resp["jobs_rows"] = $res[1];
  }
  
  echo json_encode($resp);
} // End of POST request processing.
  


?>