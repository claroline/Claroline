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

/** XMPPHP_XMLObj */
require_once CLASS_DIR . 'XMPPHP' . DIRECTORY_SEPARATOR . 'XMLObj.php';

/**
 * XMPPHP XMLObjectTest
 *
 * @package   XMPPHP
 * @author    Nathanael C. Fritz <JID: fritzy@netflint.net>
 * @author    Stephan Wentz <JID: stephan@jabber.wentz.it>
 * @author    Michael Garvin <JID: gar@netflint.net>
 * @copyright 2008 Nathanael C. Fritz
 * @version   $Id$
 */
class XMPPHP_XMLObjTest extends PHPUnit_Framework_TestCase {

  public function testToStringNameNamespace() {

    $xmlobj   = new XMPPHP_XMLObj('testname', 'testNameSpace');
    $expected = '<testname xmlns="testNameSpace"></testname>';
    $result   = $xmlobj->toString();
    $this->assertSame($expected, $result);
  }

  public function testToStringNameNamespaceAttr() {

    $xmlobj   = new XMPPHP_XMLObj('testName', 'testNameSpace', array('attr1' => 'valA', 'attr2' => 'valB'));
    $expected = '<testname xmlns="testNameSpace" attr1="valA" attr2="valB"></testname>';
    $result   = $xmlobj->toString();
    $this->assertSame($expected, $result);
  }

  public function testToStringNameNamespaceData() {

    $xmlobj   = new XMPPHP_XMLObj('testName', 'testNameSpace', array(), 'I am test data');
    $expected = '<testname xmlns="testNameSpace">I am test data</testname>';
    $result   = $xmlobj->toString();
    $this->assertSame($expected, $result);
  }

  public function testToStringNameNamespaceSub() {

    $xmlobj       = new XMPPHP_XMLObj('testName', 'testNameSpace');
    $sub1         = new XMPPHP_XMLObj('subName', 'subNameSpace');
    $xmlobj->subs = array($sub1);
    $expected     = '<testname xmlns="testNameSpace"><subname xmlns="subNameSpace"></subname></testname>';
    $result       = $xmlobj->toString();
    $this->assertSame($expected, $result);
  }
}
