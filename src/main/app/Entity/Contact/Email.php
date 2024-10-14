<?php

namespace Claroline\AppBundle\Entity\Contact;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait Email
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $email = null;

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
