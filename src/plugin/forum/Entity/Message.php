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
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     *
     * @var Subject
     */
    protected $subject;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Message",
     *     inversedBy="children",
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     *
     * @var Message
     */
    protected $parent;

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
    protected $moderation = Forum::VALIDATE_NONE;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $flagged = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $first = false;

    /**
     * Message constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->children = new ArrayCollection();
    }

    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;
        $subject->addMessage($this);
    }

    /**
     * @return Subject
     */
    public function getSubject()
    {
        if ($this->getParent()) {
            return $this->getParent()->getSubject();
        }

        return $this->subject;
    }

    public function getForum()
    {
        if ($this->getSubject()) {
            return $this->getSubject()->getForum();
        }

        return null;
    }

    public function setModerated($moderated)
    {
        $this->moderation = $moderated;
    }

    public function getModerated()
    {
        return $this->moderation ? $this->moderation : Forum::VALIDATE_NONE;
    }

    public function setParent(self $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return Message
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setFlagged($bool)
    {
        $this->flagged = $bool;
    }

    public function isFlagged()
    {
        return $this->flagged;
    }

    public function isFirst()
    {
        return $this->first;
    }

    public function setFirst($isFirst)
    {
        $this->first = $isFirst;
    }
}
