<?php

namespace Claroline\AppBundle\Entity\Identifier;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid as BaseUuid;

/**
 * Gives an entity the ability to have a UUID.
 */
trait Uuid
{
    #[ORM\Column('uuid', type: Types::STRING, length: 36, unique: true)]
    protected string $uuid;

    /**
     * Gets UUID.
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * Sets UUID.
     */
    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * Generates a new UUID.
     */
    public function refreshUuid(): void
    {
        $this->uuid = BaseUuid::uuid4()->toString();
    }
}
