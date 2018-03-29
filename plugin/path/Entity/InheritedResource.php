<?php

namespace Innova\PathBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * Inherited resources.
 *
 * @ORM\Table("innova_step_inherited_resources")
 * @ORM\Entity
 */
class InheritedResource
{
    use Uuid;

    /**
     * Unique identifier.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Depth of the original step of the resource.
     *
     * @var int
     *
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * Step.
     *
     * @var \Innova\PathBundle\Entity\Step
     *
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Step", inversedBy="inheritedResources")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $step;

    /**
     * Resource.
     *
     * @var \Claroline\CoreBundle\Entity\Resource\ResourceNode
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $resource;

    /**
     * Order of the inherited resource in the step.
     *
     * @var int
     *
     * @ORM\Column(name="resource_order", type="integer", options={"default" = 0})
     */
    protected $order;

    /**
     * Uuid of the original secondary resource.
     *
     * @var string
     *
     * @ORM\Column("source_uuid", type="string", length=36, nullable=true)
     */
    protected $sourceUuid;

    /**
     * InheritedResource constructor.
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
     * Get level.
     *
     * @return int
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set level.
     *
     * @param int $lvl
     *
     * @return \Innova\PathBundle\Entity\InheritedResource
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get resource.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set resource.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resource
     *
     * @return \Innova\PathBundle\Entity\InheritedResource
     */
    public function setResource(ResourceNode $resource)
    {
        $this->resource = $resource;

        return $this;
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
     * @return \Innova\PathBundle\Entity\InheritedResource
     */
    public function setStep(Step $step = null)
    {
        $this->step = $step;

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
     * @return InheritedResource
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get source uuid.
     *
     * @return string
     */
    public function getSourceUuid()
    {
        return $this->sourceUuid;
    }

    /**
     * Set source uuid.
     *
     * @param string $sourceUuid
     *
     * @return InheritedResource
     */
    public function setSourceUuid($sourceUuid)
    {
        $this->sourceUuid = $sourceUuid;

        return $this;
    }
}
