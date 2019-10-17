<?php

namespace UJM\ExoBundle\Tests\Library\Attempt;

use PHPUnit\Framework\TestCase;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;

class GenericPenaltyTest extends TestCase
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
