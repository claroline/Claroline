<?php

namespace Claroline\EvaluationBundle\Tests\Library;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Library\Checker\ScoreChecker;
use Claroline\EvaluationBundle\Library\GenericEvaluation;
use PHPUnit\Framework\TestCase;

final class ScoreCheckerTest extends TestCase
{
    public function testSuccessScoreUnderZeroThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new ScoreChecker(-1);
    }

    public function testSuccessScoreOverHundredThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new ScoreChecker(300);
    }

    public function testSupportEvaluationWithScore()
    {
        $checker = new ScoreChecker(100);

        $evaluationWithScore = new GenericEvaluation(100, 10);
        $this->assertTrue($checker->supports($evaluationWithScore));
    }

    public function testDontSupportEvaluationWithoutScore()
    {
        $checker = new ScoreChecker(100);

        $evaluationWithoutScore = new GenericEvaluation();
        $this->assertFalse($checker->supports($evaluationWithoutScore));
    }

    public function testDontVoteIfNoSuccessScore()
    {
        $checker = new ScoreChecker(0);

        $this->assertNull($checker->vote(new GenericEvaluation(100, 10)));
    }

    public function testDontVoteForNonTerminatedEvaluation()
    {
        $checker = new ScoreChecker(100);

        $nonTerminatedEvaluation = new GenericEvaluation(0, 10);
        $this->assertNull($checker->vote($nonTerminatedEvaluation));
    }

    public function testVoteForTerminatedEvaluationWithScore()
    {
        $checker = new ScoreChecker(100);

        $terminatedEvaluation = new GenericEvaluation(100, 10);
        $this->assertNotNull($checker->vote($terminatedEvaluation));
    }

    public function testVotePassed()
    {
        $checker = new ScoreChecker(70);

        $passedEvaluation = new GenericEvaluation(100, 100, 70);
        $this->assertEquals(AbstractEvaluation::STATUS_PASSED, $checker->vote($passedEvaluation));
    }

    public function testVoteFailed()
    {
        $checker = new ScoreChecker(70);

        $failedEvaluation = new GenericEvaluation(100, 100, 60);
        $this->assertEquals(AbstractEvaluation::STATUS_PASSED, $checker->vote($failedEvaluation));
    }
}
