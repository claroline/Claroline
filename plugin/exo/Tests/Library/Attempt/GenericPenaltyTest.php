<?php

namespace UJM\ExoBundle\Tests\Library\Attempt;

use UJM\ExoBundle\Library\Attempt\GenericPenalty;

class GenericPenaltyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonNumericPenaltyThrowsException()
    {
        new GenericPenalty([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPenaltyUnderZeroThrowsException()
    {
        new GenericPenalty(-1);
    }

    public function testGetPenalty()
    {
        $penalty = new GenericPenalty(1);
        $this->assertEquals(1, $penalty->getPenalty());
    }
}
