<?php

namespace Claroline\EvaluationBundle\Tests\Library;

use Claroline\EvaluationBundle\Library\Checker\ScoreChecker;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Library\GenericEvaluation;
use PHPUnit\Framework\TestCase;

final class ScoreCheckerTest extends TestCase
{
    public function testSuccessScoreUnderZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ScoreChecker(-1);
    }

    public function testSuccessScoreOverHundredThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ScoreChecker(300);
    }

    public function testSupportEvaluationWithScore(): void
    {
        $checker = new ScoreChecker(100);

        $evaluationWithScore = new GenericEvaluation(100, 10);
        $this->assertTrue($checker->supports($evaluationWithScore));
    }

    public function testDontSupportEvaluationWithoutScore(): void
    {
        $checker = new ScoreChecker(100);

        $evaluationWithoutScore = new GenericEvaluation();
        $this->assertFalse($checker->supports($evaluationWithoutScore));
    }

    public function testDontVoteIfNoSuccessScore(): void
    {
        $checker = new ScoreChecker(0);

        $this->assertNull($checker->vote(new GenericEvaluation(100, 10)));
    }

    public function testDontVoteForNonTerminatedEvaluation(): void
    {
        $checker = new ScoreChecker(100);

        $nonTerminatedEvaluation = new GenericEvaluation(0, 10);
        $this->assertNull($checker->vote($nonTerminatedEvaluation));
    }

    public function testVoteForTerminatedEvaluationWithScore(): void
    {
        $checker = new ScoreChecker(100);

        $terminatedEvaluation = new GenericEvaluation(100, 10);
        $this->assertNotNull($checker->vote($terminatedEvaluation));
    }

    public function testVotePassed(): void
    {
        $checker = new ScoreChecker(70);

        $passedEvaluation = new GenericEvaluation(100, 100, 70);
        $this->assertEquals(EvaluationStatus::PASSED, $checker->vote($passedEvaluation));
    }

    public function testVoteFailed(): void
    {
        $checker = new ScoreChecker(70);

        $failedEvaluation = new GenericEvaluation(100, 100, 60);
        $this->assertEquals(EvaluationStatus::FAILED, $checker->vote($failedEvaluation));
    }
}
