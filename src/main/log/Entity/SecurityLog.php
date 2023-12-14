<?php

namespace Claroline\LogBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="claro_log_security")
 */
class SecurityLog extends AbstractLog
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     *
     * @ORM\JoinColumn(name="target_id", referencedColumnName="id", onDelete="SET NULL")
     */
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
