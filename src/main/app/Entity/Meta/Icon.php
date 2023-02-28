<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait Icon
{
    /**
     * @ORM\Column(name="icon", nullable=true)
     *
     * @var string
     */
    protected $icon;

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon = null): void
    {
        $this->icon = $icon;
    }
}
