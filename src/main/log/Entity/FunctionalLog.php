<?php

namespace Claroline\LogBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_log_functionnal')]
#[ORM\Entity]
class FunctionalLog extends AbstractLog
{
    
    #[ORM\JoinColumn(name: 'resource_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $resource = null;

    
    #[ORM\JoinColumn(name: 'workspace_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private ?Workspace $workspace = null;

    public function getResource(): ?ResourceNode
    {
        return $this->resource;
    }

    public function setResource(?ResourceNode $resource): void
    {
        $this->resource = $resource;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }
}
