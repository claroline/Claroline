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
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * Sets the access code.
     *
     * @param bool $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }
}
