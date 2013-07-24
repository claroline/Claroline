<?php

namespace ICAP\BlogBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ICAPLyon1\Bundle\SimpleTagBundle\Entity\TaggableInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__blog_post")
 * @ORM\Entity(repositoryClass="ICAP\BlogBundle\Repository\PostRepository")
 */
class Post extends Statusable implements TaggableInterface
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
     * @var string $title
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @var string $content
     *
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @Gedmo\Slug(fields={"title"}, unique=true)
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @var \Datetime $creationDate
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="creation_date")
     */
    protected $creationDate;

    /**
     * @var \Datetime $modificationDate
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="modification_date")
     */
    protected $modificationDate;

    /**
     * @var \Datetime $publicationDate
     *
     * @ORM\Column(type="datetime", name="publication_date", nullable=true)
     * @Gedmo\Timestampable(on="change", field="status", value="1")
     */
    protected $publicationDate;

    /**
     * @var Comment
     *
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post", cascade={"remove"})
     */
    protected $comments;

    /**
     * @var User $author
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @var Blog
     *
     * @ORM\ManyToOne(targetEntity="ICAP\BlogBundle\Entity\Blog", inversedBy="posts")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id")
     */
    protected $blog;

    /**
     * @var PostTag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ICAP\BlogBundle\Entity\Tag", inversedBy="posts", cascade={"persist"})
     * @ORM\JoinTable(name="icap__blog_post_tag")
     */
    protected $tags;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags     = new ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Post
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Post
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
     * Set modificationDate
     *
     * @param \DateTime $modificationDate
     * @return Post
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return \DateTime 
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set publicationDate
     *
     * @param \DateTime $publicationDate
     * @return Post
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Get publicationDate
     *
     * @return \DateTime 
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Add comments
     *
     * @param Comment $comments
     * @return Post
     */
    public function addComment(Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param Comment $comments
     *
     * @return Post
     */
    public function removeComment(Comment $comments)
    {
        $this->comments->removeElement($comments);

        return $this;
    }

    /***
     * Set comments
     *
     * @param ArrayCollection $comments
     * @return Post
     */
    public function setComments(ArrayCollection $comments)
    {
        /** @var \ICAP\BlogBundle\Entity\Comment[] $comments */
        foreach($comments as $comment)
        {
            $comment->setPost($this);
        }

        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set author
     *
     * @param User $author
     * @return Post
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
     * Set blog
     *
     * @param Blog $blog
     * @return Post
     */
    public function setBlog(Blog $blog = null)
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * Get blog
     *
     * @return Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return (null !== $this->getPublicationDate());
    }

    /**
     * @param \ICAP\BlogBundle\Entity\PostTag[]|\Doctrine\Common\Collections\ArrayCollection $postTags
     *
     * @return Post
     */
    public function setTags($postTags)
    {
        $this->tags = $postTags;

        return $this;
    }

    /**
     * @return \ICAP\BlogBundle\Entity\PostTag[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag $tag
     *
     * @return Post
     */
    public function addTag(Tag $tag)
    {
        $this->tags->add($tag);

        return $this;
    }

    /**
     * @param Tag $tag
     *
     * @return Post
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->remove($tag);

        return $this;
    }

    /**
     * @return Post
     */
    public function publish()
    {
        return $this->setStatus(self::STATUS_PUBLISHED);
    }

    /**
     * @return Post
     */
    public function unpublish()
    {
        $this->publicationDate = null;

        return $this->setStatus(self::STATUS_UNPUBLISHED);
    }
}