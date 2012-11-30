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
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource"
     * )
     * @ORM\JoinTable(name="claro_resource_activity",
     *      joinColumns={@ORM\JoinColumn(name="activity_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")}
     * )
     */
    protected $resources;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
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

    public function addResource(AbstractResource $resource)
    {
        foreach ($this->resources as $activityResource){
            if ($resource->getPath() == $activityResource->getPath()){
                throw new \Exception('This resource was already added in the current activity');
            }
        }
        $this->resources->add($resource);
    }

    public function removeResource(AbstractResource $resource)
    {
        $this->resources->removeElement($resource);
    }

    public function getResources()
    {
        return $this->resources;
    }
}