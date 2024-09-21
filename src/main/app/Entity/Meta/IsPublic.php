<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait IsPublic
{
    #[ORM\Column(name: 'is_public', type: 'boolean')]
    private bool $public = false;

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }
}
