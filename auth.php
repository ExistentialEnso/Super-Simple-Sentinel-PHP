<?php
/**
* Super Simple Sentinel (PHP)
* @author Thorne Melcher <tmelcher@portdusk.com>
* @copyright Thorne Melcher 2013
* @license MIT
* @version 0.3
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

// Sets how long (in seconds) to force the user to wait to login again.
$config['lockout_time'] = 60;

// Don't mess with this line to avoid causing errors in some PHP configurations
$config['accounts'] = array();

// Add to the array sub-arrays with username/password values.
$config['accounts'][] = array('username'=>'jdoe', 'password'=>'abc123');
$config['accounts'][] = array('username'=>'gadams', 'password'=>'qwerty99');

/* END CONFIGURATION VARIABLES */

// Ensure the session is setup properly.
if(session_id() == '') session_start();
if(!isset($_SESSION['failures'])) $_SESSION['failures'] = 0;

if(isset($_SESSION['lockout_until_time'])) {
  if($_SESSION['lockout_until_time'] < time()) {
    $_SESSION['failures'] = 0;
    unset($_SESSION['lockout_until_time']);
  } else {
    echo $config['not_authorized_message'];
    exit;
  }
}

// 1. Determine which string function to use (based on case sensitivity settings).
$cmp_function = ($config['usernames_case_sensitive']) ? "strcmp" : "strcasecmp";

// 2. Verify login information, clearing out any invalid login info in the process
if(isset($_SERVER['PHP_AUTH_PW']) && isset($_SERVER['PHP_AUTH_USER'])) {
  $acc_found = false;

  // Find the account and check its password
  foreach($config['accounts'] as $acc) {
  	if($cmp_function($acc['username'], $_SERVER['PHP_AUTH_USER']) == 0) {
      $acc_found = true;
    
      if($_SERVER['PHP_AUTH_PW'] != $acc['password']) {
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

// 3. Require auth if no login information set
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