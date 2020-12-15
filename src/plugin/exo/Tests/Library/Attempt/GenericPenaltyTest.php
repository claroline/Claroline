<?php

namespace UJM\ExoBundle\Tests\Library\Attempt;

use PHPUnit\Framework\TestCase;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;

class GenericPenaltyTest extends TestCase
{
    public function testNonNumericPenaltyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        new GenericPenalty([]);
    }

    public function testPenaltyUnderZeroThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        new GenericPenalty(-1);
    }

    public function testGetPenalty()
    {
        $penalty = new GenericPenalty(1);
        $this->assertEquals(1, $penalty->getPenalty());
    }
}
