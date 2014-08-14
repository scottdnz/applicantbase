<?php 
	/**
   * This file contains the view instructions to render the Logout web page.
   * 
   * @author Scott Davies
   * @version 1.0
   * @package log_in
   */
	
	
	require("../lib/smarty_start.php");
	require("../lib/common_lib.php");
	
	
	$page_title = "Log Out";
	$show_head_menu = 0;
	$err_arr = array();
	$test_arr = array();
	$extra_css_list = array("css/common.css");
	$extra_js_list = array("js/common.js");
	
	$cookies = array("PHPSESSID");
	$session_vals = array("user_name", "password");
	session_start();
	$authorised_arr = check_auth();
	
	if ($authorised_arr[1] > 0) {
	  /* User is logged in. Set the auth cookies' expiration dates to one hour
	   * ago, i.e. remove cookies. */
	  foreach ($cookies as $ck) {
	    setcookie($ck, "", time() - 3600, "/", "", 0);
	    $test_arr[] = "Should have unset cookie";
	  }
	  // Get rid of the user session on the server.
	  session_destroy();
	  foreach ($session_vals as $s_val) {
	    unset($_SESSION[$s_val]);
	  }
	}
	
	
	$smarty->assign("title", $page_title);
	$smarty->assign("extra_css", $extra_css_list);
	$smarty->assign("extra_js", $extra_js_list);
	$smarty->assign("error_arr", $err_arr);
	$smarty->assign("test_arr", $test_arr);
	
	// GET request.
	$smarty->assign("show_head_menu", $show_head_menu);
	$smarty->display("logout.html");