<?php

namespace Claroline\AppBundle\Entity\Display;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait Hidden
{
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    protected bool $hidden = false;

    /**
     * Is the entity hidden ?
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Sets the hidden flag.
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
