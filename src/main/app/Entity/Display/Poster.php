<?php

namespace Claroline\AppBundle\Entity\Display;

use Doctrine\ORM\Mapping as ORM;

trait Poster
{
    /**
     * @ORM\Column(name="poster", nullable=true)
     */
    protected ?string $poster = null;

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(string $poster = null): void
    {
        $this->poster = $poster;
    }
}
