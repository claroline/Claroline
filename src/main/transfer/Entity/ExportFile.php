<?php

namespace Claroline\TransferBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\TransferBundle\Finder\ExportFileType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_transfer_export')]
#[ORM\Entity]
#[CrudEntity(finderClass: ExportFileType::class)]
class ExportFile extends AbstractTransferFile
{
    /**
     * The URL to the generated file with export data.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $url = null;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getLog(): string
    {
        return $this->uuid;
    }
}
