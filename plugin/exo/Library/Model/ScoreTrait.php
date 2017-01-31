<?php

namespace UJM\ExoBundle\Library\Model;

/**
 * Gives an entity the ability to have a score.
 */
trait ScoreTrait
{
    /**
     * @var float
     *
     * @ORM\Column(name="score", type="float")
     */
    private $score;

    /**
     * Sets score.
     *
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Gets score.
     *
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }
}
