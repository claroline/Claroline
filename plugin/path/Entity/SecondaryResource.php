<?php

namespace Innova\PathBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * Secondary resources.
 *
 * @ORM\Table("innova_step_secondary_resource")
 * @ORM\Entity
 */
class SecondaryResource
{
    use Uuid;

    /**
     * Identifier.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Step.
     *
     * @var \Innova\PathBundle\Entity\Step
     *
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Step", inversedBy="secondaryResources")
     * @ORM\JoinColumn(name="step_id", onDelete="CASCADE", nullable=false)
     */
    protected $step;

    /**
     * Resource.
     *
     * @var \Claroline\CoreBundle\Entity\Resource\ResourceNode
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_id", onDelete="CASCADE", nullable=false)
     */
    protected $resource;

    /**
     * Order of the secondary resource in the step.
     *
     * @var int
     *
     * @ORM\Column(name="resource_order", type="integer")
     */
    protected $order;

    /**
     * @var bool
     *
     * @ORM\Column(name="inheritance_enabled", type="boolean")
     */
    protected $inheritanceEnabled = false;

    /**
     * SecondaryResource constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

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
     * Get step.
     *
     * @return Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set step.
     *
     * @param Step $step
     *
     * @return SecondaryResource
     */
    public function setStep(Step $step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get resource.
     *
     * @return ResourceNode
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set resource.
     *
     * @param ResourceNode $resource
     *
     * @return SecondaryResource
     */
    public function setResource(ResourceNode $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param int $order
     *
     * @return SecondaryResource
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInheritanceEnabled()
    {
        return $this->inheritanceEnabled;
    }

    /**
     * @param bool $inheritanceEnabled
     *
     * @return SecondaryResource
     */
    public function setInheritanceEnabled($inheritanceEnabled)
    {
        $this->inheritanceEnabled = $inheritanceEnabled;

        return $this;
    }
}
