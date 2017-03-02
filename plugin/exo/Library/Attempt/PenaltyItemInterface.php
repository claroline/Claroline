<?php

namespace UJM\ExoBundle\Library\Attempt;

/**
 * Represents an item that an user can use (eg. an hint) in the context of his attempt
 * and which will apply a penalty to final score.
 */
interface PenaltyItemInterface
{
    /**
     * Get the penalty to apply.
     *
     * @return float
     */
    public function getPenalty();
}
