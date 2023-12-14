<?php

namespace Claroline\EvaluationBundle\Tests\Library;

use Claroline\EvaluationBundle\Library\GenericEvaluation;
use PHPUnit\Framework\TestCase;

final class GenericEvaluationTest extends TestCase
{
    public function testProgressionUnderZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GenericEvaluation(-1);
    }

    public function testProgressionOverHundredThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GenericEvaluation(300);
    }

    public function testGetProgression(): void
    {
        $evaluation = new GenericEvaluation(1);
        $this->assertEquals(1, $evaluation->getProgression());
    }

    public function testGetScoreMax(): void
    {
        $evaluation = new GenericEvaluation(100, 5);
        $this->assertEquals(5, $evaluation->getScoreMax());
    }

    public function testGetScore(): void
    {
        $evaluation = new GenericEvaluation(100, 5, 2);
        $this->assertEquals(2, $evaluation->getScore());
    }

    public function testIsTerminatedIfProgressionIsHundred(): void
    {
        $evaluation = new GenericEvaluation(100, 5, 2);
        $this->assertTrue($evaluation->isTerminated());
    }

    public function testIsNotTerminatedIfProgressionIsNotHundred(): void
    {
        $evaluation = new GenericEvaluation(50, 5, 2);
        $this->assertFalse($evaluation->isTerminated());
    }
}
