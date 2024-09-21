<?php

namespace Claroline\AgendaBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_task')]
#[ORM\Entity]
class Task extends AbstractPlanned
{
    /**
     *
     * @var Workspace
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class, cascade: ['persist'])]
    private ?Workspace $workspace = null;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_task_done', type: Types::BOOLEAN)]
    private $done = false;

    public static function getType(): string
    {
        return 'task';
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function setDone(bool $done)
    {
        $this->done = $done;
    }

    public function isDone(): bool
    {
        return $this->done;
    }
}
