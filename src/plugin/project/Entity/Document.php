<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ProjectBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_project_document')]
#[ORM\Entity]
class Document
{
    use Id;
    use Uuid;

    /**
     * The drop this document is attached to.
     */
    #[ORM\ManyToOne(targetEntity: Drop::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Drop $drop = null;

    /**
     * Type of content submitted by the learner.
     *   - file : an uploaded file (PDF, Word, image, video, audio)
     *   - html : rich text content entered via the platform WYSIWYG editor
     *   - url  : an external URL
     *   - media : a media file uploaded directly
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $type = 'file';

    /**
     * Metadata of the uploaded file (name, size, mime type, storage path, etc.).
     * Only set when type is "file" or "media".
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $file = null;

    /**
     * External URL submitted by the learner.
     * Only set when type is "url".
     */
    #[ORM\Column(type: Types::STRING, length: 2048, nullable: true)]
    private ?string $url = null;

    /**
     * Rich text content entered by the learner via the WYSIWYG editor.
     * Only set when type is "html".
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    /**
     * Date and time the document was added to the drop.
     */
    #[ORM\Column(name: 'drop_date', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $dropDate;

    public function __construct()
    {
        $this->refreshUuid();

        $this->dropDate = new \DateTime();
    }

    public function getDrop(): ?Drop
    {
        return $this->drop;
    }

    public function setDrop(?Drop $drop): void
    {
        $this->drop = $drop;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getFile(): ?array
    {
        return $this->file;
    }

    public function setFile(?array $file): void
    {
        $this->file = $file;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getDropDate(): \DateTimeInterface
    {
        return $this->dropDate;
    }

    public function setDropDate(\DateTimeInterface $dropDate): void
    {
        $this->dropDate = $dropDate;
    }
}
