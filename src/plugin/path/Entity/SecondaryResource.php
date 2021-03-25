<?php

namespace Innova\PathBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
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
    use Id;

    /**
     * Step.
     *
     * @var Step
     *
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Step", inversedBy="secondaryResources")
     * @ORM\JoinColumn(name="step_id", onDelete="CASCADE", nullable=false)
     */
    private $step;

    /**
     * Resource.
     *
     * @var ResourceNode
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_id", onDelete="CASCADE", nullable=false)
     */
    private $resource;

    /**
     * Order of the secondary resource in the step.
     *
     * @var int
     *
     * @ORM\Column(name="resource_order", type="integer")
     */
    private $order;

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
}
