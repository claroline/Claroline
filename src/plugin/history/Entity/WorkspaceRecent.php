<?php

namespace Claroline\HistoryBundle\Entity;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\HistoryBundle\Repository\WorkspaceRecentRepository")
 * @ORM\Table(name="claro_workspace_recent", indexes={
 *     @ORM\Index(name="user_idx", columns={"user_id"})
 * })
 */
class WorkspaceRecent extends AbstractRecent
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * @param Workspace $workspace
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }
}
