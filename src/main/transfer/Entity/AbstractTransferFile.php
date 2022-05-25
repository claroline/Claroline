<?php

namespace Claroline\TransferBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractTransferFile implements TransferFileInterface
{
    use Id;
    use Uuid;
    use Creator;
    use CreatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $action;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status = self::PENDING;

    /**
     * @ORM\Column(name="file_format", type="string")
     *
     * @var string
     */
    protected $format;

    /**
     * Extra data required to process the import/export (eg. parent directory for directory creation).
     *
     * @ORM\Column(type="json", nullable=true)
     *
     * @var mixed
     */
    protected $extra;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTimeInterface
     */
    protected $executionDate;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Workspace
     */
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

    public function getExecutionDate(): ?\DateTimeInterface
    {
        return $this->executionDate;
    }

    public function setExecutionDate(?\DateTimeInterface $date = null)
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
