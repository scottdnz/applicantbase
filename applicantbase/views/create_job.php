<?php
/**
 * This file contains the view instructions to render the Jobs List web page.
 * 
 * @author Scott Davies
 * @version 1.0
 * @package jobs_list
 */
	
	
require("../lib/smarty_start.php");
require("../lib/db_funcs.php");
require("../lib/common_lib.php");
require("../lib/sanitize.php");
require("../lib/lib_create_job.php");
	
 	
/**
 * Main processing... 
 */
$page_title = "Create a New Job Record";
$show_head_menu = 1;
$error_arr = array();
$conf_arr = array();
// $test_arr = array();
$extra_css_list = array("js/jqueryui/themes/base/jquery.ui.core.css", 
	                    "js/jqueryui/themes/base/jquery.ui.theme.css",
	                    "js/jqueryui/themes/base/jquery.ui.datepicker.css",
						"css/common.css",
						"js/uEditor/uEditor.css"
                       );
$extra_js_list = array("js/jquery.js",
                    "js/jqueryui/ui/minified/jquery.ui.core.min.js", 
					"js/jqueryui/ui/minified/jquery.ui.datepicker.min.js",
					"js/common.js",
					"js/uEditor/uEditor.js",
					"js/create_job.js"
					 );
$applicants_list = array();
// Posted fields.
$job_title = "";
$date_started = "";
$description = "";
$ad_source = "";
$filled = 0;
$app_filled_by = 0;
$date_filled = "";

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
// DB connection is ok.
$conn = $conn_res[1];
	
	
// Check for POST request. ***********************************************
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $main_inputs = array("jobTitle", "dateStarted", "description", "adSource",
  										"filled");
  // These are only needed if extra fields are conditionally displayed.
  $extra_inputs = array("appFilledBy", "dateFilled");
  while (TRUE) {
  	// Check that all form input fields are received.
  	foreach ($main_inputs as $input) {
      if (! is_text_field_posted($input)) {
      	$error_arr[] = "The input field '" . $input . "' is missing. ";
      }
  	}
  	if (count($error_arr) > 0) {
      break;
  	}
  	// Get sanitized main input values.
    $job_title = sanitize_string(filter_input(INPUT_POST, "jobTitle",
                                    FILTER_SANITIZE_STRING));
    $date_started = sanitize_string(filter_input(INPUT_POST, "dateStarted",
                                    FILTER_SANITIZE_STRING));
    $description = filter_input(INPUT_POST, "description", 
                      FILTER_SANITIZE_MAGIC_QUOTES);
//     $description = sanitize_string(filter_input(INPUT_POST, "description",
//                                     FILTER_SANITIZE_MAGIC_QUOTES), 2000,
//                                     $excluded_chars=array("<", ">"));
//                                         FILTER_SANITIZE_STRING));
//     FILTER_SANITIZE_MAGIC_QUOTES), 500, $excluded_chars=array("<", ">"));
// 	  $description = $_POST["description"];
    $ad_source = sanitize_string(filter_input(INPUT_POST, "adSource",
                                              FILTER_SANITIZE_STRING));
    $filled = (int)filter_input(INPUT_POST, "filled", 
                                FILTER_SANITIZE_NUMBER_INT);
	$res = process_main_inputs($job_title, $date_started, $description, 
														$ad_source, $filled);
	if (count($res[0]) > 0) {
      $error_arr = array_merge($error_arr, $res[0]); 
      break;
	}
    $field_vals = $res[1];
// 	$test_arr[] = $field_vals;

    /* If the radio button for "Filled" had the Yes value, check that all form 
     * input fields are received. */
    if ($filled > 0) {
      foreach ($extra_inputs as $input) {
        if (! is_text_field_posted($input)) {
          $error_arr[] = "The input field '" . $input . "' is missing. ";
        }
      }
      if (count($error_arr) > 0) {
        break;
      }
      // Get sanitized extra input values.
      $app_filled_by = (int)sanitize_string(filter_input(INPUT_POST,
  									"appFilledBy", FILTER_SANITIZE_NUMBER_INT));
      $date_filled = sanitize_string(filter_input(INPUT_POST, "dateFilled",
    											  FILTER_SANITIZE_STRING));
      # Validate the extra fields.
      $res = process_extra_inputs($app_filled_by, $date_filled);
      if (count($res[0]) > 0) {
        $error_arr = array_merge($error_arr, $res[0]);
        break;
      }
      $extra_vals = $res[1];
      $field_vals["app_filled_by"] = $extra_vals["app_filled_by"];
      $field_vals["date_filled"] = $extra_vals["date_filled"];
    } // End of if.

    $sql = get_insert_job_sql($field_vals);
    $res_arr = run_modify_query($conn, $sql);
    if (strlen($res_arr[0]) > 0) {
      $error_arr[] = $res_arr[0];
      break;
    }
    $conf_arr[] = "Your job record was successfully added. ";
    break;
  } // End of while.
	  
  $json_arr = array("errors" => $error_arr,
					"confirmation" => $conf_arr);
  // 	  $json_arr["test_arr"] = $test_arr;
    echo json_encode($json_arr);
} // End of POST data checking.
	
	
else {
  // GET request. ********************************************************
  while (TRUE) {
  	// Query for applicant table.
  	$sql = "select id, first_name, surname from applicant;";
  	$res_arr = run_query($conn, $sql);
  	if (count($res_arr[0]) > 0) {
  	  $error_arr[] = $res_arr[0];
  	  break;
  	}
  	if (count($res_arr[1]) > 0) {
  	  $applicants_list = $res_arr[1];
  	}
  	break;
  } // End of while.
  $smarty->assign("title", $page_title);
  $smarty->assign("show_head_menu", $show_head_menu);
  $smarty->assign("extra_css", $extra_css_list);
  $smarty->assign("extra_js", $extra_js_list);
  $smarty->assign("error_arr", $error_arr);
  $smarty->assign("applicants_list", $applicants_list);
//       $smarty->assign("test_arr", $test_arr);
  
  $smarty->display("create_job.html");
} // End of GETT data checking.
	
?>