<?php

namespace Claroline\AgendaBundle\Entity;

use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_task')]
#[ORM\Entity]
class Task extends AbstractPlanned
{
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class, cascade: ['persist'])]
    private ?Workspace $workspace = null;

    #[ORM\Column(name: 'is_task_done', type: Types::BOOLEAN)]
    private bool $done = false;

    public static function getType(): string
    {
        return 'task';
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace = null): void
    {
        $this->workspace = $workspace;
    }

    public function setDone(bool $done): void
    {
        $this->done = $done;
    }

    public function isDone(): bool
    {
        return $this->done;
    }
}
