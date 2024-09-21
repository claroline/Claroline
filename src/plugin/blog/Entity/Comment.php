<?php

namespace Icap\BlogBundle\Entity;

use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'icap__blog_comment')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Comment extends Statusable
{
    use Id;
    use Uuid;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'creation_date')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeInterface $creationDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'publication_date', nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: '1')]
    private ?DateTimeInterface $publicationDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'update_date', nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: 'message')]
    private ?DateTimeInterface $updateDate;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $author;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    private ?Post $post;

    #[ORM\Column(type: 'smallint')]
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

    public function setCreationDate(?DateTimeInterface $creationDate = null): void
    {
        $this->creationDate = $creationDate;
    }

    public function getCreationDate(): ?DateTimeInterface
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

    public function setPublicationDate(DateTimeInterface $publicationDate = null): void
    {
        $this->publicationDate = $publicationDate;
    }

    public function getPublicationDate(): ?DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setUpdateDate(DateTimeInterface $updateDate = null): void
    {
        $this->updateDate = $updateDate;
    }

    public function getUpdateDate(): ?DateTimeInterface
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
