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

// Get current working directory with trailing slash
define('TESTS_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// Get class directory with trailing slash
define('CLASS_DIR', dirname(TESTS_DIR) . DIRECTORY_SEPARATOR);

/** XMPPHP_LogTest */
require_once TESTS_DIR . 'XMPPHP' . DIRECTORY_SEPARATOR . 'LogTest.php';

/** XMPPHP_XMLObjTest */
require_once TESTS_DIR . 'XMPPHP' . DIRECTORY_SEPARATOR . 'XMLObjTest.php';

/** XMPPHP_XMPPTest */
require_once TESTS_DIR . 'XMPPHP' . DIRECTORY_SEPARATOR . 'XMPPTest.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
  define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

/**
 * XMPPHP AllTests
 *
 * @package   XMPPHP
 * @author    Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author    Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author    Michael Garvin <JID: gar@netflint.net>
 * @copyright 2008 Nathanael C. Fritz
 * @version   $Id$
 */
class AllTests {

  public static function main() {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }

  public static function suite() {

    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTestSuite('XMPPHP_LogTest');
    $suite->addTestSuite('XMPPHP_XMLObjTest');
    $suite->addTestSuite('XMPPHP_XMPPTest');

    return $suite;
  }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
  AllTests::main();
}
