<?php

namespace UJM\ExoBundle\Tests\Library\Model;

use UJM\ExoBundle\Library\Model\ShuffleTrait;

class ShuffleTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mock;

    protected function setUp()
    {
        parent::setUp();

        // Creates a mock using the trait
        $this->mock = $this->getMockForTrait(ShuffleTrait::class);
    }

    /**
     * The trait MUST adds a `shuffle` with its getter and setter in the class using it.
     */
    public function testInjectScore()
    {
        // Test getter
        $this->assertTrue(method_exists($this->mock, 'getShuffle'));
        // Test setter
        $this->assertTrue(method_exists($this->mock, 'setShuffle'));
    }
}
