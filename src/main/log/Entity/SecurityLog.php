<?php

namespace Claroline\LogBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_log_security')]
#[ORM\Entity]
class SecurityLog extends AbstractLog
{
    #[ORM\JoinColumn(name: 'target_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $target = null;

    public function getTarget(): ?User
    {
        return $this->target;
    }

    public function setTarget(?User $target): self
    {
        $this->target = $target;

        return $this;
    }
}
