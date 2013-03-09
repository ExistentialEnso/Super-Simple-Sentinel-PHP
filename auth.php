<?php
/**
* Super Simple Sentinel (PHP)
* @author Thorne Melcher <tmelcher@portdusk.com>
* @copyright Thorne Melcher 2013
* @version 0.2
*
* Tweak the $config variables below, and just require_once() this file on any password-protected page before any data is sent.
*/

// Change this to show what will appear in the authorization popup.
$config['app_name'] = "Your App";

// Change this to show what message will appear when they hit "cancel."
$config['not_authorized_message'] = "You are not authorized to view this page.";

// Don't mess with this line to avoid causing errors in some PHP configurations
$config['accounts'] = array();

// Add to the array sub-arrays with username/password values.
$config['accounts'][] = array('username'=>'jdoe', 'password'=>'abc123');
$config['accounts'][] = array('username'=>'gadams', 'password'=>'qwerty');

// Verify login information
if(isset($_SERVER['PHP_AUTH_PW']) && isset($_SERVER['PHP_AUTH_USER'])) {
  $acc_found = false;

  foreach($config['accounts'] as $acc) {
    if($acc['username'] == $_SERVER['PHP_AUTH_USER']) {
	  if($_SERVER['PHP_AUTH_PW'] != $acc['password']) {
	    logout(); //The password was wrong, we need to clear out any data.
	  } else {
	    $acc_found = true;
	  }
	}
  }
  
  if(!$acc_found) {
	logout(); // No account was found, we also need to clear out any data
  }
} else if (isset($_SERVER['PHP_AUTH_PW']) || isset($_SERVER['PHP_AUTH_USER'])) {
  logout(); // Somehow, only user or password is set, we need to clear out the data.
}

// Require auth if the user isn't logged in
if (!isset($_SERVER['PHP_AUTH_PW'])) {
  header('WWW-Authenticate: Basic realm="'.$config['app_name'].'"');
  header('HTTP/1.0 401 Unauthorized');
  echo $config['not_authorized_message'];
  exit;
}

// Flushes out any login information
function logout() {
  unset($_SERVER['PHP_AUTH_PW']);
  unset($_SERVER['PHP_AUTH_USER']);
}