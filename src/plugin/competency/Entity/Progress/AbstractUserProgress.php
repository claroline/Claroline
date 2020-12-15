<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class AbstractUserProgress
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\Column(type="integer")
     *
     * NOTE: this field holds the result of a progress computation in order
     *       to avoid expensive queries on every read operation.
     */
    protected $percentage = 0;

    /**
     * @ORM\Column(name="user_name")
     *
     * Note: this field retains the user name in case it is deleted
     */
    protected $userName;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $this->userName = $user->getFirstName().' '.$user->getLastName();
    }

    /**
     * @return int
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * @param int $percentage
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }
}
