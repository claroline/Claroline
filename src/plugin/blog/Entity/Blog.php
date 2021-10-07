<?php

namespace Icap\BlogBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="icap__blog")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Blog extends AbstractResource
{
    use Uuid;

    /**
     * Blog constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @var Post[]
     *
     * @ORM\OneToMany(targetEntity="Icap\BlogBundle\Entity\Post", mappedBy="blog", cascade={"all"})
     * @ORM\OrderBy({"creationDate" = "ASC"})
     */
    protected $posts;

    /**
     * @var Member[]
     *
     * @ORM\OneToMany(targetEntity="Icap\BlogBundle\Entity\Member", mappedBy="blog", cascade={"all"})
     */
    protected $members;

    /**
     * @var BlogOptions
     *
     * @ORM\OneToOne(targetEntity="BlogOptions", mappedBy="blog", cascade={"all"})
     */
    protected $options;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $infos;

    /**
     * @return Blog
     */
    public function setPosts(ArrayCollection $posts)
    {
        /** @var Post[] $posts */
        foreach ($posts as $post) {
            $post->setBlog($this);
        }

        $this->posts = $posts;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountPublishedPosts()
    {
        $countPublishedPosts = 0;

        foreach ($this->getPosts() as $post) {
            if (Statusable::STATUS_PUBLISHED === $post->getStatus()) {
                ++$countPublishedPosts;
            }
        }

        return $countPublishedPosts;
    }

    /**
     * @return Blog
     */
    public function setOptions(BlogOptions $options)
    {
        $options->setBlog($this);

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
     * @return ArrayCollection|Post[]
     */
    public function getPosts()
    {
        return $this->posts;
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
        return BlogOptions::COMMENT_MODERATION_NONE === $this->getOptions()->getCommentModerationMode();
    }

    /**
     * @return bool
     */
    public function isAutoPublishPost()
    {
        return $this->getOptions()->getAutoPublishPost();
    }

    /**
     * @return bool
     */
    public function displayPostViewCounter()
    {
        return $this->getOptions()->getDisplayPostViewCounter();
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
        if (null === $this->getOptions()) {
            $entityManager = $args->getEntityManager();

            $blogOptions = new BlogOptions();
            $blogOptions->setBlog($this);
            $this->setOptions($blogOptions);

            $entityManager->persist($blogOptions);
            $entityManager->flush($blogOptions);
        }
    }
}
