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
     * @ORM\Column(type="string")
     */
    protected $instruction;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceActivity",
     *      mappedBy="activity"
     * )
     */
    protected $resourcesActivity;

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
        $this->resourcesActivity = new ArrayCollection();
    }

    /**
     * Returns the instruction.
     *
     * @return string
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * Sets the instruction.
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }

    public function addResourceActivity(ResourceActivity $resourceActivity)
    {/*
        foreach ($this->resources as $activityResource){
            if ($resource->getPath() == $activityResource->getPath()){
                throw new \Exception('This resource was already added in the current activity');
            }
        }*/
        $this->resources->add($resourceActivity);
    }

    public function removeResource(ResourceActivity $resourceActivity)
    {
        $this->resources->removeElement($resourceActivity);
    }

    public function getResourceActivity()
    {
        return $this->resourcesActivity;
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