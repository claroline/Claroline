<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait Archived
{
    /**
     * @ORM\Column(name="archived", type="boolean")
     */
    protected bool $archived = false;

    /**
     * Returns whether the entity is archived.
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * Sets the entity archived state.
     */
    public function setArchived(bool $archived): void
    {
        $this->archived = $archived;
    }
}
