<?php
	/**
	 * This file ...
	 * 
	 * @author Scott Davies
	 * @version 1.0
	 * @package settings.php
	 */

  $init_passwords = array("admin" => "wouldntyouliketoknow");
  
  define("D_FORMAT", "Y-m-d-H-i-s"); // MySQL input datetime string format.
//   $SEARCH_MIN = DateTime::createFromFormat(D_FORMAT, "2013-01-01-00-00-00");
  $SEARCH_MIN = DateTime::createFromFormat(D_FORMAT, "2012-09-01-00-00-00");
  $SEARCH_MAX = new DateTime();  

?>