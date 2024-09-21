<?php

namespace Claroline\AppBundle\Entity\Meta;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait Creator
{
    /**
     * The user who created the entity.
     *
     *
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $creator = null;

    /**
     * Returns the entity creator.
     */
    public function getCreator(): ?User
    {
        return $this->creator;
    }

    /**
     * Sets the entity creator.
     */
    public function setCreator(User $creator = null): void
    {
        $this->creator = $creator;
    }
}
