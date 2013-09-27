<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Step2ExcludedResource
 *
 * @ORM\Table("innova_step2excludedResource")
 * @ORM\Entity(repositoryClass="Innova\PathBundle\Repository\Step2ExcludedResourceRepository")
 */
class Step2ExcludedResource
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
    * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Step", inversedBy="excludedResources")
    */
    private $step;

    /**
    * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
    */
    private $resourceNode;

    /**
     * Set step
     *
     * @param  \Innova\PathBundle\Entity\Step $step
     * @return Step2ExcludedResource
     */
    public function setStep(\Innova\PathBundle\Entity\Step $step = null)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \Innova\PathBundle\Entity\Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set resourceNode
     *
     * @param  \Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode
     * @return Step2ExcludedResource
     */
    public function setResourceNode(\Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;

        return $this;
    }

    /**
     * Get resourceNode
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }
}
