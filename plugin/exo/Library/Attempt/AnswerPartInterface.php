<?php

namespace UJM\ExoBundle\Library\Attempt;

/**
 * Represents a part of a question answer that will give points to a user when found in his answer.
 * NB. The score given can be negative if it is not an expected.
 */
interface AnswerPartInterface
{
    /**
     * Get the points to add.
     *
     * @return float
     */
    public function getScore();
}
