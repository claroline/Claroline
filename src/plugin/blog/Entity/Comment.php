<?php

namespace Icap\BlogBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="icap__blog_comment")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Comment extends Statusable
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $message;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="creation_date")
     */
    private ?\DateTimeInterface $creationDate;

    /**
     * @ORM\Column(type="datetime", name="publication_date", nullable=true)
     * @Gedmo\Timestampable(on="change", field="status", value="1")
     */
    private ?\DateTimeInterface $publicationDate;

    /**
     * @ORM\Column(type="datetime", name="update_date", nullable=true)
     * @Gedmo\Timestampable(on="change", field="message")
     */
    private ?\DateTimeInterface $updateDate;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private ?User $author;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\BlogBundle\Entity\Post", inversedBy="comments")
     */
    private ?Post $post;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $reported = 0;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setCreationDate(?\DateTimeInterface $creationDate = null): void
    {
        $this->creationDate = $creationDate;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setAuthor(User $author = null): void
    {
        $this->author = $author;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setPost(Post $post = null): void
    {
        $this->post = $post;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPublicationDate(\DateTimeInterface $publicationDate = null): void
    {
        $this->publicationDate = $publicationDate;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setUpdateDate(\DateTimeInterface $updateDate = null): void
    {
        $this->updateDate = $updateDate;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->updateDate;
    }

    public function setReported(int $num): void
    {
        $this->reported = $num;
    }

    public function getReported(): int
    {
        return $this->reported;
    }
}
