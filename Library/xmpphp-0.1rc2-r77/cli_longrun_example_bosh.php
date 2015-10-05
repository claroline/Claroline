<?php

// activate full error reporting
//error_reporting(E_ALL & E_STRICT);

include 'XMPPHP/BOSH.php';

#Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
$conn = new XMPPHP_BOSH('server.tld', 5280, 'username', 'password', 'xmpphp', 'server.tld', $printlog=true, $loglevel=XMPPHP_Log::LEVEL_VERBOSE);
$conn->autoSubscribe();

try {
    $conn->connect('http://server.tld:5280/xmpp-httpbind');
    while(!$conn->isDisconnected()) {
    	$payloads = $conn->processUntil(array('message', 'presence', 'end_stream', 'session_start'));
    	foreach($payloads as $event) {
    		$pl = $event[1];
    		switch($event[0]) {
    			case 'message': 
    				print "---------------------------------------------------------------------------------\n";
    				print "Message from: {$pl['from']}\n";
    				if($pl['subject']) print "Subject: {$pl['subject']}\n";
    				print $pl['body'] . "\n";
    				print "---------------------------------------------------------------------------------\n";
    				$conn->message($pl['from'], $body="Thanks for sending me \"{$pl['body']}\".", $type=$pl['type']);
    				if($pl['body'] == 'quit') $conn->disconnect();
    				if($pl['body'] == 'break') $conn->send("</end>");
    			break;
    			case 'presence':
    				print "Presence: {$pl['from']} [{$pl['show']}] {$pl['status']}\n";
    			break;
    			case 'session_start':
    			    print "Session Start\n";
			    	$conn->getRoster();
    				$conn->presence($status="Cheese!");
    			break;
    		}
    	}
    }
} catch(XMPPHP_Exception $e) {
    die($e->getMessage());
}
