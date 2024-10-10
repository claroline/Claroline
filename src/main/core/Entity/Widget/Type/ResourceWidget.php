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
     */
    #[ORM\JoinColumn(name: 'node_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $resourceNode = null;

    /**
     * Choose to display the header of the resource.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $showResourceHeader = false;

    public function getResourceNode(): ?ResourceNode
    {
        return $this->resourceNode;
    }

    public function setResourceNode(?ResourceNode $resourceNode = null): void
    {
        $this->resourceNode = $resourceNode;
    }

    public function getShowResourceHeader(): bool
    {
        return $this->showResourceHeader;
    }

    public function setShowResourceHeader(bool $showResourceHeader): void
    {
        $this->showResourceHeader = $showResourceHeader;
    }
}
