<?php

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
