<?php
/**
 * This file processes post requests from the Create Applicant page.
 * 
 * @author Scott Davies
 * @version 1.0
 * @package create_applicant_process
 */


require("../lib/smarty_start.php");
require("../lib/db_funcs.php");
require("../lib/common_lib.php");
require("../lib/sanitize.php");
require("../lib/validate.php");


/**
 * Checks that a form input string matches a designated pattern.
 * 
 * @param string $val
 * @param string $f_type
 * @return boolean $res
 */
function is_bad_input($val, $f_type) {
  $res = FALSE;
  if ($f_type == "phone") {
    $val = substr($val, 0, 20);
    $regx_phone_loose = '/^[0-9 \-\+]{7,20}$/i';
    if (! preg_match($regx_phone_loose, $val)) {
      $res = TRUE;
    }
  }
  else if ($f_type == "email") {
    if (! filter_var($val, FILTER_VALIDATE_EMAIL)) {
      $res = TRUE;
    }
  }
  return $res;
}


/**
 * 
 * @param array $c_arr
 * @param  $fields
 */
function check_compulsory_fields($c_arr, $fields) {
  $err_arr = array();
  if (count($c_arr) > 1) {
    // Only one field in the group is compulsory. Any field.
    $one_filled = FALSE;
    foreach($c_arr as $c_field) {
      if (strlen($fields[$c_field]) > 0) {
        $one_filled = TRUE;
        break;
      }
    }
    if (! $one_filled) {
      $msg = "At least one of these fields must be filled in: ";
      $msg .= implode(", ", $c_arr) . ". ";
      $err_arr[] = $msg;
    }
  }
  // A single compulsory field.
  else {
    foreach($c_arr as $c_field) {
      if (strlen($fields[$c_field]) < 1) {
        $err_arr[] = "Field " . $c_field . " is missing or invalid. ";
      }
    }
  }
  return $err_arr;
}


/**
 * Checks that input fields are valid in the step 1 page view.
 * 
 * @param array $fields
 */
function check_step1_fields($fields) {
  $err_arr = array();
  $test_arr = array();
  $compulsory_fields = array(array("firstName"),
                              array("surname"),
                              array("phoneHome", "phoneWork", "phoneMobile"),
                            );
  
  if (! ($fields["positionApp"] == "1")) {
//     array_push($compulsory_fields, 
    $compulsory_fields[] = array("positionLink");
    $compulsory_fields[] = array("applicationSrc"); 
    $compulsory_fields[] = array("applicationDate");
  }
  $phone_fields = array("phoneHome", "phoneWork", "phoneMobile");
  $email_fields = array("emailHome", "emailWork");
  // Conditional field extra.
//   if ($fields["positionApp"] > 1) {
//     $compulsory_fields[] = array("positionLink");
//   }
  // Check compulsory fields filled in.
  foreach($compulsory_fields as $c_arr) {
    $err_arr = array_merge($err_arr, check_compulsory_fields($c_arr, $fields));
  } 
  // Check for invalid phone fields.    
  foreach($phone_fields as $val) {
    $res = is_bad_input($fields[$val], "phone");
//     if (! $res) {
//       $test_msg .= "false" . "\n"; 
//     }
//     else {
//       $test_msg .= "true" . "\n";
//     }
    if ( (strlen($fields[$val]) > 0) && (
                                      is_bad_input($fields[$val], "phone")) ) {
      $err_arr[] = "Field " . $val . " is an invalid phone number. ";
    }
  }
  // Check for invalid email fields.
  foreach($email_fields as $val) {
    if ( (strlen($fields[$val]) > 0) && (
                                      is_bad_input($fields[$val], "email")) ) {
      $err_arr[] = "Field " . $val . " is an invalid email address. ";
    }
  }
//   $label, $date_vbl, $compulsory
  $res = validate_date("Application Date", $fields["applicationDate"], FALSE);
  if ($res[0]) {
    $err_arr[] = $res[0];
  }
//   $test_arr = array($compulsory_fields, "k" . $fields["positionApp"] . "k");
  return array($err_arr, $test_arr);  
}


/**
 * Takes a string in format "dd/mm/yyyy" and converts to a string in format
 * "yyyy-mm-dd hh:mm:ss".
 * 
 * @param string $dt_strg
 * @return string
 */
function conv_to_datetime_strg($dt_strg) {
  $date_arr = explode("/", $dt_strg);
  $day = (int)$date_arr[0];
  $month = (int)$date_arr[1];
  $year = (int)$date_arr[2];
  $dt = new DateTime();
  $dt->setDate($year, $month, $day);
  return $dt->format("Y-m-d H:i:s");
}


/**
 * Runs SQL queries to save the inputs.
 * Query format:
 * insert into applicant (
   first_name, surname, phone_home, phone_work, phone_mobile, email_home, 
   email_work, flag_future_id, narrative, position_applied_for_id) values (
   varchar (32), varchar (32), varchar (15), varchar (15), varchar (13),
   varchar (64), varchar (64), int, text, int);
   
    
 * 
 * @param mysqli connection object $conn
 * @param array $fields
 * @return array $err_arr
 */
function save_step1_fields($conn, $fields) {
  $err_arr = array();
  
  $fields["applicationDate"] = conv_to_datetime_strg($fields["applicationDate"]);
  $len_filled_fields = 0;
  
  $contact_fields = array("phoneHome" => "phone_home"
      "phoneWork" => "phone_work"
      "phoneMobile" => "phone_mobile"
      "emailHome" => "email_home"
      "emailWork" => "email_work");
  
  while (TRUE) {
    // Add Applicant sql.
    $sql = "insert into applicant (";
    $sql .= "first_name, surname";
    
    foreach($contact_fields as $input_f => $db_f) {
      if (strlen($fields[$input_f]) > 0) {
        $sql .= ", " . $db_f;
        $len_filled_fields++;
      }
    }
//     $sql .= "phone_home, phone_work, phone_mobile, ";
//     $sql .= "email_home, email_work";
    $sql .= ") values (";
    $sql .= "\"" . $fields["firstName"] . "\", ";
    $sql .= "\"" . $fields["surname"] . "\"";
    
    foreach($contact_fields as $input_f => $db_f) {
      if (strlen($fields[$input_f]) > 0) {
        $sql .= ", \"" . $fields[$input_f] . "\"";
      }
    }
    
//     $sql .= "\"" . $fields["phoneHome"] . "\", ";
//     $sql .= "\"" . $fields["phoneWork"] . "\", ";
//     $sql .= "\"" . $fields["phoneMobile"] . "\", ";
//     $sql .= "\"" . $fields["emailHome"] . "\", ";
//     $sql .= "\"" . $fields["emailWork"] . "\"";
    $sql .= ");";
    $res = run_modify_query($conn, $sql);
    if($res[0]) {
      $err_arr[] = $res[0];
      break;
    }
    // Sql to find id of applicant added.
    $sql = "select id from applicant where ";
    $sql .= "first_name = \"" . $fields["firstName"] . "\" ";
    $sql .= "and surname = \"" . $fields["surname"] . "\" ";
    $sql .= "and (";

    $cntr = 0;
    foreach($contact_fields as $input_f => $db_f) {
      if (strlen($fields[$input_f]) > 0) {
        $sql .= $db_f . "= \"" . $fields[$input_f] . "\" ";
        if ($cntr < $len_filled_fields - 1) {
          $sql .= "or ";
        }
        $cntr++;
      }
    }
    $sql .= ");";
//     $sql .= "phone_home = \"" . $fields["phoneHome"] . "\" ";
//     $sql .= "or phone_work = \"" . $fields["phoneWork"]  . "\" ";
//     $sql .= "or phone_mobile = \"" . $fields["phoneMobile"] . "\" ";
//     $sql .= "or email_home = \"" . $fields["emailWork"] . "\" ";
//     $sql .= "or email_work = \"" . $fields["emailHome"] . "\"
    
    $res = run_query($conn, $sql);
    if($res[0]) {
      $err_arr[] = $res[0];
      break;
    }
    $applicant_id = $res[1][0]["id"];
    // Add Application sql;
    $sql = "insert into application (";
    $sql .= "applicant_id, application_source_id, application_date";
    if ($fields["positionLink"]) {
      $sql .= ", position_applied_for_id, job_id";
    }
    $sql .= ") values (";
    $sql .= $applicant_id . ", ";
    $sql .= $fields["applicationSrc"] . ", ";
    $sql .= "\"" . $fields["applicationDate"] . "\"";
    if ($fields["positionLink"]) {
      $sql .=  ", " . $fields["positionApp"] . ", ";
      $sql .= $fields["positionLink"];
    }
    $sql .= ");";
    $res = run_modify_query($conn, $sql);
    if($res[0]) {
      $err_arr[] = $res[0];
      break;
    }
    break;
  }
  return $err_arr;
}


//       status_shortlisting_id
//       status_screening_id
//       interview_id
//       reject_notification_sent_id


/**
 * Processes input form fields for the stage 1 page view.
 * 
 * @param mysqli connection object $conn
 */
function process_step_1($conn) {
  // Step 1.
  $error_arr = array();
  $conf_arr = array();
//   $test_arr = array();
  // Set some default values.
  $fields = array("firstName" => "",
      "surname" => "",
      "phoneHome" => "",
      "phoneWork" => "",
      "phoneMobile" => "",
      "emailHome" => "",
      "emailWork" => "",
      "positionApp" => "",
      "positionLink" => "",
      "applicationSrc" => "",
      "applicationDate" => ""
  );
  $exc_chars = array("+", "-");
  while (TRUE) {
    foreach($fields as $k => $v) {
      $fields[$k] = sanitize_string(filter_input(INPUT_POST, $k,
          FILTER_SANITIZE_STRING),
          $excluded=$exc_chars);
    }
    $res = array_merge($error_arr, check_step1_fields($fields));
    
    $error_arr = $res[0];
    // Testing
    $compulsory_fields = $res[1][0];
    $position_app = $res[1][1];
    //     $test_arr[] = $fields;
    if (count($error_arr) > 0) {
      break;
    }
    $error_arr = array_merge($error_arr, save_step1_fields($conn, $fields));
    if (count($error_arr) < 1) {
      $conf_arr[] = "Data successfully saved.";
    }
    break;
  } // End of while.
  
  $json_arr = array("errors" => $error_arr,
      "confirmation" => $conf_arr);
//   $test_arr = array("fields" => $fields,
//                      "compulsory_fields" => $compulsory_fields,
//                     "positionApp" => $position_app);
  $json_arr["test_arr"] = $test_arr;
  echo json_encode($json_arr);
}


function process_step_2($conn) {
  
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  while (TRUE) {
    // Check auth.    
//     session_start();
//     $authorised_arr = check_auth();
//     if ($authorised_arr[1] < 1) {
//       $error_arr[] = "You do not appear to be logged in.";
//       break;
//     }
    // Get a DB connection.
    $conn_res = get_connection();
    if (strlen($conn_res[0]) > 0) {
      $error_arr[] = $conn_res[0];
      break;
    }
    // DB connection is ok.
    $conn = $conn_res[1];
    
    if (array_key_exists("btnSaveStep1", $_POST)) {
      // Step 1.
      process_step_1($conn);
    }
    else if (array_key_exists("btnSaveStep2", $_POST)) {
      // Step 1.
      process_step_2($conn);
    }
    break;
  } // End of while.
    
} // End of POST data checking.


?>