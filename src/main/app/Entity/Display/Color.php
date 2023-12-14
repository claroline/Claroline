<?php

namespace Claroline\AppBundle\Entity\Display;

use Doctrine\ORM\Mapping as ORM;

trait Color
{
    /**
     * @ORM\Column(name="color", nullable=true)
     */
    protected ?string $color = null;

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color = null): void
    {
        $this->color = $color;
    }
}
