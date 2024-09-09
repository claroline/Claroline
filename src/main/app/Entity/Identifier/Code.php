<?php

namespace Claroline\AppBundle\Entity\Identifier;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait Code
{
    /**
     * A unique code for the entity.
     *
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank()
     */
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
