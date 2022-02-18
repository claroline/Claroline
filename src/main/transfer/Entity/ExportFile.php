<?php

namespace Claroline\TransferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_transfer_export")
 */
class ExportFile extends AbstractTransferFile
{
    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
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
