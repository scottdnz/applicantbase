<?php 
	/**
	 * This file contains functions common to all the views files.
	 */


	/**
	* For a form input field, checks whether it is in the $_POST array keys.
	* If the field is, checks that its value is not NULL or empty. If it passes
	* all that, returns True.
	* @param $inp_name
	* @return int $srch_posted;
	*/
	function is_text_field_posted($inp_name) {
	  $srch_posted = False;
	  if (array_key_exists($inp_name, $_POST)) {
	    // Key is in $_POST array;
	    if (isset($_POST[$inp_name])) {
	      // Value is not Null.
// 	      if (strlen($_POST[$inp_name]) > 0) {
// 	        // Length is positve
// 	        $srch_posted = True;
// 	      }
	    	$srch_posted = True;
	    }
	  }
	  return $srch_posted;
	}
	
	
	/**
	* Checks that the two authorisation cookies and session variables have been
	* set. Also check that they match. Returns a result of 1 or 0.
	* @return int $checkVal
	*/
	function check_auth() {
	  $check_val = 0;
	  $test_strg = "";
// 	  $test_strg .= "session_id: " . session_id() . "<br />";
// 	  $test_strg .= "sess cookie val: " . filter_input(INPUT_COOKIE, "PHPSESSID", 
//FILTER_SANITIZE_STRING) . "<br />";
	  while (1) {
	    // Check all client-side cookies exist.
	    if ( (! isset ($_COOKIE["PHPSESSID"])) ) {
	      // && (! isset(SID))
	      $test_strg .= "Auth Cookie missing";
	      break;
	    }
	    if (session_id() != filter_input(INPUT_COOKIE, "PHPSESSID", 
                                       FILTER_SANITIZE_STRING)) {
	      $test_strg .= "Auth Cookie is incorrect<br />";
	      break;
	    }
// 	    if (session_id() == "") {
// 	      session_start(addslashes($_COOKIE["PHPSESSID"]));
// 	      $test_strg .= "Session started. ";
// 	    }
	    $check_val = 1;
	    break;
	  }
	  return array($test_strg, $check_val);
	}
?>