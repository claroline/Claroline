<?php

namespace Innova\PathBundle\Entity;

use Claroline\AppBundle\Entity\Display\Order;
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
    use Order;

    /**
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Step", inversedBy="secondaryResources")
     * @ORM\JoinColumn(name="step_id", onDelete="CASCADE", nullable=false)
     */
    private ?Step $step = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_id", onDelete="CASCADE", nullable=false)
     */
    private ?ResourceNode $resource = null;

    public function getStep(): Step
    {
        return $this->step;
    }

    public function setStep(Step $step): void
    {
        $this->step = $step;
    }

    public function getResource(): ResourceNode
    {
        return $this->resource;
    }

    public function setResource(ResourceNode $resource): void
    {
        $this->resource = $resource;
    }
}
