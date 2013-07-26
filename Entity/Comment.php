<?php

namespace ICAP\BlogBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__blog_comment")
 * @ORM\Entity(repositoryClass="ICAP\BlogBundle\Repository\CommentRepository")
 */
class Comment extends Statusable
{
    /**
     * @var int $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $message
     *
     * @ORM\Column(type="text")
     */
    protected $message;

    /**
     * @var \Datetime $creationDate
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="creation_date")
     */
    protected $creationDate;

    /**
     * @var \Datetime $publicationDate
     *
     * @ORM\Column(type="datetime", name="publication_date", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @Gedmo\Timestampable(on="change", field="status", value="1")
     */
    protected $publicationDate;

    /**
     * @var User $author
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="ICAP\BlogBundle\Entity\Post", inversedBy="comments")
     */
    protected $post;

    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Comment
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Comment
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set author
     *
     * @param User $author
     * @return Comment
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set post
     *
     * @param Post $post
     * @return Comment
     */
    public function setPost(Post $post = null)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return Post
     */
    public function getPost()
    {
        return $this->post;
    }
}
