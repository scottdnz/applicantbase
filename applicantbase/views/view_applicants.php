<?php
	/**
	 * This file contains the view instructions for the Applicant Details View
	 * page.  
	 */
	
	
	require("../lib/smarty_start.php");
	require("../lib/db_funcs.php");
	require("../lib/common_lib.php");
	require("../lib/lib_view_applicants.php");
	
	
	$page_title = "View Applicants";
	$show_head_menu = 1;
	
	$error_arr = array();
	$extra_css_list = array("css/common.css", "css/app_details_views.css");
	$extra_js_list = array("js/jquery.js",
                          "js/common.js",
                          "js/app_details_view.js");

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
	
	if ($_SERVER["REQUEST_METHOD"] == "GET") {
	  $res = get_applicants($conn);
	  while (TRUE) {
	    if (strlen($res[0]) > 0) {
	      $error_arr[] = $res[0];
	      break;
	    }
	    $applicants_rows = $res[1];
	    break;
	  } // End of while.
	} // End of GET request processing.
	
	$smarty->assign("title", $page_title);
	$smarty->assign("show_head_menu", $show_head_menu);
	$smarty->assign("hdr_arr", $hdr_arr);
	$smarty->assign("hdr_default", $hdr_default);
	$smarty->assign("extra_css", $extra_css_list);
	$smarty->assign("extra_js", $extra_js_list);
	$smarty->assign("error_arr", $error_arr);
	$smarty->assign("applicants_rows", $applicants_rows);
	
	$smarty->display("view_applicants.html");
?>