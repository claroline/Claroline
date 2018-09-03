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
    private $thumbnail;

    /**
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param string $poster
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }
}
