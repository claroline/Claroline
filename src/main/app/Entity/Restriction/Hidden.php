<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\ORM\Mapping as ORM;

trait Hidden
{
    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * Is the entity hidden ?
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Sets the hidden flag.
     */
    public function setHidden(bool $hidden)
    {
        $this->hidden = $hidden;
    }
}
