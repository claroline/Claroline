<?php

namespace Claroline\CoreBundle\Library\Testing;

use Mockery as m;

abstract class MockeryTestCase extends \PHPUnit_Framework_TestCase
{
    private static $isMockeryInitialized = false;
    private static $nonCloneableClasses = array();
    private static $mocks = array();

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->initMockery();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * Creates a mock. This method will create the mock once and return
     * clones to reduce the memory foot print.
     *
     * @param mixed $class
     *
     * @return object
     */
    protected function mock($class)
    {
        // ensure mockery is initialized in the data providers, which are
        // called before the setUp method
        if (!self::$isMockeryInitialized) {
            $this->initMockery();
            self::$isMockeryInitialized = true;
        }

        // ensure the class can be cloned safely
        if (!$this->isCloneable($class)) {
            return m::mock($class);
        }

        // keep the orginal mock before returning a clone
        if (!isset(self::$mocks[$class])) {
            self::$mocks[$class] = m::mock($class);
        }

        return clone self::$mocks[$class];
    }

    private function initMockery()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(false);
        m::getConfiguration()->allowMockingMethodsUnnecessarily(false);
    }

    private function isCloneable($class)
    {
        if (!is_string($class) || in_array($class, self::$nonCloneableClasses)) {
            return false;
        }

        $rClass = new \ReflectionClass($class);

        // native php objects may not be cloneable, and we cannot rely on any
        // custom __clone implementation (ex: Symfony's Request object)
        if (!$rClass->isCloneable() || $rClass->hasMethod('__clone')) {
            self::$nonCloneableClasses[] = $class;
            return false;
        }

        return true;
    }
}
