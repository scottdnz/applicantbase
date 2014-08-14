<?php 
/**
 * This file contains the view instructions to render the Login web page.
 * 
 * @author Scott Davies
 * @version 1.0
 * @package log_in
 */
	
	
require("../lib/smarty_start.php");
require("../lib/db_funcs.php");
require("../lib/common_lib.php");
require("../lib/sanitize.php");
require("../lib/encryption.php");
	
	
/**
 * Attempts to retrieve two fields from the user table in the DB.
 * @param "database connection object" $conn
 * @param string user_name
 * @return array
 */
function retrieve_password_fields($conn, $user_name) {
  $error_arr = array();
  $results_arr = array();	  
  $sql = "select password_enc, valid from user where username = '";
  $sql .= $user_name . "';";
  $res_arr = run_query($conn, $sql);
  if (count($res_arr[0]) > 0) {
    foreach($res_arr[0] as $err) {
      $error_arr[] = $err;
    }
  }
  else {
    if (count($res_arr[1] > 0)) {
      $results_arr = $res_arr[1][0];
    }
    else {
      $error_arr[] = "No user details found";
    } 
  }
  return array($error_arr, $results_arr);
}

	
/**
 * Main processing...
 */
$page_title = "Log In";
$user_name_repeated = "";
$show_head_menu = 0;
$err_arr = array();
// $test_arr = array();
$extra_css_list = array("css/common.css");
$extra_js_list = array("js/common.js");
// 	$user_name_repeated = "";
if (isset($_POST["enterBtn"])) {
  while (1) {
    if ( (! is_text_field_posted("userName")) || (! is_text_field_posted(
    	      			"uPassword")) ) {
      $err_arr[] = "Field missing.";  	  	      			
      break;  
    }  	  
    $user_name = filter_input(INPUT_POST, "userName", FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, "uPassword", FILTER_SANITIZE_STRING);
    // Get clean versions of user_name and password.
    $user_name = sanitize_string($user_name, FALSE, 50, TRUE);
    $password = sanitize_string($password, FALSE, 50, TRUE);
    
    // Get a DB connection.
    $conn_res = get_connection();
    if (strlen($conn_res[0]) > 0) {
      $error_arr[] = $conn_res[0];
      break;
    }
    // DB connection is ok.
    $conn = $conn_res[1];
    // Will get rid of this and use AJAX / Central_Auth? !!
    /* Retrieve username, encrypted password and valid field from DB. Check 
     * whether the username is still valid. If so, unencrypt the password.
     * Check the unencrypted password against the password submitted from the
     * form. 
     */    
    $res_arr = retrieve_password_fields($conn, $user_name);
    if (count($res_arr[0]) > 0) {
      foreach($res_arr[0] as $err) {
        $err_arr[] = $err;
      }
      break;
    }
    else {
      $results = $res_arr[1];
      if ($results["valid"] < 1) {
        $err_arr[] = $user_name . " is not a valid user name.";
        break;
      }
      $dec_password = get_dec_password($results["password_enc"]);
      if ($password != $dec_password) {
        $err_arr[] = "Incorrect password for user " . $user_name . ".";
        break;
      }
    }
    break;
	} // End of while.
} // End of if.
	
if (count($err_arr) > 0) {
  $user_name_repeated = filter_input(INPUT_POST, "userName", 
                                     FILTER_SANITIZE_STRING);
}  	
  	
$smarty->assign("title", $page_title);
$smarty->assign("extra_css", $extra_css_list);
$smarty->assign("extra_js", $extra_js_list);
$smarty->assign("show_head_menu", $show_head_menu);
$smarty->assign("error_arr", $err_arr);
// $smarty->assign("test_arr", $test_arr);
$smarty->assign("user_name_repeated", $user_name_repeated);
	
// POST request with errors
if (count($err_arr) > 0) {
  $smarty->display("login.html");
  exit();
}
  
/* Successful POST request, so set auth cookies, session encryption vals &
 * redirect. */
// If the user is already logged in, redirect to the app details entry page.
if ( (isset($user_name)) && (isset($password)) ) {
	header("Location: create_applicant.php");
	session_start();
	$_SESSION["user_name"] = $user_name;
	$_SESSION["password"] = $password;
	/* On the client machine a PHPSESSION cookie will be stored by 
	 * default. */
	exit();
}

	
// GET request.
// 	$smarty->assign("show_head_menu", 0);
	$smarty->display("login.html");
?>
