<?php

namespace Icap\BlogBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Icap\NotificationBundle\Entity\UserPickerContent;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="icap__blog_comment")
 * @ORM\Entity(repositoryClass="Icap\BlogBundle\Repository\CommentRepository")
 * @ORM\EntityListeners({"Icap\BlogBundle\Listener\CommentListener"})
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 */
class Comment extends Statusable
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     * @Groups({"blog_list", "blog_post"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Expose
     * @Groups({"blog_list", "blog_post"})
     */
    protected $message;

    /**
     * @var \Datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="creation_date")
     * @Expose
     * @Groups({"blog_list", "blog_post"})
     */
    protected $creationDate;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="datetime", name="publication_date", nullable=true)
     * @Gedmo\Timestampable(on="change", field="status", value="1")
     * @Expose
     * @Groups({"blog_list", "blog_post"})
     */
    protected $publicationDate;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="datetime", name="update_date", nullable=true)
     * @Gedmo\Timestampable(on="change", field="message")
     * @Expose
     * @Groups({"blog_list", "blog_post"})
     */
    protected $updateDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Expose
     * @Groups({"blog_list", "blog_post"})
     */
    protected $author;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Icap\BlogBundle\Entity\Post", inversedBy="comments")
     */
    protected $post;

    protected $userPicker = null;

    public function __construct()
    {
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return Comment
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Comment
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set author.
     *
     * @param User $author
     *
     * @return Comment
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set post.
     *
     * @param Post $post
     *
     * @return Comment
     */
    public function setPost(Post $post = null)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post.
     *
     * @return Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param \Datetime $publicationDate
     *
     * @return Comment
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * @param \Datetime $updateDate
     *
     * @return Comment
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param UserPickerContent $userPicker
     *
     * @return $this
     */
    public function setUserPicker(UserPickerContent $userPicker)
    {
        $this->userPicker = $userPicker;

        return $this;
    }

    /**
     * @return \Icap\NotificationBundle\Entity\UserPickerContent
     */
    public function getUserPicker()
    {
        return $this->userPicker;
    }
}
