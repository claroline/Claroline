<?php

/**
 * @file: XMPPHP Cli example
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

session_start();
header('content-type', 'plain/text');

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
$conn->autoSubscribe();

try {

  if (isset($_SESSION['messages'])) {

    foreach ($_SESSION['messages'] as $message) {
      echo $message;
      flush();
    }
  }
  else {
    $_SESSION['messages'] = array();
  }

  $conn->connect('http://server.tld:5280/xmpp-httpbind', 1, true);
  $events   = array('message', 'presence', 'end_stream', 'session_start', 'vcard');
  $payloads = $conn->processUntil($events);

  foreach ($payloads as $result) {

    list($event, $data) = $result;

    if (isset($data)) {
      extract($data);
    }

    switch($event) {

      case 'message':

        if (!$body) {
          break;
        }

        $cmd  = explode(' ', $body);
        $msg  = str_repeat('-', 80);
        $msg .= "\nMessage from: $from\n";

        if (isset($subject)) {
          $msg .= "Subject: $subject\n";
        }

        $msg .= $body . "\n";
        $msg .= str_repeat('-', 80);
        echo "<pre>$msg</pre>";

        if (isset($cmd[0])) {

          if ($cmd[0] == 'quit') {
            $conn->disconnect();
          }

          if ($cmd[0] == 'break') {
            $conn->send('</end>');
          }
        }
        $_SESSION['messages'][] = $msg;
        flush();
        break;

      case 'presence':

        echo "Presence: $from [$show] $status\n";
        break;

      case 'session_start':

        echo "Session start\n";
        $conn->getRoster();
        $conn->presence('Quasar!');
        break;
    }
  }
} catch(XMPPHP_Exception $e) {
    die($e->getMessage());
}

$conn->saveSession();

echo '<img src="http://xmpp.org/images/xmpp.png" onload="window.location.reload()" />';
