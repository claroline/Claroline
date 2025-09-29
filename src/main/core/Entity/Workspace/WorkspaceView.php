<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\AbstractUserView;
use Claroline\CoreBundle\Finder\WorkspaceViewType;
use Claroline\CoreBundle\Repository\WorkspaceViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('claro_workspace_view')]
#[ORM\Entity(repositoryClass: WorkspaceViewRepository::class)]
#[CrudEntity(finderClass: WorkspaceViewType::class)]
class WorkspaceView extends AbstractUserView
{
    #[ORM\JoinColumn(name: 'workspace_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private ?Workspace $workspace = null;

    public function getSubject(): Workspace
    {
        return $this->workspace;
    }

    /** @param Workspace $subject */
    public function setSubject(object $subject): void
    {
        $this->workspace = $subject;
    }
}
