<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\User;

/**
 * UJM\ExoBundle\Entity\Subscription.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_subscription")
 */
class Subscription
{
    /**
     * @var int
     * 
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * @var Exercise
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Exercise")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $exercise;

    /**
     * @var bool
     *
     * @ORM\Column(name="creator", type="boolean")
     */
    private $creator;

    /**
     * @var bool
     *
     * @ORM\Column(name="admin", type="boolean")
     */
    private $admin;

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
     * Set User.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
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
     * Set Exercise.
     *
     * @param Exercise $exercise
     */
    public function setExercise(Exercise $exercise)
    {
        $this->exercise = $exercise;
    }

    /**
     * Get Exercise.
     *
     * @return Exercise
     */
    public function getExercise()
    {
        return $this->exercise;
    }

    /**
     * Set creator.
     *
     * @param bool $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Is creator.
     *
     * @return bool
     */
    public function isCreator()
    {
        return $this->creator;
    }

    /**
     * Set admin.
     *
     * @param bool $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * Is admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->admin;
    }
}
