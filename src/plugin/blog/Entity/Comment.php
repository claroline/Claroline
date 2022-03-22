<?php

namespace Icap\BlogBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Icap\NotificationBundle\Entity\UserPickerContent;

/**
 * @ORM\Table(name="icap__blog_comment")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Comment extends Statusable
{
    use Uuid;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $message;

    /**
     * @var \Datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="creation_date")
     */
    protected $creationDate;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="datetime", name="publication_date", nullable=true)
     * @Gedmo\Timestampable(on="change", field="status", value="1")
     */
    protected $publicationDate;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="datetime", name="update_date", nullable=true)
     * @Gedmo\Timestampable(on="change", field="message")
     */
    protected $updateDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $author;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Icap\BlogBundle\Entity\Post", inversedBy="comments")
     */
    protected $post;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $reported = 0;

    protected $userPicker = null;

    /**
     * Comment constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
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
     * @param \DateTimeInterface $creationDate
     *
     * @return Comment
     */
    public function setCreationDate(\DateTimeInterface $creationDate = null)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTimeInterface
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set author.
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
     * @param \DateTimeInterface $publicationDate
     *
     * @return Comment
     */
    public function setPublicationDate(\DateTimeInterface $publicationDate = null)
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
     * @param \DateTimeInterface $updateDate
     *
     * @return Comment
     */
    public function setUpdateDate(\DateTimeInterface $updateDate = null)
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

    public function setReported($num)
    {
        $this->reported = $num;
    }

    public function getReported()
    {
        return $this->reported;
    }
}
