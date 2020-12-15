<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\ORM\Mapping as ORM;

trait AccessibleFrom
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="accessible_from", type="datetime", nullable=true)
     */
    private $accessibleFrom;

    /**
     * Returns the resource accessible from date.
     *
     * @return \DateTime
     */
    public function getAccessibleFrom()
    {
        return $this->accessibleFrom;
    }

    /**
     * Sets the resource accessible from date.
     *
     * @param \DateTime $accessibleFrom
     */
    public function setAccessibleFrom(\DateTime $accessibleFrom = null)
    {
        $this->accessibleFrom = $accessibleFrom;
    }
}
