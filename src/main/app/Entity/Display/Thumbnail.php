<?php

namespace Claroline\AppBundle\Entity\Display;

use Doctrine\ORM\Mapping as ORM;

trait Thumbnail
{
    #[ORM\Column(name: 'thumbnail', nullable: true)]
    protected ?string $thumbnail = null;

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail = null): void
    {
        $this->thumbnail = $thumbnail;
    }
}
