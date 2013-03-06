<?php
/**
* Super Simple Sentinel (PHP) v0.1
* @author Thorne Melcher <tmelcher@portdusk.com>
*
* Tweak the configuration variables below, and just require_once() this file on any password-protected page before any data is sent.
*/

$config['username'] = "username";
$config['password'] = "password";
$config['app_name'] = "Your App";
$config['not_authorized_message'] = "You are not authorized.";

// Clear out any invalid authentication information. If one is wrong, wipe both.
if(isset($_SERVER['PHP_AUTH_PW']) || isset($_SERVER['PHP_AUTH_USER'])) {
  if($_SERVER['PHP_AUTH_PW'] != $config['password'] || $_SERVER['PHP_AUTH_USER'] != $config['username'] ) {
    unset($_SERVER['PHP_AUTH_PW']);
    unset($_SERVER['PHP_AUTH_USER']);
  }
}

// Require auth if there isn't a valid auth set
if (!isset($_SERVER['PHP_AUTH_PW'])) {
  header('WWW-Authenticate: Basic realm="'.$config['app_name'].'"');
  header('HTTP/1.0 401 Unauthorized');
  echo $config['not_authorized_message'];
  exit;
}