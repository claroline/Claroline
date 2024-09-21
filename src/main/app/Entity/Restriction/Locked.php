<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait Locked
{
    #[ORM\Column(name: 'is_locked', type: Types::BOOLEAN, options: ['default' => 0])]
    protected bool $locked = false;

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }
}
