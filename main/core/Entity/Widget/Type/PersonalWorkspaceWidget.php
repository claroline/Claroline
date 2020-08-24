<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * ResourceWidget.
 *
 * Permits to embedded a Resource.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_widget_resource")
 */
class PersonalWorkspaceWidget extends AbstractWidget
{
    /**
     * Choose the resourceNode to display in widget.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="node_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @var ResourceNode
     */
    private $resourceNode = null;

    /**
     * Choose to display the header of the resource.
     *
     * @ORM\Column(type="boolean")
     */
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
