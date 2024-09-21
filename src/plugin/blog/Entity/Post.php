<?php

namespace Icap\BlogBundle\Entity;

use Icap\BlogBundle\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'icap__blog_post')]
#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Post extends Statusable
{
    use Id;
    use Creator;
    use Poster;
    use Thumbnail;
    use Uuid;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $title;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content;

    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['title'], unique: true, updatable: false)]
    private ?string $slug;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'creation_date')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeInterface $creationDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'modification_date', nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: ['title', 'content'])]
    private ?DateTimeInterface $modificationDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'publication_date', nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: '1')]
    private ?DateTimeInterface $publicationDate;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => '0'])]
    private int $viewCounter = 0;

    #[ORM\Column(type: Types::BOOLEAN, name: 'pinned')]
    private bool $pinned = false;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, cascade: ['all'])]
    #[ORM\OrderBy(['creationDate' => 'DESC'])]
    private Collection $comments;

    #[ORM\Column(nullable: true)]
    private ?string $author;

    #[ORM\JoinColumn(name: 'blog_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Blog::class, inversedBy: 'posts')]
    private ?Blog $blog;

    public function __construct()
    {
        $this->refreshUuid();

        $this->comments = new ArrayCollection();
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setCreationDate(DateTimeInterface $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getCreationDate(): ?DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setModificationDate(DateTimeInterface $modificationDate): void
    {
        $this->modificationDate = $modificationDate;
    }

    public function getModificationDate(): ?DateTimeInterface
    {
        return $this->modificationDate;
    }

    public function setPublicationDate(DateTimeInterface $publicationDate = null): void
    {
        $this->publicationDate = $publicationDate;
    }

    public function getPublicationDate(): ?DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function addComment(Comment $comments): void
    {
        $this->comments[] = $comments;
    }

    public function removeComment(Comment $comment): void
    {
        $this->comments->removeElement($comment);
    }

    public function setComments(Collection $comments): void
    {
        /** @var Comment[] $comments */
        foreach ($comments as $comment) {
            $comment->setPost($this);
        }

        $this->comments = $comments;
    }

    /**
     * Get comments.
     *
     * @return Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function setAuthor(?string $author = null): void
    {
        $this->author = $author;

    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setBlog(Blog $blog = null): void
    {
        $this->blog = $blog;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function countComments(bool $countUnpublished = false): int
    {
        $countComments = 0;

        if ($countUnpublished) {
            $countComments = $this->getComments()
                    ->count();
        } else {
            foreach ($this->getComments() as $comment) {
                if ($comment->isPublished()) {
                    ++$countComments;
                }
            }
        }

        return $countComments;
    }

    public function countUnpublishedComments(): int
    {
        return $this->countComments(true) - $this->countComments(false);
    }

    public function isPublished(bool $checkDate = true): bool
    {
        $isStatusPublished = parent::isPublished();

        if ($checkDate) {
            $currentTimestamp = time();

            if ($isStatusPublished && (null !== $this->publicationDate && $currentTimestamp >= $this->publicationDate->getTimestamp())) {
                return true;
            }
        } else {
            return $isStatusPublished;
        }

        return false;
    }

    public function setViewCounter(int $viewCounter): void
    {
        $this->viewCounter = $viewCounter;
    }

    public function getViewCounter(): int
    {
        return $this->viewCounter;
    }

    public function increaseViewCounter(): void
    {
        $this->setViewCounter(++$this->viewCounter);
    }

    public function getAbstract(): ?string
    {
        return !empty($this->content) && strlen($this->content) > 400 ? TextNormalizer::resumeHtml($this->content, 400) : $this->content;
    }

    public function isAbstract(): bool
    {
        return strlen($this->content) > 400;
    }

    public function setPinned($pinned): void
    {
        $this->pinned = $pinned;
    }

    public function isPinned(): bool
    {
        return $this->pinned;
    }
}
