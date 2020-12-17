<?php

namespace Claroline\AppBundle\Entity\Meta;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait Creator
{
    /**
     * The user who created the entity.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @var User
     */
    private $creator;

    /**
     * Returns the entity creator.
     *
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Sets the entity creator.
     *
     * @param User $creator
     */
    public function setCreator(User $creator = null)
    {
        $this->creator = $creator;
    }
}
