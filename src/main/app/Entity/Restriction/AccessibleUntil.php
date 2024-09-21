<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait AccessibleUntil
{
    /**
     * @var DateTimeInterface
     */
    #[ORM\Column(name: 'accessible_until', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected $accessibleUntil;

    /**
     * Returns the resource accessible until date.
     */
    public function getAccessibleUntil(): ?DateTimeInterface
    {
        return $this->accessibleUntil;
    }

    /**
     * Sets the resource accessible until date.
     */
    public function setAccessibleUntil(?DateTimeInterface $accessibleUntil = null): void
    {
        $this->accessibleUntil = $accessibleUntil;
    }
}
