<?php

namespace Claroline\TransferBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_transfer_export')]
#[ORM\Entity]
class ExportFile extends AbstractTransferFile
{
    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private $url;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function getLog(): string
    {
        return $this->uuid;
    }
}
