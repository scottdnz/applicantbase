<?php
	/**
	 * This file ...
	 */


  //$_POST["attachFile"];

  /* Plan:
   * Check if overwrite selected, if so, skip to end.
   * Check name fields filled in. If not, return fail.
   * Check if candidate already exists in db. If so, skip to process upload file step.
   * Store/update name fields from form.
   * Create new applicant. Store name details in DB, get unique applicant id.
   * Make an upload subdirectory for new candidate with applicant id. 
   * Process upload file. If invalid, return fail.
   * If file is already in directory, ask if want to overwrite? Return with message.
   * Save & move the file to the uploads applicant directory.
   * Send JSON back = success / fail / message
   */

  
  
  $file_dir = "";
  if (is_uploaded_file($file_array["tmp_name"])) {
    move_uploaded_file($file_array["tmp_name"], $file_dir . $file_array["name"]);
  }


?>