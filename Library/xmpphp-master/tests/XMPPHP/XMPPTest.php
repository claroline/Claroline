<?php

/**
 * XMPPHP: The PHP XMPP Library
 * Copyright (C) 2008  Nathanael C. Fritz
 * This file is part of SleekXMPP.
 *
 * XMPPHP is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * XMPPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with XMPPHP; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category  xmpphp
 * @package   XMPPHP
 * @author    Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author    Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author    Michael Garvin <JID: gar@netflint.net>
 * @copyright 2008 Nathanael C. Fritz
 */

/** XMPPHP_XMPP */
require_once CLASS_DIR . 'XMPPHP' . DIRECTORY_SEPARATOR . 'XMPP.php';

/**
 * XMPPHP XMPPTest
 *
 * @package   XMPPHP
 * @author    Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author    Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author    Michael Garvin <JID: gar@netflint.net>
 * @copyright 2008 Nathanael C. Fritz
 * @version   $Id$
 */
class XMPPHP_XMPPTest extends PHPUnit_Framework_TestCase {

  public function testConnectException() {

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

  public function testAuthException() {

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
