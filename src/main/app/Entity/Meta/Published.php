<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait Published
{
    #[ORM\Column(name: 'published', type: Types::BOOLEAN, options: ['default' => 1])]
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
