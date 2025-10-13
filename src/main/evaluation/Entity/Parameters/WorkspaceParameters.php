<?php

namespace Claroline\EvaluationBundle\Entity\Parameters;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\Certified;
use Doctrine\ORM\Mapping as ORM;

/**
 * Store evaluation parameters for evaluated workspaces.
 */
#[ORM\Entity]
#[ORM\Table(name: 'claro_evaluation_workspace_parameters')]
class WorkspaceParameters extends AbstractEvaluationParameters
{
    use Certified;

    #[ORM\JoinColumn(name: 'workspace_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private ?Workspace $workspace = null;

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }
}
