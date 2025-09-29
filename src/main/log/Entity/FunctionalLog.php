<?php

namespace Claroline\LogBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\LogBundle\Finder\FunctionalLogType;
use Claroline\LogBundle\Repository\FunctionalLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_log_functionnal')]
#[ORM\Entity(repositoryClass: FunctionalLogRepository::class)]
#[CrudEntity(finderClass: FunctionalLogType::class)]
class FunctionalLog extends AbstractLog
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $contextId = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $objectClass = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $objectId = null;

    public function getContextId(): ?string
    {
        return $this->contextId;
    }

    public function setContextId(?string $contextId): void
    {
        $this->contextId = $contextId;
    }

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
}
