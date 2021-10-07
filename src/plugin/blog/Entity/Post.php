<?php

namespace Icap\BlogBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Icap\BlogBundle\Utils\StringUtils;
use Icap\NotificationBundle\Entity\UserPickerContent;

/**
 * @ORM\Table(name="icap__blog_post")
 * @ORM\Entity(repositoryClass="Icap\BlogBundle\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Post extends Statusable
{
    use Creator;
    use Poster;
    use Thumbnail;
    use Uuid;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @Gedmo\Slug(fields={"title"}, unique=true, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="datetime", name="creation_date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="datetime", name="modification_date", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"title", "content"})
     */
    protected $modificationDate;

    /**
     * @var \Datetime
     * @ORM\Column(type="datetime", name="publication_date", nullable=true)
     * @Gedmo\Timestampable(on="change", field="status", value="1")
     */
    protected $publicationDate;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default": "0"})
     */
    protected $viewCounter = 0;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="pinned")
     */
    protected $pinned = false;

    /**
     * @var Comment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post", cascade={"all"})
     * @ORM\OrderBy({"creationDate" = "DESC"})
     */
    protected $comments;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    protected $author;

    /**
     * @var Blog
     *
     * @ORM\ManyToOne(targetEntity="Icap\BlogBundle\Entity\Blog", inversedBy="posts")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id")
     */
    protected $blog;

    protected $userPicker = null;

    public function __construct()
    {
        $this->refreshUuid();

        $this->comments = new ArrayCollection();
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
     * Set title.
     *
     * @param string $title
     *
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $url
     * @param string $text
     *
     * @return string
     */
    public function getShortContent($url, $text)
    {
        $readMoreText = sprintf('... <a href="%s" class="read_more">%s <span class="fa fa-long-arrow-right"></span></a>', $url, $text);

        return StringUtils::resumeHtml($this->content, 400, $readMoreText);
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Post
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set creationDate.
     *
     * @return Post
     */
    public function setCreationDate(\DateTimeInterface $creationDate)
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
     * Set modificationDate.
     *
     * @return Post
     */
    public function setModificationDate(\DateTimeInterface $modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set publicationDate.
     *
     * @return Post
     */
    public function setPublicationDate(\DateTimeInterface $publicationDate = null)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Get publicationDate.
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Add comments.
     *
     * @return Post
     */
    public function addComment(Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments.
     *
     * @return Post
     */
    public function removeComment(Comment $comments)
    {
        $this->comments->removeElement($comments);

        return $this;
    }

    /**
     * Set comments.
     *
     * @return Post
     */
    public function setComments(ArrayCollection $comments)
    {
        /** @var Comment[] $comments */
        foreach ($comments as $comment) {
            $comment->setPost($this);
        }

        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments.
     *
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set author.
     *
     * @return Post
     */
    public function setAuthor(?string $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set blog.
     *
     * @return Post
     */
    public function setBlog(Blog $blog = null)
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * Get blog.
     *
     * @return Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * @param bool $countUnpublished
     *
     * @return int
     */
    public function countComments($countUnpublished = false)
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

    /**
     * @return int
     */
    public function countUnpublishedComments()
    {
        return $this->countComments(true) - $this->countComments(false);
    }

    /**
     * @param bool $checkDate
     *
     * @return bool
     */
    public function isPublished($checkDate = true)
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

    /**
     * @param int $viewCounter
     *
     * @return Post
     */
    public function setViewCounter($viewCounter)
    {
        $this->viewCounter = $viewCounter;

        return $this;
    }

    /**
     * @return int
     */
    public function getViewCounter()
    {
        return $this->viewCounter;
    }

    /**
     * @return Post
     */
    public function increaseViewCounter()
    {
        return $this->setViewCounter(++$this->viewCounter);
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

    /**
     * @return string
     */
    public function getAbstract()
    {
        return strlen($this->content) > 400 ? StringUtils::resumeHtml($this->content, 400) : $this->content;
    }

    /**
     * @return bool
     */
    public function isAbstract()
    {
        return strlen($this->content) > 400;
    }

    /**
     * @param bool $pinned
     *
     * @return Post
     */
    public function setPinned($pinned)
    {
        $this->pinned = $pinned;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPinned()
    {
        return $this->pinned;
    }
}
