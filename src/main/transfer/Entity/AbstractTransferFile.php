<?php

namespace Claroline\TransferBundle\Entity;

use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractTransferFile implements TransferFileInterface
{
    use Id;
    use Uuid;
    use Creator;
    use CreatedAt;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected $name;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected $action;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected $status = self::PENDING;

    /**
     * @var string
     */
    #[ORM\Column(name: 'file_format', type: Types::STRING)]
    protected $format;

    /**
     * Extra data required to process the import/export (eg. parent directory for directory creation).
     *
     *
     * @var mixed
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected $extra;

    /**
     * @var DateTimeInterface
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected $executionDate;

    /**
     *
     * @var Workspace
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    protected $workspace;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action)
    {
        $this->action = $action;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    public function setFormat(string $format)
    {
        $this->format = $format;
    }

    public function getExecutionDate(): ?DateTimeInterface
    {
        return $this->executionDate;
    }

    public function setExecutionDate(?DateTimeInterface $date = null)
    {
        $this->executionDate = $date;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }
}
