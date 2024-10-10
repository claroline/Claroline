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
    private array $expected = [];

    /**
     * The expected answers that have been missed by the user.
     *
     * @var AnswerPartInterface[]
     */
    private array $missing = [];

    /**
     * The answers that are not supposed to be chosen by the user.
     *
     * @var AnswerPartInterface[]
     */
    private array $unexpected = [];

    /**
     * Penalties to apply to the score (eg. hints).
     *
     * @var PenaltyItemInterface[]
     */
    private array $penalties = [];

    /**
     * The answers that have been correctly not chosen by the user.
     *
     * @var AnswerPartInterface[]
     */
    private array $expectedMissing = [];

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
    public function getExpected(): array
    {
        return $this->expected;
    }

    public function addExpected(AnswerPartInterface $expected): void
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
    public function getMissing(): array
    {
        return $this->missing;
    }

    public function addMissing(AnswerPartInterface $missing): void
    {
        $this->missing[] = $missing;
    }

    /**
     * Get unexpected answers.
     *
     * @return AnswerPartInterface[]
     */
    public function getUnexpected(): array
    {
        return $this->unexpected;
    }

    public function addUnexpected(AnswerPartInterface $unexpected): void
    {
        $this->unexpected[] = $unexpected;
    }

    /**
     * Get penalties.
     *
     * @return PenaltyItemInterface[]
     */
    public function getPenalties(): array
    {
        return $this->penalties;
    }

    public function addPenalty(PenaltyItemInterface $penalty): void
    {
        $this->penalties[] = $penalty;
    }

    /**
     * Get expected missing answers.
     *
     * @return AnswerPartInterface[]
     */
    public function getExpectedMissing(): array
    {
        return $this->expectedMissing;
    }

    public function addExpectedMissing(AnswerPartInterface $expectedMissing): void
    {
        $this->expectedMissing[] = $expectedMissing;
    }
}
