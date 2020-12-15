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
    private $code;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }
}
