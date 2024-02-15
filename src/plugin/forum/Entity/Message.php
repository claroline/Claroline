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

use Claroline\CoreBundle\Entity\AbstractMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="claro_forum_message")
 */
class Message extends AbstractMessage
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Subject",
     *     inversedBy="messages",
     *     cascade={"persist"}
     * )
     *
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    protected ?Subject $subject = null;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Message",
     *     inversedBy="children",
     * )
     *
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    protected ?Message $parent = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Message",
     *     mappedBy="parent"
     * )
     *
     * @var Message[]
     */
    protected $children;

    /**
     * @ORM\Column(type="string")
     */
    protected string $moderation = Forum::VALIDATE_NONE;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $flagged = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $first = false;

    /**
     * Message constructor.
     */
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

    public function setParent(self $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?Message
    {
        return $this->parent;
    }

    public function getChildren()
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
