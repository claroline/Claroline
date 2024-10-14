<?php

namespace Claroline\AppBundle\Entity\Contact;

use Doctrine\ORM\Mapping as ORM;

trait Phone
{
    #[ORM\Column(nullable: true)]
    protected ?string $phone = null;

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone = null): void
    {
        $this->phone = $phone;
    }
}
