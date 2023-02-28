<?php

namespace Claroline\AppBundle\Entity\Identifier;

use Doctrine\ORM\Mapping as ORM;

trait Id
{
    /**
     * An auto generated unique identifier.
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
