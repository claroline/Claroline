<?php

namespace Claroline\LogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="claro_log_operational")
 */
class OperationalLog extends AbstractLog
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $objectClass = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $objectId = null;

    /**
     * @ORM\Column(type="json")
     */
    private ?array $changeset = [];

    public function getObjectClass(): ?string
    {
        return $this->objectClass;
    }

    public function setObjectClass(string $objectClass): void
    {
        $this->objectClass = $objectClass;
    }

    public function getObjectId(): ?string
    {
        return $this->objectId;
    }

    public function setObjectId(string $objectId): void
    {
        $this->objectId = $objectId;
    }

    public function getChangeset(): array
    {
        return $this->changeset;
    }

    public function setChangeset(array $changeset = []): void
    {
        $this->changeset = $changeset;
    }
}
