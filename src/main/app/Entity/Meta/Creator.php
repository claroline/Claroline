<?php

namespace Claroline\AppBundle\Entity\Meta;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait Creator
{
    /**
     * The user who created the entity.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @var User
     */
    protected $creator;

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
    public function setCreator(User $creator = null)
    {
        $this->creator = $creator;
    }
}
