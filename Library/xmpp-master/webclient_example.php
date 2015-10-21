<?php
session_start();
header('content-type', 'plain/text');
// activate full error reporting
//error_reporting(E_ALL & E_STRICT);

include 'XMPPHP/BOSH.php';
print "<pre>";

#Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
$conn = new XMPPHP_BOSH('server.tld', 5280, 'user', 'password', 'xmpphp', 'server.tld', $printlog=true, $loglevel=XMPPHP_Log::LEVEL_INFO);
$conn->autoSubscribe();

try {
	if(isset($_SESSION['messages'])) {
		foreach($_SESSION['messages'] as $msg) {
			print $msg;
			flush();
		}
	}
	$conn->connect('http://server.tld:5280/xmpp-httpbind', 1, true);
	#while(true) {
			$payloads = $conn->processUntil(array('message', 'presence', 'end_stream', 'session_start'));
			foreach($payloads as $event) {
				$pl = $event[1];
				switch($event[0]) {
					case 'message': 
						if(!isset($_SESSION['messages'])) $_SESSION['message'] = Array();
						$msg = "---------------------------------------------------------------------------------\n{$pl['from']}: {$pl['body']}\n";
						print $msg;
						$_SESSION['messages'][] = $msg;
						flush();
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
	#}
} catch(XMPPHP_Exception $e) {
    die($e->getMessage());
}
$conn->saveSession();

print "</pre>";
print "<img src='http://xmpp.org/images/xmpp.png' onload='window.location.reload()' />";
?>
