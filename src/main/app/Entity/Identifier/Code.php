<?php

namespace Claroline\AppBundle\Entity\Identifier;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait Code
{
    /**
     * An unique code for the entity.
     *
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $code;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }
}
