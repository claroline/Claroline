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
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private $picking = Picking::STANDARD;

    /**
     * @var string
     */
    #[ORM\Column(name: 'random_order', type: 'string')]
    private $randomOrder = Recurrence::NEVER;

    /**
     * @var string
     */
    #[ORM\Column(name: 'random_pick', type: 'string')]
    private $randomPick = Recurrence::NEVER;

    /**
     * @var int|array
     */
    #[ORM\Column(type: 'text')]
    private $pick = 0;

    /**
     * Maximum time (in minutes) allowed.
     * If 0, there is no duration limit.
     *
     *
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private $duration = 0;

    /**
     * Number of attempts allowed.
     * If 0, the user can retry as many times a he wishes.
     *
     *
     * @var int
     */
    #[ORM\Column(name: 'max_attempts', type: 'integer')]
    private $maxAttempts = 0;

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
}
