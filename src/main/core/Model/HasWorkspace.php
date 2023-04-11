<?php

namespace Claroline\CoreBundle\Model;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

trait HasWorkspace
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", onDelete="CASCADE")
     */
    protected ?Workspace $workspace = null;

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }
}
