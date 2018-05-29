<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\ORM\Mapping as ORM;

trait AccessibleUntil
{
    /**
     * @var \DateTime
     * @ORM\Column(name="accessible_until", type="datetime", nullable=true)
     */
    private $accessibleUntil;

    /**
     * Returns the resource accessible until date.
     *
     * @return \DateTime
     */
    public function getAccessibleUntil()
    {
        return $this->accessibleUntil;
    }

    /**
     * Sets the resource accessible until date.
     *
     * @param \DateTime $accessibleUntil
     */
    public function setAccessibleUntil(\DateTime $accessibleUntil = null)
    {
        $this->accessibleUntil = $accessibleUntil;
    }
}
