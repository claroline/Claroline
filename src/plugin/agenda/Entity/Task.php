<?php

namespace Claroline\AgendaBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_task")
 * @ORM\Entity
 */
class Task extends AbstractPlanned
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(name="is_task_done", type="boolean")
     */
    private $done = false;

    public function setDone(bool $done)
    {
        $this->done = $done;
    }

    public function isDone(): bool
    {
        return $this->done;
    }
}
