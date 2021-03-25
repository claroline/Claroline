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
     * The answers that have been correctly not chosen by the user.
     *
     * @var AnswerPartInterface[]
     */
    private $expectedMissing = [];

    /**
     * CorrectedAnswer constructor.
     *
     * @param AnswerPartInterface[]  $expected
     * @param AnswerPartInterface[]  $missing
     * @param AnswerPartInterface[]  $unexpected
     * @param PenaltyItemInterface[] $penalties
     * @param AnswerPartInterface[]  $expectedMissing
     */
    public function __construct(
        array $expected = [],
        array $missing = [],
        array $unexpected = [],
        array $penalties = [],
        array $expectedMissing = []
    ) {
        $this->expected = $expected;
        $this->missing = $missing;
        $this->unexpected = $unexpected;
        $this->penalties = $penalties;
        $this->expectedMissing = $expectedMissing;
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

    public function addExpected(AnswerPartInterface $expected)
    {
        $found = false;

        if (method_exists($expected, 'getUuid')) {
            foreach ($this->expected as $data) {
                if ($data->getUuid() === $expected->getUuid()) {
                    $found = true;
                }
            }
        }

        // avoid duplicates here
        if (!$found) {
            $this->expected[] = $expected;
        } else {
            $this->addMissing($expected);
        }
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

    public function addPenalty(PenaltyItemInterface $penalty)
    {
        $this->penalties[] = $penalty;
    }

    /**
     * Get expected missing answers.
     *
     * @return AnswerPartInterface[]
     */
    public function getExpectedMissing()
    {
        return $this->expectedMissing;
    }

    public function addExpectedMissing(AnswerPartInterface $expectedMissing)
    {
        $this->expectedMissing[] = $expectedMissing;
    }
}
