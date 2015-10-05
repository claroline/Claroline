<?php

/**
 * @file: XMPPHP Send message example
 *
 * @info: If this script doesn't work, are you running 64-bit PHP with < 5.2.6?
 */

/**
 * Activate full error reporting
 * error_reporting(E_ALL & E_STRICT);
 *
 * XMPPHP Log levels:
 *
 * LEVEL_ERROR   = 0;
 * LEVEL_WARNING = 1;
 * LEVEL_INFO    = 2;
 * LEVEL_DEBUG   = 3;
 * LEVEL_VERBOSE = 4;
 */

require 'XMPPHP/XMPP.php';

$conf = array(
  'server'   => 'talk.google.com',
  'port'     => 5222,
  'username' => 'username',
  'password' => 'password',
  'proto'    => 'xmpphp',
  'domain'   => 'gmail.com',
  'printlog' => true,
  'loglevel' => XMPPHP_Log::LEVEL_VERBOSE,
);

// Easy and simple for access to variables with their names
extract($conf);

$conn = new XMPPHP_XMPP($server, $port, $username, $password, $proto, $domain, $printlog, $loglevel);

try {
  $conn->connect();
  $conn->processUntil('session_start');
  $conn->presence();
  $conn->message('someguy@someserver.net', 'This is a test message!');
  $conn->disconnect();
} catch(XMPPHP_Exception $e) {
  die($e->getMessage());
}
