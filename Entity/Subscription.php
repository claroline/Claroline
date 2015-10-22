<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\User;

/**
 * UJM\ExoBundle\Entity\Subscription.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\SubscriptionRepository")
 * @ORM\Table(name="ujm_subscription")
 */
class Subscription
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

    /**
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

    public function __construct(User $user, Exercise $exercise)
    {
        $this->user = $user;
        $this->exercise = $exercise;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setExercise(Exercise $exercise)
    {
        $this->produit = $exercise;
    }

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
     * Get creator.
     */
    public function getCreator()
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
     * Get admin.
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}
