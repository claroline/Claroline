<?php

require_once dirname(dirname(dirname(__FILE__))) . '/XMPPHP/XMPP.php';

class XMPPHP_XMPPTest extends PHPUnit_Framework_TestCase
{
    public function testConnectException()
    {
        try {
            $xmpp = new XMPPHP_XMPP('talk.google.com', 1234, 'invalidusername', 'invalidpassword', 'xmpphp', 'talk.google.com', false, XMPPHP_Log::LEVEL_VERBOSE);
            $xmpp->useEncryption(false);
            $xmpp->connect(10);
            $xmpp->processUntil('session_start');
            $xmpp->presence();
            $xmpp->message('stephan@jabber.wentz.it', 'This is a test message!');
            $xmpp->disconnect();
        } catch(XMPPHP_Exception $e) {
            return;
        } catch(Exception $e) {
            $this->fail('Unexpected Exception thrown: '.$e->getMessage());
        }
        
        $this->fail('Expected XMPPHP_Exception not thrown!');
    }

    public function testAuthException()
    {
        try {
            $xmpp = new XMPPHP_XMPP('jabber.wentz.it', 5222, 'invalidusername', 'invalidpassword', 'xmpphp', 'jabber.wentz.it', true, XMPPHP_Log::LEVEL_VERBOSE);
            $xmpp->useEncryption(false);
            $xmpp->connect(10);
            $xmpp->processUntil('session_start');
            $xmpp->presence();
            $xmpp->message('stephan@jabber.wentz.it', 'This is a test message!');
            $xmpp->disconnect();
        } catch(XMPPHP_Exception $e) {
            return;
        } catch(Exception $e) {
            $this->fail('Unexpected Exception thrown: '.$e->getMessage());
        }
        
        $this->fail('Expected XMPPHP_Exception not thrown!');
    }
}
