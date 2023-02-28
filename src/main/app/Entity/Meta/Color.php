<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait Color
{
    /**
     * @ORM\Column(name="color", nullable=true)
     *
     * @var string
     */
    protected $color;

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color = null): void
    {
        $this->color = $color;
    }
}
