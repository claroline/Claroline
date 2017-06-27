<?php

namespace Claroline\CoreBundle\Entity\Model;

use Ramsey\Uuid\Uuid;

/**
 * Gives an entity the ability to have an UUID.
 */
trait UuidTrait
{
    /**
     * @var string
     *
     * @ORM\Column("uuid", type="string", length=36, unique=true)
     */
    private $uuid;

    /**
     * Gets UUID.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Sets UUID.
     *
     * @param $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    public function refreshUuid()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }
}
