<?php

namespace Claroline\EvaluationBundle\Tests\Library;

use Claroline\EvaluationBundle\Library\Checker\ProgressionChecker;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Library\GenericEvaluation;
use PHPUnit\Framework\TestCase;

final class ProgressionCheckerTest extends TestCase
{
    public function testThresholdUnderZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ProgressionChecker(-1);
    }

    public function testThresholdOverHundredThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ProgressionChecker(300);
    }

    public function testSupportEvaluation(): void
    {
        $checker = new ProgressionChecker(100);

        $evaluation = new GenericEvaluation(100);
        $this->assertTrue($checker->supports($evaluation));
    }

    public function testVoteNotAttempted(): void
    {
        $checker = new ProgressionChecker(70);

        $notAttemptedEvaluation = new GenericEvaluation(0);
        $this->assertEquals(EvaluationStatus::NOT_ATTEMPTED, $checker->vote($notAttemptedEvaluation));
    }

    public function testVoteIncomplete(): void
    {
        $checker = new ProgressionChecker(70);

        $incompleteEvaluation = new GenericEvaluation(69);
        $this->assertEquals(EvaluationStatus::INCOMPLETE, $checker->vote($incompleteEvaluation));
    }

    public function testVotePassed(): void
    {
        $checker = new ProgressionChecker();

        $endedEvaluation = new GenericEvaluation(100);
        $this->assertEquals(EvaluationStatus::COMPLETED, $checker->vote($endedEvaluation));
    }
}
