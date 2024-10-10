<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TransferBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\TransferBundle\Finder\ImportFileType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_transfer_import')]
#[ORM\Entity]
#[CrudEntity(finderClass: ImportFileType::class)]
class ImportFile extends AbstractTransferFile
{
    #[ORM\JoinColumn(name: 'file_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: PublicFile::class)]
    private ?PublicFile $file = null;

    /**
     * @deprecated. we should use uuid instead.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $log = null;

    public function setFile(PublicFile $file): void
    {
        $this->file = $file;
    }

    public function getFile(): ?PublicFile
    {
        return $this->file;
    }

    public function setLog(?string $log): void
    {
        $this->log = $log;
    }

    public function getLog(): string
    {
        if ($this->log) {
            return $this->log;
        }

        return $this->uuid;
    }
}
