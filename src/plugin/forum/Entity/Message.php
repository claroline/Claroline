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
use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Entity\AbstractMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_forum_message')]
#[ORM\Entity]
class Message extends AbstractMessage
{
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Subject::class, cascade: ['persist'], inversedBy: 'messages')]
    private ?Subject $subject = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Message::class, inversedBy: 'children')]
    private ?Message $parent = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'parent')]
    private Collection $children;

    #[ORM\Column(type: Types::STRING)]
    private string $moderation = Forum::VALIDATE_NONE;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $flagged = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $first = false;

    public function __construct()
    {
        parent::__construct();

        $this->children = new ArrayCollection();
    }

    public function setSubject(Subject $subject): void
    {
        $this->subject = $subject;
        $subject->addMessage($this);
    }

    public function getSubject(): ?Subject
    {
        if ($this->getParent()) {
            return $this->getParent()->getSubject();
        }

        return $this->subject;
    }

    public function getForum(): ?Forum
    {
        if ($this->getSubject()) {
            return $this->getSubject()->getForum();
        }

        return null;
    }

    public function setModerated($moderated): void
    {
        $this->moderation = $moderated;
    }

    public function getModerated(): string
    {
        return $this->moderation ?: Forum::VALIDATE_NONE;
    }

    public function setParent(?self $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?Message
    {
        return $this->parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setFlagged(bool $bool): void
    {
        $this->flagged = $bool;
    }

    public function isFlagged(): bool
    {
        return $this->flagged;
    }

    public function isFirst(): bool
    {
        return $this->first;
    }

    public function setFirst(bool $isFirst): void
    {
        $this->first = $isFirst;
    }
}
