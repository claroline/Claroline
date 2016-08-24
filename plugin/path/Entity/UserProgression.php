<?php

namespace Innova\PathBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserProgression
 * Represents the progression of a User in a Step.
 *
 * @ORM\Table(name="innova_path_progression")
 * @ORM\Entity(repositoryClass="Innova\PathBundle\Repository\UserProgressionRepository")
 */
class UserProgression implements \JsonSerializable
{
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
     * Unique identifier.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Step for which we track the progression.
     *
     * @var \Innova\PathBundle\Entity\Step
     *
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Step")
     * @ORM\JoinColumn(name="step_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $step;

    /**
     * User for which we track the progression.
     *
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $user;

    /**
     * Current state of the Step.
     *
     * @var string
     *
     * @ORM\Column(name="progression_status", type="string")
     */
    protected $status;

    /**
     * Can the user access the step.
     *
     * @var bool
     *
     * @ORM\Column(name="authorized_access", type="boolean")
     */
    protected $authorized;
    /**
     * State of the access to the step.
     *
     * @var bool
     *
     * @ORM\Column(name="locked_access", type="boolean")
     */
    protected $locked;

    /**
     * Has the lock been called for removal ?
     *
     * @var bool
     *
     * @ORM\Column(name="lockedcall_access", type="boolean")
     */
    protected $lockedcall;

    /**
     * CLass constructor.
     */
    public function __construct()
    {
        // Set the default status
        $this->status = static::$statusDefault;
        $this->lockedcall = false;
        $this->locked = false;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Step.
     *
     * @return \Innova\PathBundle\Entity\Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set Step.
     *
     * @param \Innova\PathBundle\Entity\Step $step
     *
     * @return \Innova\PathBundle\Entity\UserProgression
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
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Innova\PathBundle\Entity\UserProgression
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
     * @return \Innova\PathBundle\Entity\UserProgression
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

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'userId' => ($this->user instanceof User) ? $this->user->getId() : 0,
            'stepId' => $this->step->getId(),
            'status' => $this->status,
            'authorized' => $this->authorized,
            'locked' => $this->locked,
            'lockedcall' => $this->lockedcall,
        ];
    }

    /**
     * Set authorized.
     *
     * @param bool $authorized
     *
     * @return UserProgression
     */
    public function setAuthorized($authorized)
    {
        $this->authorized = $authorized;

        return $this;
    }

    /**
     * Get authorized.
     *
     * @return bool
     */
    public function getAuthorized()
    {
        return $this->authorized;
    }

    /**
     * Set locked.
     *
     * @param bool $locked
     *
     * @return UserProgression
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked.
     *
     * @return bool
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set lockedcall.
     *
     * @param bool $lockedcall
     *
     * @return UserProgression
     */
    public function setLockedcall($lockedcall)
    {
        $this->lockedcall = $lockedcall;

        return $this;
    }

    /**
     * Get lockedcall.
     *
     * @return bool
     */
    public function getLockedcall()
    {
        return $this->lockedcall;
    }
}
