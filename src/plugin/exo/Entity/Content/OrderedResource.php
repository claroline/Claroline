<?php

namespace UJM\ExoBundle\Entity\Content;

use Claroline\AppBundle\Entity\Meta\Order;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base class to create an ordered list of ResourceNodes in an entity.
 *
 * @ORM\MappedSuperclass()
 */
abstract class OrderedResource
{
    use Order;

    /**
     * Linked ResourceNode.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $resourceNode;

    /**
     * Gets id.
     *
     * @return int
     */
    abstract public function getId();

    /**
     * Sets resource node.
     */
    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    /**
     * Gets resource node.
     *
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }
}
