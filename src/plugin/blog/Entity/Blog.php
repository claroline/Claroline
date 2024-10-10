<?php

namespace Icap\BlogBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'icap__blog')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Blog extends AbstractResource
{
    /**
     * @var Post[]
     */
    #[ORM\OneToMany(targetEntity: \Icap\BlogBundle\Entity\Post::class, mappedBy: 'blog', cascade: ['all'])]
    #[ORM\OrderBy(['creationDate' => 'ASC'])]
    protected $posts;

    /**
     * @var Member[]
     */
    #[ORM\OneToMany(targetEntity: \Icap\BlogBundle\Entity\Member::class, mappedBy: 'blog', cascade: ['all'])]
    protected $members;

    /**
     * @var BlogOptions
     */
    #[ORM\OneToOne(targetEntity: \BlogOptions::class, mappedBy: 'blog', cascade: ['all'])]
    protected $options;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $infos;

    public function __construct()
    {
        parent::__construct();

        $this->posts = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

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

    #[ORM\PostPersist]
    public function postPersist(PostPersistEventArgs $args)
    {
        if (null === $this->getOptions()) {
            $entityManager = $args->getObjectManager();

            $blogOptions = new BlogOptions();
            $blogOptions->setBlog($this);
            $this->setOptions($blogOptions);

            $entityManager->persist($blogOptions);
            $entityManager->flush($blogOptions);
        }
    }
}
