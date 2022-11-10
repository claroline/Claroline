<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\File;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_public_file")
 */
class PublicFile
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(name="file_size", type="integer", nullable=true)
     *
     * @var int
     */
    private $size;

    /**
     * @ORM\Column(name="filename")
     *
     * @var string
     */
    private $filename;

    /**
     * @ORM\Column(name="hash_name")
     *
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(name="mime_type", nullable=true)
     *
     * @var string
     */
    private $mimeType;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        // normalize the URL
        // we should remove \ from window envs because it requires additional escaping when used in UI.
        $this->url = str_replace('\\', '/', $url);
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }
}
