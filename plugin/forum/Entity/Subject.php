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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\SubjectRepository")
 * @ORM\Table(name="claro_forum_subject")
 */
class Subject
{
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
     *     targetEntity="Claroline\ForumBundle\Entity\Category",
     *     inversedBy="subjects"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $category;

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
    protected $isSticked = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isClosed = false;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $author;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->messages = new ArrayCollection();
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

    public function setCategory(Category $category)
    {
        $this->category = $category;
        $category->addSubject($this);
    }

    public function getCategory()
    {
        return $this->category;
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

    public function setIsSticked($boolean)
    {
        $this->isSticked = $boolean;
    }

    public function isSticked()
    {
        return $this->isSticked;
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

    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;
    }

    public function isClosed()
    {
        return $this->isClosed;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }
}
