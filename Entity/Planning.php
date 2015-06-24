<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Planning
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_planning")
 */
class Planning
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var datetime $startTime
     *
     * @ORM\Column(name="start_time", type="datetime")
     */
    private $startTime;

    /**
     * @var datetime $endTime
     *
     * @ORM\Column(name="end_time", type="datetime")
     */
    private $endTime;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Groupes")
     */
    private $group;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set startTime
     *
     * @param datetime $sartTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * Get startTime
     *
     * @return datetime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param datetime $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * Get endTime
     *
     * @return datetime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(\UJM\ExoBundle\Entity\Groupes $group)
    {
        $this->group = $group;
    }
}
