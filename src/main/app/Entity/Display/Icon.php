<?php

namespace Claroline\AppBundle\Entity\Display;

use Doctrine\ORM\Mapping as ORM;

trait Icon
{
    #[ORM\Column(name: 'icon', nullable: true)]
    protected ?string $icon = null;

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon = null): void
    {
        $this->icon = $icon;
    }
}
