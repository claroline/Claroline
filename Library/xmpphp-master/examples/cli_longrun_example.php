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

$vcard_request = array();

try {

  $conn->connect();

  while (!$conn->isDisconnected()) {

    $events   = array('message', 'presence', 'end_stream', 'session_start', 'vcard');
    $payloads = $conn->processUntil($events);

     foreach ($payloads as $result) {

      list($event, $data) = $result;

      if (isset($data)) {
        extract($data);
      }

      switch ($event) {

        case 'message':

          if (!$body) {
            break;
          }

          echo str_repeat('-', 80);
          echo "Message from: $from";

          if (isset($subject)) {
            echo "Subject: $subject";
          }

          echo $body;
          echo str_repeat('-', 80);

          $cmd  = explode(' ', $body);
          $body = "Mi no entender! '$body'";
          $conn->message($from, $body, $type);

          if (isset($cmd[0])) {

            if ($cmd[0] == 'quit') {
              $conn->disconnect();
            }

            if ($cmd[0] == 'break') {
              $conn->send('</end>');
            }

            if ($cmd[0] == 'vcard') {

              if (!isset($cmd[1])) {
                $cmd[1] = $conn->user;
              }

              // Take a note which user requested which vcard
              $vcard_request[$from] = $cmd[1];
              // Request the vcard
              $conn->getVCard($cmd[1]);
            }
          }
          break;

        case 'presence':

          echo "Presence: $from [$show] $status\n";
          break;

        case 'session_start':

          echo "Session start\n";
          $conn->getRoster();
          $conn->presence('Quasar!');
          break;

        case 'vcard':

          $deliver = array_keys($vcard_request, $from);
          $msg     = '';

          foreach ($data as $key => $item) {

            $msg .= $key . ': ';

            if (is_array($item)) {
              $msg .= "\n";
              foreach ($item as $subkey => $subitem) {
                $msg .= ' ' . $subkey . ':' . $subitem . "\n";
              }
            }
            else {
              $msg .= $item . "\n";
            }
          }

          foreach ($deliver as $sendjid) {
            unset($vcard_request[$sendjid]);
            $conn->message($sendjid, $msg, 'chat');
          }
          break;
      }
    }
  }
} catch(XMPPHP_Exception $e) {
  die($e->getMessage());
}
