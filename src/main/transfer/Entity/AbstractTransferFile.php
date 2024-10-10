<?php

namespace Claroline\TransferBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractTransferFile implements TransferFileInterface
{
    use Id;
    use Uuid;
    use Creator;
    use CreatedAt;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $name = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $action = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected string $status = self::PENDING;

    #[ORM\Column(name: 'file_format', type: Types::STRING)]
    protected ?string $format = null;

    /**
     * Extra data required to process the import/export (eg. parent directory for directory creation).
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected ?array $extra = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $executionDate = null;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    protected ?Workspace $workspace = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getExtra(): ?array
    {
        return $this->extra;
    }

    public function setExtra(?array $extra): void
    {
        $this->extra = $extra;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getExecutionDate(): ?\DateTimeInterface
    {
        return $this->executionDate;
    }

    public function setExecutionDate(?\DateTimeInterface $date = null): void
    {
        $this->executionDate = $date;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace = null): void
    {
        $this->workspace = $workspace;
    }
}
