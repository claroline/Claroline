<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_activity")
 */
class Activity extends AbstractResource
{
    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", name="instruction")
     */
    protected $instructions;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceActivity",
     *      mappedBy="activity"
     * )
     */
    protected $resourcesActivities;

    /**
     * @ORM\Column(type="datetime", name="date_beginning")
     */
    protected $dateBeginning;

    /**
     * @ORM\Column(type="datetime", name="date_end")
     */
    protected $dateEnd;


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
        foreach ($this->resourcesActivities as $resourceActivity){
            if ($resourceActivity->getResource()->getPath() == $newResourceActivity->getResource()->getPath()){
                throw new \Exception('This resource was already added in the current activity');
            }
        }
        $this->resources->add($resourceActivity);
    }

    public function removeResource(ResourceActivity $resourceActivity)
    {
        $this->resources->removeElement($resourceActivity);
    }

    public function getResourceActivities()
    {
        return $this->resourcesActivities;
    }

    public function setDateBeginning($date)
    {
        $this->dateBeginning = $date;
    }

    public function getDateBeginning()
    {
        return $this->dateBeginning;
    }

    public function setDateEnd($date)
    {
        $this->dateEnd = $date;
    }

    public function getDateEnd()
    {
        return $this->dateEnd;
    }
}