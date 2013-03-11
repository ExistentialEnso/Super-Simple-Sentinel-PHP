<?php
/**
* Super Simple Sentinel (PHP)
* @author Thorne Melcher <tmelcher@portdusk.com>
* @copyright Thorne Melcher 2013
* @license MIT
* @version 0.4
*
* Tweak the $config variables below, and just require_once() this file on any password-protected page before any data is sent.
*/

/* BEGIN CONFIGURATION VARIABLES */

// Change this to show what will appear in the authorization popup.
$config['app_name'] = "Your App";

// Change this to show what message will appear when they hit "cancel."
$config['not_authorized_message'] = "You are not authorized to view this page.";

// Changes whether or not usernames are case sensitive.
$config['usernames_case_sensitive'] = false;

// Sets how many times the user can attempt to login before it shows the error page
$config['maximum_login_attempts'] = 6;

// Sets how long (in seconds) to force the user to wait to try login again after hitting the number of attempts above
$config['lockout_time'] = 60;

// A one-parameter hash function to run passwords through. Leave null to not hash passwords.
$config['hash_function'] = null; //e.g. "sha1", "md5", or even a custom function!

// Define username/password combinations (as many as you want as sub arrays)
// NOTE: If hashing is turned on (by setting a function above, the passwords need to be the hashed values!
$config['accounts'] = array(
  array('username'=>'jdoe', 'password'=>'abc'), 
  array('username'=>'gsmith', 'password'=>'qwerty99')
);

// Example, alternative database integration:
/*
$db = new \mysqli("localhost", "root", "password", "database_name");
$result = $db->query("SELECT username, password FROM users");
$config['accounts'] = array();
while($account = $result->fetch_assoc()) {
  $config['accounts'][] = $account;
}

/* END CONFIGURATION VARIABLES */

// 1. Ensure the session is setup properly.
if(session_id() == '') session_start();
if(!isset($_SESSION['failures'])) $_SESSION['failures'] = 0;

// 2. Handle them having a lockout time set (be it expired or not)
if(isset($_SESSION['lockout_until_time'])) {
  if($_SESSION['lockout_until_time'] < time()) {
    $_SESSION['failures'] = 0;
    unset($_SESSION['lockout_until_time']);
  } else {
    echo $config['not_authorized_message'];
    exit;
  }
}

// 3. Determine which string function to use (based on case sensitivity settings).
$cmp_function = ($config['usernames_case_sensitive']) ? "strcmp" : "strcasecmp";

// 4. Verify login information, clearing out any invalid login info in the process
if(isset($_SERVER['PHP_AUTH_PW']) && isset($_SERVER['PHP_AUTH_USER'])) {
  $acc_found = false;
  $pw = $_SERVER['PHP_AUTH_PW'];
  
  // Hash the password, if necessary
  if($config['hash_function'] != null) {
    $pw = $config['hash_function']($_SERVER['PHP_AUTH_PW']);
  }

  // Find the account and check its password
  foreach($config['accounts'] as $acc) {
    if($cmp_function($acc['username'], $_SERVER['PHP_AUTH_USER']) == 0) {
      $acc_found = true;
    
      if($pw != $acc['password']) {
        logout(); // The password was wrong, we need to clear out any data.
        fail(); // Notifies our velocity system of the failure
      }
    }
  }
  
  // If no account was found, the username was invalid.
  if(!$acc_found) {
    logout(); // Clear out any login data
    fail(); // Notifies our velocity system of the failure
  }
  
} else if (isset($_SERVER['PHP_AUTH_PW']) || isset($_SERVER['PHP_AUTH_USER'])) {
  // We don't call fail(), since this isn't technically a failure.
  logout(); // Somehow, only user or password is set, we need to clear out the data.
}

// 5. Require auth if no login information set
if (!isset($_SERVER['PHP_AUTH_PW'])) {
  header('WWW-Authenticate: Basic realm="'.$config['app_name'].'"');
  header('HTTP/1.0 401 Unauthorized');
  echo $config['not_authorized_message'];
  exit;
}

/* BEGIN HELPER FUNCTIONS */

// Counts the failure to help prevent repeat attempts
function fail() {
  global $config;

  $_SESSION['failures']++;
  
  // Handle them potentially hitting the maximum 
  if($_SESSION['failures'] >= $config['maximum_login_attempts']) {
    echo $config['not_authorized_message'];
    $_SESSION['lockout_until_time'] = time() + $config['lockout_time'];
    exit;
  }
}

// Flushes out any login information
function logout() {
  unset($_SERVER['PHP_AUTH_PW']);
  unset($_SERVER['PHP_AUTH_USER']);
}