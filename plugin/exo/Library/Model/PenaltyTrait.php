<?php

namespace UJM\ExoBundle\Library\Model;

/**
 * Gives an entity the ability to have a penalty.
 */
trait PenaltyTrait
{
    /**
     * @var float
     *
     * @ORM\Column(name="penalty", type="float")
     */
    private $penalty = 0;

    /**
     * Sets penalty.
     *
     * @param float $penalty
     */
    public function setPenalty($penalty)
    {
        $this->penalty = $penalty;
    }

    /**
     * Gets penalty.
     *
     * @return float
     */
    public function getPenalty()
    {
        return $this->penalty;
    }
}
