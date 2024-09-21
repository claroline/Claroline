<?php

namespace Claroline\AppBundle\Entity\Identifier;

use Doctrine\ORM\Mapping as ORM;

trait Code
{
    /**
     * A unique code for the entity.
     */
    #[ORM\Column(unique: true)]
    protected ?string $code = null;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}
