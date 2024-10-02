<?php

namespace Claroline\LogBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Finder\SecurityLogType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_log_security')]
#[ORM\Entity]
#[CrudEntity(finderClass: SecurityLogType::class)]
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
