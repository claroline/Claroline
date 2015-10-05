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

/** XMPPHP_Log */
require_once CLASS_DIR . 'XMPPHP' . DIRECTORY_SEPARATOR . 'Log.php';

/**
 * XMPPHP LogTest
 *
 * @package   XMPPHP
 * @author    Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author    Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author    Michael Garvin <JID: gar@netflint.net>
 * @copyright 2008 Nathanael C. Fritz
 * @version   $Id$
 */
class XMPPHP_LogTest extends PHPUnit_Framework_TestCase {

  public function testPrintoutNoOutput() {

    $log = new XMPPHP_Log();
    $msg = 'I am a test log message';

    ob_start();
    $log->log('test');
    $result = ob_get_clean();

    $this->assertEquals('', $result);
  }

  public function testPrintoutOutput() {

    $log = new XMPPHP_Log(true);
    $msg = 'I am a test log message';

    ob_start();
    $log->log($msg);
    $result = ob_get_clean();

    $this->assertContains($msg, $result);
  }

  public function testPrintoutNoOutputWithDefaultLevel() {

    $log = new XMPPHP_Log(true, XMPPHP_Log::LEVEL_ERROR);
    $msg = 'I am a test log message';

    ob_start();
    $log->log($msg);
    $result = ob_get_clean();

    $this->assertSame('', $result);
  }

  public function testPrintoutOutputWithDefaultLevel() {

    $log = new XMPPHP_Log(true, XMPPHP_Log::LEVEL_INFO);
    $msg = 'I am a test log message';

    ob_start();
    $log->log($msg);
    $result = ob_get_clean();

    $this->assertContains($msg, $result);
  }

  public function testPrintoutNoOutputWithCustomLevel() {

    $log = new XMPPHP_Log(true, XMPPHP_Log::LEVEL_INFO);
    $msg = 'I am a test log message';

    ob_start();
    $log->log($msg, XMPPHP_Log::LEVEL_DEBUG);
    $result = ob_get_clean();

    $this->assertSame('', $result);
  }

  public function testPrintoutOutputWithCustomLevel() {

    $log = new XMPPHP_Log(true, XMPPHP_Log::LEVEL_INFO);
    $msg = 'I am a test log message';

    ob_start();
    $log->log($msg, XMPPHP_Log::LEVEL_INFO);
    $result = ob_get_clean();

    $this->assertContains($msg, $result);
  }

  public function testExplicitPrintout() {

    $log = new XMPPHP_Log(false);
    $msg = 'I am a test log message';

    ob_start();
    $log->log($msg);
    $result = ob_get_clean();

    $this->assertSame('', $result);
  }

  public function testExplicitPrintoutResult() {

    $log = new XMPPHP_Log(false);
    $msg = 'I am a test log message';

    ob_start();
    $log->log($msg);
    $result = ob_get_clean();

    $this->assertSame('', $result);

    ob_start();
    $log->printout();
    $result = ob_get_clean();

    $this->assertContains($msg, $result);
  }

  public function testExplicitPrintoutClear() {

    $log = new XMPPHP_Log(false);
    $msg = 'I am a test log message';

    ob_start();
    $log->log($msg);
    $result = ob_get_clean();

    $this->assertSame('', $result);

    ob_start();
    $log->printout();
    $result = ob_get_clean();

    $this->assertContains($msg, $result);

    ob_start();
    $log->printout();
    $result = ob_get_clean();

    $this->assertSame('', $result);
  }

  public function testExplicitPrintoutLevel() {

    $log = new XMPPHP_Log(false, XMPPHP_Log::LEVEL_ERROR);
    $msg = 'I am a test log message';

    ob_start();
    $log->log($msg);
    $result = ob_get_clean();

    $this->assertSame('', $result);

    ob_start();
    $log->printout(true, XMPPHP_Log::LEVEL_INFO);
    $result = ob_get_clean();

    $this->assertSame('', $result);
  }
}
