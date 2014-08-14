<?php 


require("PHPUnit/Autoload.php");

require("../applicantbase/lib/db_funcs.php");
require("../applicantbase/lib/settings.php");


function get_settings() {
  $d_format = "Y-m-d-H-i-s";
  $search_min = DateTime::createFromFormat($d_format, "2013-01-01-00-00-00");
  $search_max = new DateTime();
  
//   echo "search_max: " . $search_max->format($d_format) . "\n";
  
//   $one_day = new DateInterval("P1D");
//   $search_max->add($one_day);
  return array("SEARCH_MIN"=> $search_min,
                "SEARCH_MAX"=> $search_max);
}


function validate_date_strg($dt_strg) {
  $match_strg = "/\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}/";
  $res = (preg_match($match_strg, $dt_strg)) ? true: false;
  return $res;
}


function validate_bool_strg($b) {
  $res = false;  
  $allowed = array("0", "1");
  $b = substr($b, 0, 1);
  $res = (in_array($b, $allowed)) ? true: false; 
  return $res;
}


function validate_dt_objects($dt_fields, $settings) {
  $errors = array();
  foreach ($dt_fields as $dt) {
    if (! is_object($dt)) {
      $errors[] = "Datetime field is not an object. ";
      continue;
    }
    
    $d_format = "d/m/Y H:i:s";
    echo "Date validated: " . $dt->format($d_format) . "\n";
    echo "date min: " . $settings["SEARCH_MIN"]->format($d_format) . "\n";
    
    if ($dt < $settings["SEARCH_MIN"]) {
      $errors[] = "Datetime field is lower than the allowed minimum. ";
      continue;
    }
    elseif ($dt > $settings["SEARCH_MAX"]) {
//       echo "search_max: " . $settings["SEARCH_MAX"]->format($d_format) . "\n";
      $errors[] = "Datetime field is higher than the allowed maximum. ";
    }
  }
  return $errors;
}


function validate_date_range($dt_start, $dt_end) {
  $res = ($dt_start < $dt_end) ? true: false;
  return $res;
}


function test_print($results) {
  $d_format = "d/m/Y H:i:s";
  foreach ($results as $row) {
    echo "****\n";
    echo "id: " . $row["id"] . ", title: " . $row["title"] . ", date_started: ";
//     echo $row["date_started"];
    echo ", filled: " . $row["filled"];
    if  ($row["applicant_filled_by_id"]) {
      echo ", applicant_filled_by_id: " . $row["applicant_filled_by_id"];
    }
    if  ($row["date_filled"]) {
      echo ", date_filled: " . $row["date_filled"];
    }
//     echo "\nMysqli date field return type: " . gettype($row["date_started"]);
    echo "\n";
  }
}


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


function cast_jobs_inputs($d_format, $start_strg=false, $end_strg=false,
    $filled_strg=false) {
  $f = array("start"=> false, "end"=> false, "filled"=> false);
  // Typecast.
  if ($start_strg) {
//     echo "start_strg: " . print_r($start_strg) . "\n";
    $f["start"] = DateTime::createFromFormat($d_format, $start_strg);
  }
  if ($end_strg) {
    $f["end"] = DateTime::createFromFormat($d_format, $end_strg);
  }
  if (count($filled_strg) > 0) {
    $f["filled"] = (int)$filled_strg;
  }
  return $f;
}


function search_for_jobs($conn, $d_format, $start=false, $end=false, $filled=false) {
  $sql = "select id, title, date_started, filled, applicant_filled_by_id, date_filled from job";
  $clauses = array();
  if ($start) {
    $clauses[] = "date_started >= '" . $start->format($d_format) . "'";
  }

  if ($end) {
    $clauses[] = "date_started <= '" . $end->format($d_format) . "'";
  }

  if (count($filled) > 0) {
    $clauses[] = "filled = " . $filled;
//     $clauses[] = "date_filled <= '" . $end->format($d_format) . "'";
  }

  if (count($clauses) > 0) {
    $sql .= " where " . implode(" and ", $clauses) . ";";
  }
  
  echo $sql . "\n";
  $res = run_query($conn, $sql);
//   test_print($res[1]);
  return $res;
}


function ind_search_logic($conn, $d_format, $inp, $settings=false) {
  $res = false;
  $errors = array();
  
  // Validate inputs as strings.
  $errors = array_merge($errors, validate_jobs_inputs_strings($inp));
  if (! $errors) {
    // Convert inputs to proper types.
    $f = cast_jobs_inputs($d_format, $inp["start"]["val"], $inp["end"]["val"],
        $inp["filled"]["val"]);
    // Validate datetime objects.
    $date_keys = array("start", "end");
//     $keys_in_f = array_keys($f);
    $dt_fields = array();
    
    foreach($date_keys as $d_key) {
//       echo "d_key: k" . $d_key . "k\n";
//       echo "keys_in_f: " . implode(",", $keys_in_f) . "\n";
      if (array_key_exists($d_key, $f)) {
//         echo "d_key found: " . $d_key . "\n";
        $dt_fields[$d_key] = $f[$d_key];
      }
    }
//     echo "array_keys(f): " . implode("", array_keys($f));
//     echo print_r($dt_fields);
    
    $errors = array_merge($errors, validate_dt_objects($dt_fields, $settings));
  }
  if (! $errors) {
    // Validate start/end.
    if (count(array_intersect($date_keys, array_keys($inp))) == count($dt_fields)) {
      // Both date fields present.
      if (! validate_date_range($inp["start"]["val"], $inp["end"]["val"])) {
        $errors[] = $dt_fields["start"]["lbl"] . " must be before " . $dt_fields["end"]["lbl"];
      }
    }
  }
  if (! $errors) {
    // Finally search.
    $res = search_for_jobs($conn, $d_format, $f["start"], $f["end"], $f["filled"]);
    $errors = array_merge($errors, $res[0]);
    $res = $res[1];
  }
  return array($errors, $res);
}


class StackTest extends PHPUnit_Framework_TestCase
{
  public function test_job_search() {
    $settings = get_settings();
    $conn_res = get_connection();
    $conn = $conn_res[1];
    $d_format = "Y-m-d-H-i-s";
    
    $end_dt = new DateTime(); // Now.
    $inp = array("start"=> array("lbl"=> "Start Date",
                                "val"=> "2013-01-05-00-00-00"),
                "end"=> array("lbl"=> "End Date",
                            "val"=> $end_dt->format($d_format)),
                "filled"=> array("lbl"=> "Filled",
                                "val"=> "0")
    );
    $res = ind_search_logic($conn, $d_format, $inp, $settings);
    $errors = $res[0];
    $results = $res[1];
//     echo print_r($res);
//     $this->assertEmpty($errors);
    $this->assertNotEmpty($results);
    
    $dir_query_sql = "select id, title, date_started, filled, applicant_filled_by_id, 
date_filled from job where date_started >= '" . $inp["start"]["val"] . "' and 
date_started <= '" . $inp["end"]["val"] . "' and filled = " . $inp["filled"]["val"] . ";";
//     echo "*Direct query sql: \n" . $dir_query_sql . "\n"; 
    $res_direct = run_query($conn, $dir_query_sql);
    $res_direct = $res_direct[1];
//     echo print_r($res_direct);
    $this->assertEquals(count($results), count($res_direct));
  }
  
  
  public function test_job_search_invalid_start_date() {
    $settings = get_settings();
    $conn_res = get_connection();
    $conn = $conn_res[1];
    $d_format = "Y-m-d-H-i-s";   
    $end_dt = new DateTime(); // Now.
    $inp = array("start"=> array("lbl"=> "Start Date",
                                "val"=> "2013-01-abc"),
                "end"=> array("lbl"=> "End Date",
                              "val"=> $end_dt->format($d_format)),
                "filled"=> array("lbl"=> "Filled",
                                "val"=> "0") );
    $res = ind_search_logic($conn, $d_format, $inp, $settings);
    $errors = $res[0];
    $results = $res[1];
    $this->assertNotEmpty($errors);
    $this->assertStringStartsWith("Problem with datetime field", $errors[0]);
    $this->assertEmpty($results);
  }
  
  
  public function test_job_search_start_date_out_of_range_low() {
    $settings = get_settings();
    $conn_res = get_connection();
    $conn = $conn_res[1];
    
    $d_format = "Y-m-d-H-i-s";
    $end_dt = new DateTime(); // Now.
    // Play around to get a start date that is lower than the minimum.
    $two_days = new DateInterval("P2D");
    $start_dt = $settings["SEARCH_MIN"]->format($d_format);
    $start_dt = DateTime::createFromFormat($d_format, $start_dt);
    $start_dt->sub($two_days);
    
    $inp = array("start"=> array("lbl"=> "Start Date",
                                  "val"=> $start_dt->format($d_format)),
                "end"=> array("lbl"=> "End Date",
                              "val"=> $end_dt->format($d_format)),
                "filled"=> array("lbl"=> "Filled",
                                "val"=> "0") );
    $res = ind_search_logic($conn, $d_format, $inp, $settings);
//     echo print_r($res);
    $errors = $res[0];
    $results = $res[1];
    echo "start_dt: " . $start_dt->format($d_format) . "\n";
    echo "error count: " . count($errors) . "\n";
    
    $this->assertNotEmpty($errors);
    $this->assertStringStartsWith("Datetime field is lower", $errors[0]);
    $this->assertEmpty($results);
  }
  
  
  public function test_job_search_start_date_out_of_range_high() {
    $settings = get_settings();
    $conn_res = get_connection();
    $conn = $conn_res[1];
  
    $d_format = "Y-m-d-H-i-s";
    $two_days = new DateInterval("P2D");
    $start_dt = new DateTime(); // Now.
    $start_dt->sub($two_days);
    // Play around to get an end_date that is higher than the maximum.
    $end_dt = $settings["SEARCH_MAX"]->format($d_format);
    $end_dt = DateTime::createFromFormat($d_format, $end_dt);
    $end_dt->add($two_days);

    $inp = array("start"=> array("lbl"=> "Start Date",
                              "val"=> $start_dt->format($d_format)),
                "end"=> array("lbl"=> "End Date",
                              "val"=> $end_dt->format($d_format)),
                "filled"=> array("lbl"=> "Filled",
                                "val"=> "0") );
    $res = ind_search_logic($conn, $d_format, $inp, $settings);
    //     echo print_r($res);
    $errors = $res[0];
    $results = $res[1];
    $this->assertNotEmpty($errors);
    $this->assertStringStartsWith("Datetime field is higher", $errors[0]);
    $this->assertEmpty($results);
  }
    
}


?>