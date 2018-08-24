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
     */
    protected $subject;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Message",
     *     inversedBy="children",
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Message",
     *     mappedBy="parent"
     * )
     */
    protected $children;

    /**
     * @ORM\Column(type="string")
     */
    protected $moderation = Forum::VALIDATE_NONE;

    /**
     * @ORM\Column(type="boolean")
     * todo: renommer
     */
    protected $flagged = false;

    /**
     * @ORM\Column(type="boolean")
     * todo: renommer
     */
    protected $first = false;

    //required because we use a "property_exists" somewhere in the crud and it doesn't work otherwise.
    protected $uuid;

    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        if ($parent = $this->getParent()) {
            return $parent->getSubject();
        }

        return $this->subject;
    }

    public function setModerated($moderated)
    {
        $this->moderation = $moderated;
    }

    public function getModerated()
    {
        return $this->moderation;
    }

    public function setParent(self $parent = null)
    {
        $this->parent = $parent;
    }

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

    public function setIsFirst($isFirst)
    {
        $this->first = $isFirst;
    }
}
