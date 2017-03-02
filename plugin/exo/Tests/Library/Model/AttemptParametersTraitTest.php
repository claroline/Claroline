<?php

namespace UJM\ExoBundle\Tests\Library\Model;

use UJM\ExoBundle\Library\Model\AttemptParametersTrait;

class AttemptParametersTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mock;

    protected function setUp()
    {
        parent::setUp();

        // Creates a mock using the trait
        $this->mock = $this->getMockForTrait(AttemptParametersTrait::class);
    }

    /**
     * The trait MUST adds a `randomOrder` with its getter and setter in the class using it.
     */
    public function testInjectRandomOrder()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getRandomOrder'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setRandomOrder'));
    }

    /**
     * The trait MUST adds a `randomPick` with its getter and setter in the class using it.
     */
    public function testInjectRandomPick()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getRandomPick'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setRandomPick'));
    }

    /**
     * The trait MUST adds a `pick` with its getter and setter in the class using it.
     */
    public function testInjectPick()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getPick'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setPick'));
    }

    /**
     * The trait MUST adds a `duration` with its getter and setter in the class using it.
     */
    public function testInjectDuration()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getDuration'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setDuration'));
    }

    /**
     * The trait MUST adds a `maxAttempts` with its getter and setter in the class using it.
     */
    public function testInjectMaxAttempts()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getMaxAttempts'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setMaxAttempts'));
    }
}
