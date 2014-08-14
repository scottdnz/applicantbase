<?php 
	/**
   * This file contains functions to deal with encryption / decryption.
   * 
   * @author Scott Davies
   * @version 1.0
   * @package encryption
   */


  /**
  * Returns a random salt key and initialization vector for encryption.
  * @return array
  */
  function get_enc_vals() {
    $salt = "IxX4eu1nfjDsMPsN";
    $iv = "J1hyciVPx9oA3Vex88keLe9bqewpJnWg";
    return array($salt, $iv);
  }
  
  
//   /**
//   * Returns a random salt key and initialization vector, every time, for 
//   * encryption.
//   * @return array
//   */
//   function get_enc_session_vals() {
//     $salt = get_random_strg();
//     $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
//     $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
//     return array($salt, $iv);
//   }
  
  
  /**
   * Takes a plain string password, and returns an encrypted password.
   * @param string $password
   * @return string $enc_password
   */
  function get_enc_password($password) {
    $enc_vals = get_enc_vals();
    $salt = $enc_vals[0];
    $iv = $enc_vals[1];
    $enc_password = encrypt($salt, $password, $iv);
    return $enc_password;
  }
  
  
  /**
   * Takes an encrypted string, and returns and unencrypted string.
   * @param string $password
   * @return string $dec_password
   */
  function get_dec_password($password) {
    $enc_vals = get_enc_vals();
    $salt = $enc_vals[0];
    $iv = $enc_vals[1];
    $dec_password = decrypt($salt, $password, $iv);
    return $dec_password;
  }
  
  
//   /**
//   * Takes a plain string password, and returns an encrypted password.
//   * @param string $password
//   * @return string $enc_password
//   */
//   function get_enc_session_password($password) {
//     $enc_vals = get_enc_session_vals();
//     $salt = $enc_vals[0];
//     $iv = $enc_vals[1];
//     $enc_password = encrypt($salt, $password, $iv);
//     return $enc_password;
//   }
  
  
//   /**
//   * Takes an encrypted string, and returns and unencrypted string.
//   * @param string $password
//   * @return string $dec_password
//   */
//   function get_dec_session_password($password) {
//     $enc_vals = get_enc_session_vals();
//     $salt = $enc_vals[0];
//     $iv = $enc_vals[1];
//     $dec_password = decrypt($salt, $password, $iv);
//     return $dec_password;
//   }
  
  
  /**
   * 
   * @param string $salt
   * @param string $text
   * @param string $iv
   * @return 
   */
  function encrypt($salt, $text, $iv) {
    /* Syntax: mcrypt_encrypt ($cipher, $key, $data, $mode, $iv) */
    $enc_text = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text,
                               MCRYPT_MODE_ECB, $iv);
    return trim(base64_encode($enc_text));
  }
  
  
  function decrypt($salt, $text, $iv) {
    $text = base64_decode($text);
    /* Syntax: mcrypt_decrypt ($cipher, $key, $data, $mode, $iv) */
    $dec_text = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, $text,
                               MCRYPT_MODE_ECB, $iv);
    return trim($dec_text);
  }
  
  
  /**
  * Returns a randomly generated string set to the length value passed in. The
  * string contains only alphanumeric characters.
  * @param int $len_val
  * @return string $rand_strg
  */
  function get_random_strg($len_val=16) {
    $rand_strg = "";
    for ($i = 0; $i < $len_val; $i++) {
      $rand_num = rand(1,62);
      if ($rand_num < 11) {
        // Digits are in ASCII range 48-57.
        $add_val = 47;
      }
      else if ($rand_num < 37) {
        // Capital alphabetic characters are in range 65-90.
        $add_val = 54;
      }
      else {
        // Lower case alphabetic characters are in range 97-122.
        $add_val = 60;
      }
      $rand_num += $add_val;
      $rand_strg .= chr($rand_num);
    }
    return $rand_strg;
  }


?>