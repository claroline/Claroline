<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait UpdatedAt
{
    /**
     * The last update date of the entity.
     */
    #[ORM\Column(name: 'updatedAt', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $updatedAt = null;

    /**
     * Returns the entity's last update date.
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Sets the entity's last update date.
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }
}
