<?php
	/**
	 * This file contains the initial smarty class set up that will be the same
	 * for and used by all PHP views files.
	 */


  // Full path to Smarty.class.php
  require("../smarty/libs/Smarty.class.php");
  require("config.php"); // $website_root, $smarty_dir, $static_path 
  
  
  /**
   * This class extends the Smarty security class and overrides some settings.
   */
  class AppBase_Security_Policy extends Smarty_Security {
  	// Set allowed php functions.
  	public $php_functions = array("isset", "empty", "count", "sizeof",
  			"in_array", "is_array", "time", "nl2br",
  			"strlen");
  	// Remove PHP tags.
  	public $php_handling = Smarty::PHP_REMOVE;
  	public $trusted_dir = array("../smarty/templates/",
  			"../smarty/templates_c");
  	public $streams = NULL;
  	public $allow_constants = FALSE;
  	public $allow_super_globals = FALSE;
  }
  
  
  $smarty = new Smarty();
//   $smarty->debugging = true;
  // Turn off caching while testing & repeatedly uploading:
  //$smarty->caching = true;
  //$smarty->cache_lifetime = 120;
  
  $smarty->setTemplateDir($smarty_dir . "templates");
  $smarty->setCompileDir($smarty_dir . "templates_c");
  //$smarty->setCacheDir($smarty_dir . "cache");
  $smarty->setConfigDir($smarty_dir . "configs");
  $smarty->assign("static_path", $static_path);
?>
