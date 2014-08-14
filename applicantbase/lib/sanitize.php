<?php
  /**
  * This file contains functions for safety checking / cleaning of inputs.
  */


  /**
	 * Takes two arrays, and removes anything found in the excluded array from 
	 * patterns array.
	 * @param array $excluded
	 * @param array $patterns
	 * @return array $patterns
   */
  function remove_excluded_from_patterns($excluded, $patterns) {
    if ($excluded) {
      foreach ($excluded as $exc) {
        // array_search($needle, $haystack);
        $posn = array_search($exc, $patterns);
        if ($posn > -1) {
          unset($patterns[$posn]);
        }
      }
    }
    // Reassign keys numerically if any now missing.
    return array_values($patterns);
  }
  
  
  /**
	 * Takes a string, and returns the string with substitutions made for 
    special characters.
	 * @param string $string_val
	 * @param bool $strip_special
	 * @param array $excluded
	 * @return string $string_val 
   */
  function sanitize_special_chars($string_val, $strip_special=FALSE, 
                                  $excluded) {
//   	$tags_allowed = "<ol><li><h1><h2><h3><h4><h5><h6><p><a><strong><em>";
    $patterns = array("&", "<", ">", '"', "*", "'", "%", "(", ")", "+", "-",
                      "\\", ";", ":", ",");
    $replacements = array("&amp;", "&lt;", "&gt;", "&quot;", "&lowast;", 
                          "&#39;", "&#37;", "&#40;", "&#41;", "&#43;", "&#45;", 
                          "&#92;","&#59;", "&#58;", "&#44;");
    // Remove any specified excluded characters (if any) from the patterns list.
    if ($excluded) {
      $patterns = remove_excluded_from_patterns($excluded, $patterns);
    }
    
    for ($i = 0; $i < count($patterns); $i++) {
      if (! $strip_special) {
        $replacement = $replacements[$i];
      }
      else {
        $replacement = "";
      }
      /* Syntax: str_replace($search, $replace, $subject) */
      $string_val = str_replace($patterns[$i], $replacement, $string_val);
    }
    return $string_val;
  }


  /**
   * Takes a string, and returns a sanitized version of the string
   * (x, with anyerrors encountered).  
   * @param string string_val
   * @param int max_chars
   * @param bool $strip_special
   * @param array $excluded
   * @param string $reset_default
   * @return array
   */
  function sanitize_string($string_val, $excluded, $max_chars=50, 
                           $strip_special=FALSE, $reset_default="") {
    $error_strg = "";
    try {
      if (strlen($string_val) < $max_chars) {
        $max_chars = strlen($string_val);
      }
      $string_val = (string)$string_val;
      $string_val = trim($string_val);
      $string_val  = substr($string_val, 0, $max_chars);
      $string_val = sanitize_special_chars($string_val, $strip_special, 
                                            $excluded);
    }
    catch (Exception $e) {
      $string_val = $reset_default;
//       $error_strg .= "Error: problem checking the string entered. ";
//       $error_strg .= $e->getMessage() . ". ";
    }
//     if (strlen($string_val) < 1) {
//       $error_strg .= "Invalid string entered. ";
//     }
//     return array($error_strg, $string_val);
    return $string_val;
  }
          

  /**
	 * Takes an integer, and returns a sanitized version of the integer, with any
   * errors encountered. Returns the int value as long as it is valid and 
   * within the acceptable range. If not, reset it to a default value. 
   * @param int $int_val
   * @param int $max_int
   * @param int $min_int
   * @param int $reset_default
   * @return array
   */ 
  function sanitize_integer($int_val, $max_int, $min_int=0, $reset_default=0) {
    $error_strg = "";
    $err_flag = 0;
    if (! isset($max_int)) {
      $max_int = PHP_INT_MAX;
    }
    while ($err_flag < 1) {
      try {
        $len_max_int = strlen((string)$max_int);
        $result_arr = sanitize_string(addslashes((string)$int_val), 
                                      $len_max_int,
                                      True);
        if (strlen($result_arr[0]) > 0) {
          $error_strg .= "Problem with integer conversion for checking: ";
          $error_strg .= $result_arr[0];
          $err_flag = 1;
        }
      }
      catch (Exception $e) {
        $error_strg .= "Problem with integer conversion for checking. ";
        $error_strg .= $e->getMessage() . ". ";
        $err_flag = 1;
      }
      if (! ctype_digit($test_strg)) {
        $error_strg .= "There is an invalid character in a value meant to ";
        $error_strg .= "be a type integer. ";
        $err_flag = 1;
      }
      try {
        $int_val = (int)$int_val;
      }
      catch (Exception $e) {
        $error_strg .= "There was an invalid integer valued entered. ";
        $error_strg .= $e->getMessage() . ". ";
        $err_flag = 1;
      }
      if (($int_val > $max_int) || ($int_val < $min_int)) {
        $error_strg .= "An integer value was outside the allowed range ";
        $error_strg .= " and has been reset to " . $reset_default . ". ";
        $err_flag = 1;
      }
      break;
    }  // End of while loop.
    if ($err_flag > 0) {
      $int_val = $reset_default;
    }
    return array($error_strg, $int_val); 
  }

    
?>