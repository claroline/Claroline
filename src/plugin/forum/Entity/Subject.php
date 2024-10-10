<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Entity;

use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use DateTime;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'claro_forum_subject')]
#[ORM\Entity]
class Subject
{
    use Id;
    use Uuid;

    #[ORM\Column]
    protected $title;

    /**
     * @var DateTimeInterface
     */
    #[ORM\Column(name: 'created', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    protected $creationDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    protected $updated;

    /**
     *
     * @var Forum
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'subjects')]
    protected ?Forum $forum = null;

    /**
     *
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'subject')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected Collection $messages;

    /**
     *
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    protected ?User $creator = null;

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    protected $sticked = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    protected $closed = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    protected $flagged = false;

    /**
     * @var int
     */
    #[ORM\Column(type: Types::INTEGER)]
    protected $viewCount = 0;

    /**
     *
     * @var PublicFile
     *
     * @todo only store file URL
     */
    #[ORM\JoinColumn(name: 'poster_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: PublicFile::class)]
    protected ?PublicFile $poster = null;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING)]
    protected $moderation = Forum::VALIDATE_NONE;

    public function __construct()
    {
        $this->refreshUuid();

        $this->messages = new ArrayCollection();
        $this->creationDate = new DateTime();
        $this->updated = new DateTime();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setForum(Forum $forum): void
    {
        $this->forum = $forum;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function getFirstMessage(): ?Message
    {
        $first = null;
        foreach ($this->messages as $message) {
            if ($message->isFirst()) {
                $first = $message;
                break;
            }
        }

        return $first;
    }

    public function setCreator(User $creator): void
    {
        $this->creator = $creator;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function addMessage(Message $message): void
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
        }
    }

    public function setSticked(bool $sticked)
    {
        $this->sticked = $sticked;
    }

    public function isSticked(): bool
    {
        return $this->sticked;
    }

    public function setCreationDate($date)
    {
        $this->creationDate = $date;
    }

    public function setModificationDate($date)
    {
        $this->updated = $date;
    }

    public function getModificationDate()
    {
        return $this->updated;
    }

    public function setClosed($isClosed)
    {
        $this->closed = $isClosed;
    }

    public function isClosed()
    {
        return $this->closed;
    }

    public function setFlagged($bool)
    {
        $this->flagged = $bool;
    }

    public function isFlagged()
    {
        return $this->flagged;
    }

    public function getViewCount()
    {
        return $this->viewCount;
    }

    public function setViewCount($viewCount)
    {
        $this->viewCount = $viewCount;
    }

    public function setPoster(?PublicFile $file = null)
    {
        $this->poster = $file;
    }

    public function getPoster()
    {
        return $this->poster;
    }

    public function setModerated($moderated)
    {
        $this->moderation = $moderated;
    }

    public function getModerated()
    {
        return $this->moderation ? $this->moderation : Forum::VALIDATE_NONE;
    }
}
