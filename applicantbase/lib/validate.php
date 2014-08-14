<?php
/**
 * This file contains for validating input fields values.
 * 
 * @author Scott Davies
 * @version 1.0
 * @package validate
 */


/**
 * Validates that a string field's data is correct.
 * message string.
 * @param string $label
 * @param string $vbl
 * @param boolean $compulsory
 * @return string $errors
 * @return string $strg_vbl
 */
function validate_string($label, $strg_vbl, $compulsory, $text_limit=50, 
                        $reset_val="", $allowed_vals=array()) {
  $errors = "";
  // Try converting the value to unicode
  while (True) {
    try {
      $strg_vbl = (string)$strg_vbl;    
      if (strlen($strg_vbl) > $text_limit) {
        $errors .= "String field " . $label . " is too long and may be ";
        $errors .= "truncated. The maximum characters allowed is : ";
        $errors .= (string)$text_limit . ". ";
        break;
      }
      $strg_vbl = trim($strg_vbl);
      if ($compulsory) {
        if (strlen($strg_vbl) < 1) {
          $errors .= "Field '" . $label . "' cannot be blank. ";
          break;
        }
      }
      if (count($allowed_vals) > 0) {
        $errors .= "Field '" . $label . "' is not in an accepted value";
        break;
      }
    }
    catch (Exception $e) {
      $errors .= "String field '" . $label . "' contains characters that are ";
      $errors .= "not allowed. ";
    }
    break;
  }	// End of while.
  if (strlen($errors) > 0) {
  	$strg_vbl = $reset_val;
  }
  return array($errors, $strg_vbl);
}


/**
 * Validates that a date string field's data is correct. Default min_date is 
 * 1/1/2011. Default max_date is 1/1/2020. 
 * @param string $label
 * @param string $vbl
 * @param boolean $compulsory
 * @return string $errors
 * @return string $date_vbl
 */
function validate_date($label, $date_vbl, $compulsory, $min_date=1293793200, 
											$max_date=1577790000) {
	$errors = "";
	$now = getdate();
	$reset_val = sprintf("%02d/%02d/%s", $now["mday"], $now["mon"], 
	                      $now["year"]);
	while (TRUE) {
      try {
        if ( (! $compulsory) && (strlen($date_vbl) < 1) ) {
          break;
        }
      	/* Unpack a date from a string in the format "dd/mm/yyyy" */
      	$date_arr = explode("/", $date_vbl);
      	$day = (int)$date_arr[0];
      	$month = (int)$date_arr[1];
      	$year = (int)$date_arr[2];
      	
      	// Check date is valid. checkdate() function is a PHP builtin.
      	if (! checkdate($month, $day, $year)) {
          $errors .= "An invalid date was received for field '" . $label;
          $errors .= "'. It must in the format dd/mm/yyyy.";
          break;
      	}
      	$dt_tstamp = mktime(0, 0, 0, $day, $month, $year);
      	if ( ($dt_tstamp < $min_date) || ($dt_tstamp > $max_date) ) {
          $errors .= "The date was outside the acceptable date range.";
          break;
      	}
			
      }
      catch (Exception $e) {
        $errors .= "An invalid date was received for field '" . $label;
        $errors .= "'. Error: " . $e->getMessage();
      }
      break;
	}
	
	if (count($errors) > 0) {
		$date_vbl = $reset_val;
	}
	return array($errors, $date_vbl);
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


?>