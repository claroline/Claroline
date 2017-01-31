<?php

namespace UJM\ExoBundle\Tests\Library\Model;

use UJM\ExoBundle\Library\Model\UuidTrait;

class UuidTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mock;

    protected function setUp()
    {
        parent::setUp();

        // Creates a mock using the trait
        $this->mock = $this->getMockForTrait(UuidTrait::class);
    }

    /**
     * The trait MUST adds a `uuid` with its getter and setter in the class using it.
     */
    public function testInjectScore()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getUuid'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setUuid'));
    }
}
