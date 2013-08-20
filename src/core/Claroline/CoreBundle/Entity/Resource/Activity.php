<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ActivityRepository")
 * @ORM\Table(name="claro_activity")
 */
class Activity extends AbstractResource
{
    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="instruction")
     */
    protected $instructions;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceActivity",
     *     mappedBy="activity"
     * )
     */
    protected $resourcesActivities;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

    public function __construct()
    {
        $this->resourcesActivities = new ArrayCollection();
    }

    /**
     * Returns the instruction.
     *
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Sets the instruction.
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;
    }

    public function addResourceActivity(ResourceActivity $newResourceActivity)
    {
        $this->resourcesActivities->add($newResourceActivity);
    }

    public function removeResourceActivity(ResourceActivity $resourceActivity)
    {
        $this->resourcesActivities->removeElement($resourceActivity);
    }

    public function getResourceActivities()
    {
        return $this->resourcesActivities;
    }

    public function setStartDate($date)
    {
        $this->startDate = $date;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setEndDate($date)
    {
        $this->endDate = $date;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }
}
