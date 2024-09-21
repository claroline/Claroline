<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\EvaluationBundle\Entity\AbstractUserEvaluation;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_workspace_evaluation')]
#[ORM\UniqueConstraint(name: 'workspace_user_evaluation', columns: ['workspace_id', 'user_id'])]
#[ORM\Entity]
class Evaluation extends AbstractUserEvaluation
{
    use Uuid;

    
    #[ORM\JoinColumn(name: 'workspace_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private ?Workspace $workspace = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->workspace?->getEstimatedDuration();
    }
}
