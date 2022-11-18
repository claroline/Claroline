<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\ORM\Mapping as ORM;

trait Locked
{
    /**
     * @ORM\Column(name="is_locked", type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    protected $locked = false;

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }
}
