<?php

namespace UJM\ExoBundle\Entity\Content;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Model\OrderTrait;

/**
 * Base class to create an ordered list of ResourceNodes in an entity.
 *
 * @ORM\MappedSuperclass()
 */
abstract class OrderedResource
{
    use OrderTrait;

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
     *
     * @param ResourceNode $resourceNode
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
