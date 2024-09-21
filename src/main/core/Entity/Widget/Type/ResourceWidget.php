<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * ResourceWidget.
 *
 * Permits to embedded a Resource.
 */
#[ORM\Table(name: 'claro_widget_resource')]
#[ORM\Entity]
class ResourceWidget extends AbstractWidget
{
    /**
     * Choose the resourceNode to display in widget.
     *
     *
     * @var ResourceNode
     */
    #[ORM\JoinColumn(name: 'node_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private $resourceNode = null;

    /**
     * Choose to display the header of the resource.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private $showResourceHeader = false;

    /**
     * Get resource node.
     *
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * Set resource node.
     *
     * @param ResourceNode $resourceNode
     */
    public function setResourceNode(ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;
    }

    /**
     * Get showResourceHeader.
     */
    public function getShowResourceHeader()
    {
        return $this->showResourceHeader;
    }

    /**
     * Set showResourceHeader.
     */
    public function setShowResourceHeader($showResourceHeader)
    {
        $this->showResourceHeader = $showResourceHeader;
    }
}
