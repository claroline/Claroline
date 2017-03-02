<?php

namespace UJM\ExoBundle\Library\Attempt;

/**
 * A user answer to a question, formatted to be marked.
 */
class CorrectedAnswer
{
    /**
     * The expected answers that have been chosen by the user.
     *
     * @var AnswerPartInterface[]
     */
    private $expected = [];

    /**
     * The expected answers that have been missed by the user.
     *
     * @var AnswerPartInterface[]
     */
    private $missing = [];

    /**
     * The answers that are not supposed to be chosen by the user.
     *
     * @var AnswerPartInterface[]
     */
    private $unexpected = [];

    /**
     * Penalties to apply to the score (eg. hints).
     *
     * @var PenaltyItemInterface[]
     */
    private $penalties = [];

    /**
     * CorrectedAnswer constructor.
     *
     * @param AnswerPartInterface[]  $expected
     * @param AnswerPartInterface[]  $missing
     * @param AnswerPartInterface[]  $unexpected
     * @param PenaltyItemInterface[] $penalties
     */
    public function __construct(
        array $expected = [],
        array $missing = [],
        array $unexpected = [],
        array $penalties = []
    ) {
        $this->expected = $expected;
        $this->missing = $missing;
        $this->unexpected = $unexpected;
        $this->penalties = $penalties;
    }

    /**
     * Get expected answers.
     *
     * @return AnswerPartInterface[]
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * @param AnswerPartInterface $expected
     */
    public function addExpected(AnswerPartInterface $expected)
    {
        $this->expected[] = $expected;
    }

    /**
     * Get missing answers.
     *
     * @return AnswerPartInterface[]
     */
    public function getMissing()
    {
        return $this->missing;
    }

    /**
     * @param AnswerPartInterface $missing
     */
    public function addMissing(AnswerPartInterface $missing)
    {
        $this->missing[] = $missing;
    }

    /**
     * Get unexpected answers.
     *
     * @return AnswerPartInterface[]
     */
    public function getUnexpected()
    {
        return $this->unexpected;
    }

    /**
     * @param AnswerPartInterface $unexpected
     */
    public function addUnexpected(AnswerPartInterface $unexpected)
    {
        $this->unexpected[] = $unexpected;
    }

    /**
     * Get penalties.
     *
     * @return PenaltyItemInterface[]
     */
    public function getPenalties()
    {
        return $this->penalties;
    }

    /**
     * @param PenaltyItemInterface $penalty
     */
    public function addPenalty(PenaltyItemInterface $penalty)
    {
        $this->penalties[] = $penalty;
    }
}
