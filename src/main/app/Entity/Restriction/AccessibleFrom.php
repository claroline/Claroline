<?php

namespace Claroline\AppBundle\Entity\Restriction;

use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait AccessibleFrom
{
    /**
     * @var DateTimeInterface
     */
    #[ORM\Column(name: 'accessible_from', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected $accessibleFrom;

    /**
     * Returns the resource accessible from date.
     */
    public function getAccessibleFrom(): ?DateTimeInterface
    {
        return $this->accessibleFrom;
    }

    /**
     * Sets the resource accessible from date.
     */
    public function setAccessibleFrom(?DateTimeInterface $accessibleFrom = null): void
    {
        $this->accessibleFrom = $accessibleFrom;
    }
}
