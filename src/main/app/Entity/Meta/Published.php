<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait Published
{
    #[ORM\Column(name: 'published', type: 'boolean', options: ['default' => 1])]
    protected bool $published = true;

    /**
     * Returns whether the entity is published.
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * Sets the entity published state.
     */
    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }
}
