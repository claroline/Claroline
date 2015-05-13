<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;

/**
 * @ORM\MappedSuperclass
 */
class AbstractCompetencyProgress
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="HeVinci\CompetencyBundle\Entity\Competency")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $competency;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="HeVinci\CompetencyBundle\Entity\Level")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $level;

    /**
     * @ORM\Column(type="integer")
     *
     * NOTE: this field holds the result of a progress computation in order
     *       to avoid expensive queries on every read operation.
     */
    protected $percentage = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Competency
     */
    public function getCompetency()
    {
        return $this->competency;
    }

    /**
     * @param Competency $competency
     */
    public function setCompetency(Competency $competency)
    {
        $this->competency = $competency;
    }

    /**
     * @return Level
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param Level $level
     */
    public function setLevel(Level $level)
    {
        $this->level = $level;
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
}
