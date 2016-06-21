<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Testing;

use Mockery as m;
use Mockery\Mock;

abstract class MockeryTestCase extends \PHPUnit_Framework_TestCase
{
    private static $isMockeryInitialized = false;
    private static $nonCloneableClasses = array();
    private static $mocks = array();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initMockery();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * Creates a mock. This method will delegate to Mockery::mock() and possibly
     * store the created mock and return a clone of it to reduce the memory footprint.
     * Its usage remains the same than the original.
     *
     * @param mixed $class
     * @param mixed $parameters
     *
     * @return Mock
     */
    protected function mock($class, $parameters = null)
    {
        // ensure mockery is initialized in the data providers, which are
        // called before the setUp method
        if (!self::$isMockeryInitialized) {
            $this->initMockery();
            self::$isMockeryInitialized = true;
        }

        if (is_string($class) && isset(self::$mocks[$class])) {
            return clone self::$mocks[$class];
        }

        $mock = $parameters === null ? m::mock($class) : m::mock($class, $parameters);

        // ensure the class can be cloned safely
        if (!$this->isCloneable($class)) {
            return $mock;
        }

        // keep the orginal mock before returning a clone
        self::$mocks[$class] = $mock;

        return clone $mock;
    }

    private function initMockery()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(false);
        m::getConfiguration()->allowMockingMethodsUnnecessarily(false);
    }

    private function isCloneable($class)
    {
        if (!is_string($class) // probably a final class mock
            || in_array($class, self::$nonCloneableClasses) // already checked
            || false !== strpos($class, '[')) { // partial mock

            return false;
        }

        $rClass = new \ReflectionClass($class);

        // native php objects may not be cloneable, and we cannot rely on any
        // custom __clone implementation (ex: Symfony's Request object)
        if ($rClass->isInternal() || $rClass->hasMethod('__clone')) {
            self::$nonCloneableClasses[] = $class;

            return false;
        }

        return true;
    }
}
