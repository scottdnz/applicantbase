<?php
/**
 * This file contains the view instructions to render the Applicant Details 
 * Entry web page.
 * 
 * @author Scott Davies
 * @version 1.0
 * @package app_details_entry
 */
	
	
require("../lib/smarty_start.php");
require("../lib/db_funcs.php");
require("../lib/common_lib.php");
require("../lib/sanitize.php");
require("../lib/validate.php");
require("../lib/lib_create_applicant.php");


$error_arr = array();
$test_arr = array();
$show_head_menu = 1;

session_start();
$authorised_arr = check_auth();
//   if (strlen($authorised_arr[0]) > 0) {
//     $test_arr[] = $authorised_arr[0];
//   }
if ($authorised_arr[1] < 1) {
  header("Location: login.php");
  //$test_arr[] = $authorised_arr[0];
}
//$test_arr[] = $authorised_arr[0];


// Get blank page for a new applicant.
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $page_title = "Applicant Details Entry";
  
  $extra_css_list = array("js/jqueryui/themes/base/jquery.ui.core.css",
  	                    "js/jqueryui/themes/base/jquery.ui.theme.css",
  	                    "js/jqueryui/themes/base/jquery.ui.datepicker.css",
  						"css/common.css",
  						"js/uEditor/uEditor.css",
  	                    "css/create_applicant.css"
  );
  $extra_js_list = array("js/jquery.js",
                        "js/jqueryui/ui/minified/jquery.ui.core.min.js",
  						"js/jqueryui/ui/minified/jquery.ui.datepicker.min.js",
  						"js/uEditor/uEditor.js",
  						"js/common.js",
  						"js/create_applicant.js"
  );

  while (TRUE) {
	// Get a DB connection.
	$conn_res = get_connection();
	if (strlen($conn_res[0]) > 0) {
      $error_arr[] = $conn_res[0];
      break;
	}
	// DB connection is ok.
	$conn = $conn_res[1];
	
	$res = get_new_app_field_vals($conn);
  		
    break;   
  }
} // End of GET processing.



else {
  // POST request. ***********************************************************
  $json_arr = array();
  // Fields to pull out.
//   "firstName"
//   "surname"
//   "phoneHome"
//   "phoneWork"
//   "phoneMobile"
//   "emailHome"
//   "emailWork"
//   "positionApp"
//   "applicationSrc"
//   "applicationDate"
//   "statusShortlisting"
//   "slRejectSent"
//   "statusScreening"
//   "scrRejectSent"
//   "interviewDate"
//   "statusInterview"
//   "intRejectSent"
//   "notes"
//   "flagFuture"
//   "jobsListSubmitBtn"
//   "attachFile"
//   "viewNarrative"
//  narrativeArea *nasty RTE with JS

  $first_name = sanitize_string(filter_input(INPUT_POST, "firstName",
                                FILTER_SANITIZE_STRING));
  $res = validate_string("First Name (s)", $first_name, TRUE);
  if (strlen($res[0]) > 0) {
    $error_arr[] = $res[0];
  }
  $first_name = $res[1];
  
  $surname = sanitize_string(filter_input(INPUT_POST, "surname",
                              FILTER_SANITIZE_STRING));
  $res = validate_string("Surname", $surname, TRUE);
  if (strlen($res[0]) > 0) {
    $error_arr[] = $res[0];
  }
  $surname = $res[1];
  
  $phone_home = sanitize_string(filter_input(INPUT_POST, "phone_home",
                                FILTER_SANITIZE_STRING),
                                array("(", ")", "+", "-"));
  $res = validate_string("Phone (home)", $phone_home, FALSE, $text_limit=22);
  if (strlen($res[0]) > 0) {
    $error_arr[] = $res[0];
  }
  $phone_home = $res[1];
  
  $json_arr = array("first_name" => $first_name,
                    "surname" => $surname,
                    "phone_home" => $phone_home,
                    "errors" => $error_arr);
  
  echo json_encode($json_arr);
  exit();
} // End of POST processing.
	
	
	$smarty->assign("title", $page_title);
	$smarty->assign("show_head_menu", $show_head_menu);
// 	$smarty->assign("hdr_arr", $hdr_arr);
// 	$smarty->assign("hdr_default", $hdr_default);
	$smarty->assign("extra_css", $extra_css_list);
	$smarty->assign("extra_js", $extra_js_list);
	$smarty->assign("error_arr", $res["error_arr"]);
	$smarty->assign("test_arr", $test_arr);
	$smarty->assign("applied_for", $res["applied_for"]);
	$smarty->assign("position", $res["position"]);
	$smarty->assign("application_source", $res["application_source"]);
	$smarty->assign("status_shortlisting", $res["status_shortlisting"]);
	$smarty->assign("reject_notification_sent", $res["reject_notification_sent"]);
	$smarty->assign("status_screening", $res["status_screening"]);
	$smarty->assign("status_interview", $res["status_interview"]);
	$smarty->assign("flag_future", $res["flag_future"]); 
	
	$smarty->display("create_applicant.html");
?>