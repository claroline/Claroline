<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait Poster
{
    /**
     * @ORM\Column(name="poster", nullable=true)
     *
     * @var string
     */
    protected $poster = null;

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(?string $poster = null)
    {
        $this->poster = $poster;
    }
}
