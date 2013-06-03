<?php

namespace Claroline\CoreBundle\Library\Testing;

use \Mockery;

abstract class MockeryTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        Mockery::getConfiguration()->allowMockingMethodsUnnecessarily(false);
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}