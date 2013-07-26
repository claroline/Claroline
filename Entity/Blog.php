<?php

namespace ICAP\BlogBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use ICAP\BlogBundle\Entity\BlogOptions;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__blog")
 * @ORM\Entity(repositoryClass="ICAP\BlogBundle\Repository\BlogRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Blog extends AbstractResource
{
    /**
     * @var Post[]
     *
     * @ORM\OneToMany(targetEntity="ICAP\BlogBundle\Entity\Post", mappedBy="blog", cascade={"persist"})
     * @ORM\OrderBy({"creationDate" = "ASC"})
     */
    protected $posts;

    /**
     * @var BlogOptions
     *
     * @ORM\OneToOne(targetEntity="BlogOptions", mappedBy="blog", cascade={"persist"})
     */
    protected $options;

    /**
     * @var string $infos
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $infos;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * @param ArrayCollection $posts
     *
     * @return Blog
     */
    public function setPosts(ArrayCollection $posts)
    {
        /** @var \ICAP\BlogBundle\Entity\Post[] $posts */
        foreach($posts as $post)
        {
            $post->setBlog($this);
        }

        $this->posts = $posts;

        return $this;
    }

    /**
     * @return ArrayCollection|Post[]
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param BlogOptions $options
     *
     * @return Blog
     */
    public function setOptions(BlogOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return BlogOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function isCommentsAuthorized()
    {
        return $this->getOptions()->getAuthorizeComment();
    }

    /**
     * @return bool
     */
    public function isAuthorizeAnonymousComment()
    {
        return $this->getOptions()->getAuthorizeAnonymousComment();
    }

    /**
     * @return bool
     */
    public function isAutoPublishComment()
    {
        return $this->getOptions()->getAutoPublishComment();
    }

    /**
     * @return bool
     */
    public function isAutoPublishPost()
    {
        return $this->getOptions()->getAutoPublishPost();
    }

    /**
     * @return array|\Claroline\CoreBundle\Entity\User[]
     */
    public function getAuthors()
    {
        $authors = array();

        foreach($this->getPosts() as $post)
        {
            $postAuthor                           = $post->getAuthor();
            $authors[$postAuthor->getUsername()]  = $postAuthor;
        }

        return $authors;
    }

    /**
     * @param string $infos
     *
     * @return Blog
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;

        return $this;
    }

    /**
     * @return string
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * @ORM\PostPersist
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entityManager = $args->getEntityManager();

        $blogOptions = new BlogOptions();
        $blogOptions->setBlog($this);

        $entityManager->persist($blogOptions);
        $entityManager->flush($blogOptions);
    }
}
