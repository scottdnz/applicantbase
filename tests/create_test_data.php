<?php 


require("../applicantbase/lib/db_funcs.php");


/**************************************************************************
 * Application insert helper functions */

/**
 * Assigns a different date of applying in the past  for each applicant. For 
 * add_test_applications f'n. */
function get_appn_dates_matching_num_applicants($num_applicant_ids, $d_format) {
  $application_dates = array();

  $one_day = new DateInterval('P1D');
  $one_week = new DateInterval('P7D');
  $cur_dt = new DateTime(); // Now
  $cur_dt->sub($one_week);

  for ($i = 0; $i < $num_applicant_ids; $i++) {
    $cur_dt->sub($one_day);
    //     echo $cur_dt->format($d_format) . "\n";
    $application_dates[] = $cur_dt->format($d_format);
  }
  return $application_dates;
}


/** 
 * Performs queries for position_applied_for ids, applicants, application_source 
 * and job. For the add_test_applications f'n. */
function get_extra_application_vals($conn, $d_format) {
  $extra = array();
  // Get the values for position_applied_for = yes/no.
  $sql = "select id, applied_for from position_applied_for where
applied_for in ('yes', 'no') and valid limit 2;";
  $res = run_query($conn, $sql);
  foreach ($res[1] as $row) {
    if ($row["applied_for"] == "yes") {
      $extra["position_app_yes_id"] = $row["id"];
    }
    elseif ($row["applied_for"] == "no") {
      $extra["position_app_no_id"] = $row["id"];
    }
  }
  // Get available applicant ids.
  $sql = "select id from applicant;";
  $res = run_query($conn, $sql);
  $extra["applicant_ids"] = $res[1];
  // Get application sources.
  $sql = "select id, source from application_source where valid;";
  $res = run_query($conn, $sql);
  $extra["application_sources"] = $res[1];
  // Get jobs ids.
  $sql = "select id from job;";
  $res = run_query($conn, $sql);
  $extra["job_ids"] = $res[1];

  $extra["application_dates"] = get_appn_dates_matching_num_applicants(
      count($extra["applicant_ids"]), $d_format);
  return $extra;
}


/**
 * Gets the id value for a valid record in each of these tables:
 * "status_interview", "status_screening", "status_shortlisting",
 * "reject_notification_sent", "flag_future". */
function get_extra_job_applied_for_vals($conn) {
  $extra = array();
  // Get the status and id value from three tables, and store them in a 2d array.
  $similar_statuses = array("status_interview", "status_screening",
      "status_shortlisting");
  foreach ($similar_statuses as $status) {
    $sql = "select id, status from " . $status . " where valid;";
    $res = run_query($conn, $sql);
    foreach ($res[1] as $row) {
      $extra[$status][$row["status"]] = $row["id"];
    }
  }
  // Get the values for reject_notification_sent
  $sql = "select id, sent from reject_notification_sent where valid;";
  $res = run_query($conn, $sql);
  foreach ($res[1] as $row) {
    $extra["reject_notification_sent"][$row["sent"]] = $row["id"];
  }
  // Get the values for flag_future
  $sql =  "select id, flag from flag_future where valid;";
  $res = run_query($conn, $sql);
  foreach ($res[1] as $row) {
    $extra["flag_future"][$row["flag"]] = $row["id"];
  }
  return $extra;
}


/** 
 * Converts the application date string to an object, add x days,
 * then return as a new formatted date string. */
function add_days_to_date_strg($num_days, $date_strg, $d_format) {
  $two_days = new DateInterval("P" . $num_days . "D");
  $interview_dt_obj = DateTime::createFromFormat($d_format, $date_strg);
  $interview_dt_obj->add($two_days);
  return $interview_dt_obj->format($d_format);
}
/***************************************************************************/


/**
 * Takes an array of field values, and returns a SQL command string.
 * Query format: 
 * "insert into job (
	title, date_started, description, ad_source, filled 
	[, applicant_filled_by_id, date_filled]     #if filled 
	) values (
	varchar (64), date, text, varchar (32), bool [, int, date]);
	
 * @param array $f
 * @return string
 */
function build_insert_job_sql($f) {
  $fields_strg = "title, date_started, description, ad_source, filled";
  $values_strg = "'" . $f["job_title"] . "', '";
  $values_strg .= $f["date_started"] . "', '";
  $values_strg .= $f["description"] . "', '";
  $values_strg .= $f["ad_source"] . "', '";
  $values_strg .= $f["filled"]  . "'";
  // If a job is filled, there are two extra fields.
  if ($f["filled"] > 0) {
    $fields_strg .= ", applicant_filled_by_id, date_filled"; 
    $values_strg .= ", '" . $f["app_filled_by"] . "', '";
    $values_strg .= $f["date_filled"] . "'";
  }
  $sql = "insert into job (" . $fields_strg . ") values (" . $values_strg . ");";
  return $sql;
}


/**
 * Query format:
 * insert into applicant (
    first_name, surname, phone_home, phone_work, phone_mobile, email_home,
    email_work, flag_future_id, narrative, position_applied_for_id) values (
        varchar (32), varchar (32), varchar (15), varchar (15), varchar (13),
        varchar (64), varchar (64), int, text, int);
 */
function build_insert_applicant_sql($f) {
  // Build fields string.
  $fields_strg = "first_name, surname"; // Fields always present.
  $values_strg = "'" . $f["first_name"] . "', '" . $f["surname"] . "'";
  $optionals = array("phone_home", "phone_work", "phone_mobile", "email_home", 
      "flag_future_id", "narrative", "position_applied_for_id");
  
  foreach ($optionals as $opt) {
    if (array_key_exists($opt, $f)) {
      $fields_strg .= ", " .  $opt;
      $values_strg .= ", '" . $f[$opt] . "'";
    }
  }
  $sql = "insert into applicant (" . $fields_strg . ") values (" . $values_strg . ");";
  return $sql;
}


/**
 * Query format:
 * insert into interview (date_of, status_interview_id [, notes]) values ( 
 * date, int(11) [, text]);
 */
function build_insert_interview_sql($f) {
  $fields_strg = "date_of, status_interview_id";
  $values_strg = "'" . $f["date_of"] . "', " . $f["status_interview_id"];
  // Only one optional field, "notes"
  if (array_key_exists("notes", $f)) {
    $fields_strg .= ", " .  "notes";
    $values_strg .= ", '" . $f["notes"] . "'";
  }
  $sql = "insert into interview (" . $fields_strg . ") values (" . $values_strg . ");";
  return $sql;
}


/**
 * insert into application (applicant_id, application_source_id, application_date, 
 * position_applied_for_id
 * [, job_id, status_shortlisting_id, status_screening_id, interview_id, 
 * reject_notification_sent_id] 
 * ) values (
 * int(11), int(11), int(11), datetime, int(11) [, int(11), int(11), int(11), int(11)]
 * );
 * 
 */
function build_insert_application_sql($f) {
  $fields_strg = "applicant_id, application_source_id, position_applied_for_id, 
application_date";
  $values_strg = $f["applicant_id"] . ", " . $f["application_source_id"] . ", ";
  $values_strg .= $f["position_applied_for_id"] . ", '" . $f["application_date"] . "'";
  $optionals = array("job_id", "status_shortlisting_id", "status_screening_id", 
      "interview_id", "reject_notification_sent_id");
  
  foreach ($optionals as $opt) {
    if (array_key_exists($opt, $f)) {
      $fields_strg .= ", " .  $opt;
      $values_strg .= ", " . $f[$opt];
    }
  }
  $sql = "insert into application (" . $fields_strg . ") values (" . $values_strg . ");";
  return $sql;
}


function add_test_jobs($conn, $jobs_arr) {
  foreach ($jobs_arr as $job) {
    $sql = build_insert_job_sql($job);
    $res_arr = run_modify_query($conn, $sql);
    if (strlen($res_arr[0]) > 0) {
      echo "Errors: \n" . implode("\n", $res_arr[0]);
    }
    else {
      echo "Your job record was successfully added. \n";
    }
  }
}


function add_test_applicants($conn, $applicants_arr) {
  foreach ($applicants_arr as $appct) {
    $sql = build_insert_applicant_sql($appct);
    $res_arr = run_modify_query($conn, $sql);
    if (strlen($res_arr[0]) > 0) {
      echo "Errors: \n" . implode("\n", $res_arr[0]);
    }
    else {
      echo "Your applicant record was successfully added. \n";
    }
  }
}


function add_test_interview($conn, $application_date_strg, $d_format, $status_id,
    $applicant_id) {
  $f["date_of"] =  add_days_to_date_strg(2, $application_date_strg, $d_format);
  $f["notes"] = "Notes for applicant ". $applicant_id;
  $f["status_interview_id"] = $status_id;
  $sql = build_insert_interview_sql($f);
  $res_arr = run_modify_query($conn, $sql);
  if (strlen($res_arr[0]) > 0) {
    echo "Errors: " . $res_arr[0];
  }
  else {
    echo "Your interview record was successfully added. \n";
  }
}


function set_flag_future_on_applicant($conn, $applicant_id, $ff_vals) {
  // Get a random flag_future key.
  $rnd_flag_ind = rand(0, count(array_keys($ff_vals)) - 1); 
  $rnd_key = array_keys($ff_vals)[$rnd_flag_ind];
  
  $sql = "update applicant set flag_future_id = " . $ff_vals[$rnd_key];
  $sql .= " where id = " . $applicant_id . ";";
  $res_arr = run_modify_query($conn, $sql);
  if (strlen($res_arr[0]) > 0) {
    echo "Errors: " . $res_arr[0];
  }
  else {
    echo "Applicant record updated with flag_future value. \n";
  }
}


// function set_position_applied_on_applicant($conn, $applicant_id, 
//                                                 $position_applied_for_id) {
//   $sql = "update applicant set position_applied_for_id = " . $position_applied_for_id;
//   $sql .= " where id = " . $applicant_id . ";"; 
//   $res_arr = run_modify_query($conn, $sql);
//   if (strlen($res_arr[0]) > 0) {
//     echo "Errors: " . $res_arr[0];
//   }
//   else {
//     echo "Applicant record updated for position_applied_for_id. \n";
//   } 
// }


/**
 * Query format:
 * update job set filled = 1, applicant_filled_by_id = 115, 
 * date_filled = '2013-01-29-08-00-00' where id = 321;
 * 
 * @param unknown $conn
 * @param unknown $applicant_id
 * @param unknown $date_filled
 * @param unknown $dt_format
 * @param unknown $job_id
 */
function update_filled_job($conn, $applicant_id, $date_filled, $d_format, $job_id) {
  // Convert to string. 
  $date_filled = $date_filled->format($d_format);
  $sql = "update job set filled = 1, applicant_filled_by_id = " . $applicant_id; 
  $sql .= ", date_filled = '" . $date_filled . "' where id = " . $job_id . ";";
  
  $res_arr = run_modify_query($conn, $sql);
  if (strlen($res_arr[0]) > 0) {
    echo "Errors: " . $res_arr[0];
  }
  else {
    echo "Job record updated as filled. \n";
  }
}


/**
 * Sets values for each stage of an application. Long because different scenarios.
 * @param unknown $conn
 * @param unknown $d_format
 */
function add_test_applications($conn, $d_format) {
  $extra = get_extra_application_vals($conn, $d_format);
  $extra_job = get_extra_job_applied_for_vals($conn);
//   $positive_shortl_id = $extra_job["status_shortlisting"]["Telephone Interview"];
  $len_app_sources = count($extra["application_sources"]);
  
  for ($i = 0; $i < count($extra["applicant_ids"]); $i++) {
    $f = array(
        "applicant_id"=> $extra["applicant_ids"][$i]["id"], 
        "application_source_id"=> rand(0, $len_app_sources - 1), 
        "application_date"=> $extra["application_dates"][$i],
    );
    // Create 3 records where the applicant hasn't applied for a position.
    if ($i < 3) {
      $f["position_applied_for_id"] = $extra["position_app_no_id"];
    } // All others have applied for a specific job.
    else {
      $f["position_applied_for_id"] = $extra["position_app_yes_id"];
      $random_job_ind = rand(0, count($extra["job_ids"]) - 1);
      $f["job_id"] = (int)$extra["job_ids"][$random_job_ind]["id"];
      
//       set_position_applied_on_applicant($conn, $f["applicant_id"],
//                                                $extra["position_app_yes_id"]);
    }
    
    // User has applied for job, but not decided to phone interview yet.
    // $i == 3
    
    /* User has applied for job, rejected, not phone interview shortlisted.
    * Reject notification not sent. */ 
    if ($i == 4) {
      $f["status_shortlisting_id"] = $extra_job["status_shortlisting"]["Reject"];
      $f["reject_notification_sent_id"] = $extra_job["reject_notification_sent"]["no"];
      set_flag_future_on_applicant($conn, $f["applicant_id"],
                                  $extra_job["flag_future"]);
    }
    
    /* User has applied for job, rejected, not phone interview shortlisted.
     * Reject notification sent. */
    if ($i == 5) {
      $f["status_shortlisting_id"] = $extra_job["status_shortlisting"]["Reject"];
      $f["reject_notification_sent_id"] = $extra_job["reject_notification_sent"]["yes"];
      set_flag_future_on_applicant($conn, $f["applicant_id"],
                                                  $extra_job["flag_future"]);
    }
    
    // All users after this have applied for job, and been phone interview shortlisted.
    if ($i > 5) {
      $f["status_shortlisting_id"] = $extra_job["status_shortlisting"]["Telephone Interview"];
    }
    
    // Not decided on screening after phone interview.
    // $i = 6
    
    // User rejected after phone interview. Notification not sent.
    if ($i == 7) {
      $id = $extra_job["status_screening"]["Reject"];
      $f["status_screening_id"] = $id;
      $f["reject_notification_sent_id"] = $extra_job[
                                         "reject_notification_sent"]["no"];
      set_flag_future_on_applicant($conn, $f["applicant_id"], 
                                                  $extra_job["flag_future"]);
    }
    
    // User rejected after phone interview. Notification sent.
    if ($i == 8) {
      $id = $extra_job["status_screening"]["Reject"];
      $f["status_screening_id"] = $id;
      $f["reject_notification_sent_id"] = $extra_job[
                                        "reject_notification_sent"]["yes"];
    }
    
    // All users after this have been screened & invited to physical interview.
    if ($i > 8) {
      $id = $extra_job["status_screening"]["Invite to Interview"];
      $f["status_screening_id"] = $id;
    }
    
    // After physical interview, undecided
//     $i = 9;
    
    // After physical interview, rejected. Notification not sent.
    if ($i == 10) {
      $status_interview_id = $extra_job["status_interview"]["Reject"];
      add_test_interview($conn, $f["application_date"], $d_format,
                                    $status_interview_id, $f["applicant_id"]);
      // Get id of last inserted interview record.
      $f["interview_id"] = $conn->insert_id;
      $f["reject_notification_sent_id"] = $extra_job[
                                            "reject_notification_sent"]["no"];
      set_flag_future_on_applicant($conn, $f["applicant_id"], $extra_job["flag_future"]);
    }
    
    // After physical interview, rejected. Notification sent.
    if ($i == 11) {
      $status_interview_id = $extra_job["status_interview"]["Reject"];
      add_test_interview($conn, $f["application_date"], $d_format,
      $status_interview_id, $f["applicant_id"]);
      // Get id of last inserted interview record.
      $f["interview_id"] = $conn->insert_id;
      $f["reject_notification_sent_id"] = $extra_job[
                                          "reject_notification_sent"]["yes"];
      set_flag_future_on_applicant($conn, $f["applicant_id"], $extra_job["flag_future"]);
    }
    
    // After physical interview, offered job. Job filled.
    if ($i == 12) {
      $status_interview_id = $extra_job["status_interview"]["Offer"];
      add_test_interview($conn, $f["application_date"], $d_format,
                                    $status_interview_id, $f["applicant_id"]);
      // Get id of last inserted interview record.
      $f["interview_id"] = $conn->insert_id;
      $date_filled = new DateTime();
      update_filled_job($conn, $f["applicant_id"], $date_filled, $d_format, 
                          $f["job_id"]);
    }

    $sql = build_insert_application_sql($f);
    $res_arr = run_modify_query($conn, $sql);
    if (strlen($res_arr[0]) > 0) {
      echo "Errors: " . $res_arr[0];
    }
    else {
      echo "Your application record was successfully added. \n";
    }
  }
}


function setUp($conn) {
  // Clean out tables of test data.
  $tables = array("job", "applicant", "application", "interview");
  foreach($tables as $tbl) {
    $sql = "delete from ". $tbl . " where id;";
    $res_arr = run_modify_query($conn, $sql);
    if (! $res_arr[0]) {
      echo "Deleted existing " . $tbl . " records.\n";
    }  
  }
}


function get_test_data() {
  $json_strg = file_get_contents("./data/jobs_data.json");
  $json_arr = json_decode($json_strg, $assoc=true);
  return $json_arr;
}


function create_test_data_main() {
  $d_format = "Y-m-d-H-i-s";
  
  // Get a DB connection.
  $conn_res = get_connection();
  if (strlen($conn_res[0]) > 0) {
    $error_arr[] = $conn_res[0];
  }
  // DB connection is ok.
  $conn = $conn_res[1];
  
  setUp($conn);
  $json_arr = get_test_data();
  add_test_jobs($conn, $json_arr["jobs"], $d_format);
  add_test_applicants($conn, $json_arr["applicants"], $d_format);
  /* Am application is linked to an applicant, and optionally a job and an interview */
  add_test_applications($conn, $d_format); 
}


create_test_data_main();


?>