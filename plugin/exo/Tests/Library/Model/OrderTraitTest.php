<?php

namespace UJM\ExoBundle\Tests\Library\Model;

use UJM\ExoBundle\Library\Model\OrderTrait;

class OrderTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mock;

    protected function setUp()
    {
        parent::setUp();

        // Creates a mock using the trait
        $this->mock = $this->getMockForTrait(OrderTrait::class);
    }

    /**
     * The trait MUST adds an `order` with its getter and setter in the class using it.
     */
    public function testInjectFeedback()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getOrder'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setOrder'));
    }
}
