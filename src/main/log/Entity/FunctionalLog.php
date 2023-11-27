<?php

namespace Claroline\LogBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="claro_log_functionnal")
 */
class FunctionalLog extends AbstractLog
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     *
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private ?ResourceNode $resource = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     *
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id", onDelete="SET NULL")
     */
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
