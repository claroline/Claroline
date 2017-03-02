<?php

namespace UJM\ExoBundle\Library\Model;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

/**
 * Gives an entity the ability to hold a content (either by using a ResourceNode or by using raw data).
 */
trait ContentTrait
{
    /**
     * The content data.
     *
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;

    /**
     * A resource node holding the content.
     *
     * @var ResourceNode
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resourceNode_id", referencedColumnName="id", nullable=true)
     */
    private $resourceNode;

    /**
     * Sets data.
     *
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Gets data.
     *
     * @returns string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets ResourceNode.
     *
     * @param ResourceNode $resourceNode
     */
    public function setResourceNode(ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;
    }

    /**
     * Gets ResourceNode.
     *
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }
}
