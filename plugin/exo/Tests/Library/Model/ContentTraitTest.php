<?php

namespace UJM\ExoBundle\Tests\Library\Model;

use UJM\ExoBundle\Library\Model\ContentTrait;

class ContentTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mock;

    protected function setUp()
    {
        parent::setUp();

        // Creates a mock using the trait
        $this->mock = $this->getMockForTrait(ContentTrait::class);
    }

    /**
     * The trait MUST adds a `resourceNode` with its getter and setter in the class using it.
     */
    public function testInjectResourceNode()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getResourceNode'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setResourceNode'));
    }

    /**
     * The trait MUST adds a `data` with its getter and setter in the class using it.
     */
    public function testInjectData()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getData'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setData'));
    }
}
