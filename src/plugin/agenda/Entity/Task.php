<?php

namespace Claroline\AgendaBundle\Entity;

use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_task")
 */
class Task extends AbstractPlanned
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * @ORM\Column(name="is_task_done", type="boolean")
     *
     * @var bool
     */
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
