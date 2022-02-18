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

use Claroline\CoreBundle\Entity\File\PublicFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_transfer_import")
 */
class ImportFile extends AbstractTransferFile
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\File\PublicFile"
     * )
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @var PublicFile
     */
    private $file;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     *
     * @deprecated. we should use uuid instead.
     */
    private $log;

    public function setFile(PublicFile $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setLog($log)
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
