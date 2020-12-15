<?php

namespace UJM\ExoBundle\Library\Model;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Options\Picking;
use UJM\ExoBundle\Library\Options\Recurrence;

/**
 * Gives an entity the ability to configure the generation of an attempt to an exercise (see Exercise and Step entities).
 */
trait AttemptParametersTrait
{
    /**
     * The picking method used to generate new attempts to the quiz.
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $picking = Picking::STANDARD;

    /**
     * @ORM\Column(name="random_order", type="string")
     *
     * @var string
     */
    private $randomOrder = Recurrence::NEVER;

    /**
     * @ORM\Column(name="random_pick", type="string")
     *
     * @var string
     */
    private $randomPick = Recurrence::NEVER;

    /**
     * @var int|array
     *
     * @ORM\Column(type="text")
     */
    private $pick = 0;

    /**
     * Maximum time (in minutes) allowed.
     * If 0, there is no duration limit.
     *
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $duration = 0;

    /**
     * Number of attempts allowed.
     * If 0, the user can retry as many times a he wishes.
     *
     * @ORM\Column(name="max_attempts", type="integer")
     *
     * @var int
     */
    private $maxAttempts = 0;

    /**
     * Number of attempts allowed per day.
     * If 0, the user can retry as many times a he wishes.
     *
     * @ORM\Column(name="max_day_attempts", type="integer")
     *
     * @var int
     */
    private $maxAttemptsPerDay = 0;

    /**
     * Sets random order.
     *
     * @param string $randomOrder
     */
    public function setRandomOrder($randomOrder)
    {
        $this->randomOrder = $randomOrder;
    }

    /**
     * Gets random order.
     *
     * @return string
     */
    public function getRandomOrder()
    {
        return $this->randomOrder;
    }

    /**
     * Sets random pick.
     *
     * @param string $randomPick
     */
    public function setRandomPick($randomPick)
    {
        $this->randomPick = $randomPick;
    }

    /**
     * Gets random pick.
     *
     * @return string
     */
    public function getRandomPick()
    {
        return $this->randomPick;
    }

    /**
     * Sets pick number.
     *
     * @param int|array $pick
     */
    public function setPick($pick)
    {
        $this->pick = json_encode($pick);
    }

    /**
     * Gets pick number.
     *
     * @return int|array
     */
    public function getPick()
    {
        return json_decode($this->pick, true);
    }

    /**
     * Sets duration.
     *
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * Gets duration.
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Sets max attempts.
     *
     * @param int $maxAttempts
     */
    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * Gets max attempts.
     *
     * @return int
     */
    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }

    /**
     * Sets max attempts.
     *
     * @param int $maxAttemptsPerDay
     */
    public function setMaxAttemptsPerDay($maxAttemptsPerDay)
    {
        if ($maxAttemptsPerDay > $this->maxAttempts) {
            //we can't try more times per day than the maximum allowed attempts defined
            $this->maxAttemptsPerDay = $this->maxAttempts;
        }

        $this->maxAttemptsPerDay = $maxAttemptsPerDay;
    }

    /**
     * Gets max attempts.
     *
     * @return int
     */
    public function getMaxAttemptsPerDay()
    {
        return $this->maxAttemptsPerDay;
    }
}
