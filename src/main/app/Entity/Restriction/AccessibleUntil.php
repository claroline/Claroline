<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\ORM\Mapping as ORM;

trait AccessibleUntil
{
    /**
     * @var \DateTimeInterface
     */
    #[ORM\Column(name: 'accessible_until', type: 'datetime', nullable: true)]
    protected $accessibleUntil;

    /**
     * Returns the resource accessible until date.
     */
    public function getAccessibleUntil(): ?\DateTimeInterface
    {
        return $this->accessibleUntil;
    }

    /**
     * Sets the resource accessible until date.
     */
    public function setAccessibleUntil(?\DateTimeInterface $accessibleUntil = null): void
    {
        $this->accessibleUntil = $accessibleUntil;
    }
}
