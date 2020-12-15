<?php

namespace UJM\ExoBundle\Library\Attempt;

/**
 * Generic class used to apply penalty to a user answer.
 */
class GenericPenalty implements PenaltyItemInterface
{
    /**
     * @var
     */
    private $penalty;

    /**
     * GenericPenalty constructor.
     *
     * @param int $penalty
     */
    public function __construct($penalty)
    {
        if (!is_numeric($penalty)) {
            throw new \InvalidArgumentException('penalty should be a number.');
        }

        if (0 > $penalty) {
            throw new \InvalidArgumentException('penalty should be greater than 0.');
        }

        $this->penalty = (int) $penalty;
    }

    /**
     * Get the penalty to apply.
     *
     * @return float
     */
    public function getPenalty()
    {
        return $this->penalty;
    }
}
