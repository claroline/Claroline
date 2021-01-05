<?php

namespace Innova\PathBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserProgression
 * Represents the progression of a User in a Step.
 *
 * @ORM\Table(name="innova_path_progression")
 * @ORM\Entity()
 */
class UserProgression
{
    use Id;

    /**
     * Default status when creating a new UserProgression.
     *
     * @var string
     */
    protected static $statusDefault = 'seen';

    /**
     * List of available status.
     *
     * @var array
     */
    protected static $statusAvailable = [
        'unseen',
        'seen',
        'to_do',
        'done',
        'to_review',
    ];

    /**
     * Step for which we track the progression.
     *
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Step")
     * @ORM\JoinColumn(name="step_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Step
     */
    protected $step;

    /**
     * User for which we track the progression.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var User
     */
    protected $user;

    /**
     * Current state of the Step.
     *
     * @ORM\Column(name="progression_status", type="string")
     *
     * @var string
     */
    protected $status;

    public function __construct()
    {
        // Set the default status
        $this->status = static::$statusDefault;
    }

    /**
     * Get Step.
     *
     * @return Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set Step.
     *
     * @return UserProgression
     */
    public function setStep(Step $step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get User.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set User.
     *
     * @return UserProgression
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return UserProgression
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the default status.
     *
     * @return string
     */
    public static function getDefaultStatus()
    {
        return static::$statusDefault;
    }

    /**
     * Get the list of all available status.
     *
     * @return array
     */
    public static function getAvailableStatus()
    {
        return static::$statusAvailable;
    }
}
