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

use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_forum_subject")
 */
class Subject
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @ORM\Column(name="created", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Forum",
     *     inversedBy="subjects"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $forum;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Message",
     *     mappedBy="subject"
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $messages;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="user_id")
     */
    protected $creator;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $sticked = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $closed = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $flagged = false;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $author;

    /**
     * @ORM\Column(type="integer")
     */
    protected $viewCount = 0;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\File\PublicFile"
     * )
     * @ORM\JoinColumn(name="poster_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @var PublicFile
     */
    protected $poster;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->messages = new ArrayCollection();
        $this->creationDate = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * Returns the resource id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setForum(Forum $forum)
    {
        $this->forum = $forum;
    }

    public function getForum()
    {
        return $this->forum;
    }

    /**
     * Sets the subject creator.
     *
     * @param \Claroline\CoreBundle\Entity\User
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;
    }

    public function getCreator()
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

    public function setSticked($boolean)
    {
        $this->sticked = $boolean;
    }

    public function isSticked()
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

    public function getAuthor()
    {
        if (!$this->author) {
            return 'undefined';
        }

        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function getViewCount()
    {
        return $this->viewCount;
    }

    public function setViewCount($viewCount)
    {
        $this->viewCount = $viewCount;
    }

    public function setPoster(PublicFile $file)
    {
        $this->poster = $file;
    }

    public function getPoster()
    {
        return $this->poster;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}
