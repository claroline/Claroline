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
class ResourceWidget extends AbstractWidget
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
     * Get resource node.
     *
     * @return ResourceNode
     */
    public function getUser()
    {
        return $this->resourceNode;
    }

    /**
     * Set resource node.
     *
     * @param ResourceNode $resourceNode
     */
    public function setUser(ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;
    }
}
