<?php

namespace Claroline\AppBundle\Entity\Identifier;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid as BaseUuid;

/**
 * Gives an entity the ability to have an UUID.
 */
trait Uuid
{
    /**
     * @var string
     *
     * @ORM\Column("uuid", type="string", length=36, unique=true)
     */
    protected $uuid;

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
    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Generates a new UUID.
     */
    public function refreshUuid()
    {
        $this->uuid = $this->generateUuid();
    }

    public function generateUuid(): string
    {
        return BaseUuid::uuid4()->toString();
    }
}
