<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait Thumbnail
{
    /**
     * @ORM\Column(name="thumbnail", nullable=true)
     *
     * @var string
     */
    protected $thumbnail = null;

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail = null)
    {
        $this->thumbnail = $thumbnail;
    }
}
