<?php

namespace Claroline\ClacoFormBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_clacoformbundle_comment')]
#[ORM\Entity]
class Comment
{
    use Id;
    use Uuid;

    public const PENDING = 0;
    public const VALIDATED = 1;
    public const BLOCKED = 2;

    #[ORM\Column(type: Types::TEXT)]
    protected ?string $content = null;

    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $user = null;

    #[ORM\JoinColumn(name: 'entry_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Entry::class, inversedBy: 'comments')]
    protected ?Entry $entry = null;

    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(name: 'edition_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $editionDate = null;

    /**
     * @var int
     */
    #[ORM\Column(name: 'comment_status', type: Types::INTEGER)]
    protected $status;

    /**
     * Comment constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * Get entry.
     *
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Set entry.
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTimeInterface $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get edition date.
     */
    public function getEditionDate(): ?\DateTimeInterface
    {
        return $this->editionDate;
    }

    /**
     * Set edition date.
     */
    public function setEditionDate(?\DateTimeInterface $editionDate = null): void
    {
        $this->editionDate = $editionDate;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
