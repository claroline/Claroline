<?php

namespace UJM\ExoBundle\Library\Attempt;

/**
 * Generic class used to apply score to a user answer.
 */
class GenericScore implements AnswerPartInterface
{
    /**
     * @var float
     */
    private $score;

    /**
     * GenericPenalty constructor.
     *
     * @param float $score
     */
    public function __construct($score)
    {
        if (!is_numeric($score)) {
            throw new \InvalidArgumentException('score should be a number.');
        }

        if (0 > $score) {
            throw new \InvalidArgumentException('score should be greater than 0.');
        }

        $this->score = floatval($score);
    }

    /**
     * Get the score to apply.
     *
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }
}
