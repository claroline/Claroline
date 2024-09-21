<?php

namespace Claroline\ClacoFormBundle\Entity;

use Doctrine\DBAL\Types\Types;
use DateTime;
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
    protected $content;

    /**
     *
     *
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'SET NULL', nullable: true)]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected $user;

    /**
     *
     *
     * @var Entry
     */
    #[ORM\JoinColumn(name: 'entry_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Entry::class, inversedBy: 'comments')]
    protected $entry;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE)]
    protected $creationDate;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'edition_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected $editionDate;

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

    /**
     * Get creation date.
     *
     * @return DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set creation date.
     */
    public function setCreationDate(DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get edition date.
     *
     * @return DateTime
     */
    public function getEditionDate()
    {
        return $this->editionDate;
    }

    /**
     * Set edition date.
     */
    public function setEditionDate(DateTime $editionDate = null)
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
