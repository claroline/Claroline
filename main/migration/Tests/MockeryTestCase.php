<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Tests;

use Mockery as m;

abstract class MockeryTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(false);
        m::getConfiguration()->allowMockingMethodsUnnecessarily(false);
    }

    protected function tearDown()
    {
        m::close();
    }
}
