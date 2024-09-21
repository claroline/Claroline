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

#[ORM\Table(name: 'claro_public_file')]
#[ORM\Entity]
class PublicFile
{
    use Id;
    use Uuid;

    /**
     * @var int
     */
    #[ORM\Column(name: 'file_size', type: 'integer', nullable: true)]
    private $size;

    /**
     * @var string
     */
    #[ORM\Column(name: 'filename')]
    private $filename;

    /**
     * @var string
     */
    #[ORM\Column(name: 'hash_name')]
    private $url;

    /**
     * @var string
     */
    #[ORM\Column(name: 'mime_type', nullable: true)]
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
