<?php

namespace Icap\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__blog_options")
 * @ORM\Entity(repositoryClass="Icap\BlogBundle\Repository\BlogOptionsRepository")
 */
class BlogOptions
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
     * @var Blog
     *
     * @ORM\OneToOne(targetEntity="Blog", inversedBy="options", cascade={"persist"})
     */
    protected $blog;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="authorize_comment")
     */
    protected $authorizeComment;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="authorize_anonymous_comment")
     */
    protected $authorizeAnonymousComment;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", name="post_per_page")
     */
    protected $postPerPage;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="auto_publish_post")
     */
    protected $autoPublishPost;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="auto_publish_comment")
     */
    protected $autoPublishComment;

    public function __construct()
    {
        $this->authorizeComment          = false;
        $this->authorizeAnonymousComment = false;
        $this->postPerPage               = 10;
        $this->autoPublishPost           = false;
        $this->autoPublishComment        = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Blog $blog
     *
     * @return BlogOptions
     */
    public function setBlog(Blog $blog)
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * @return Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * @param boolean $authorizeAnonymousComment
     *
     * @return BlogOptions
     */
    public function setAuthorizeAnonymousComment($authorizeAnonymousComment)
    {
        $this->authorizeAnonymousComment = $authorizeAnonymousComment;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAuthorizeAnonymousComment()
    {
        return $this->authorizeAnonymousComment;
    }

    /**
     * @param boolean $authorizeComment
     *
     * @return BlogOptions
     */
    public function setAuthorizeComment($authorizeComment)
    {
        $this->authorizeComment = $authorizeComment;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAuthorizeComment()
    {
        return $this->authorizeComment;
    }

    /**
     * @param boolean $autoPublishComment
     *
     * @return BlogOptions
     */
    public function setAutoPublishComment($autoPublishComment)
    {
        $this->autoPublishComment = $autoPublishComment;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAutoPublishComment()
    {
        return $this->autoPublishComment;
    }

    /**
     * @param boolean $autoPublishPost
     *
     * @return BlogOptions
     */
    public function setAutoPublishPost($autoPublishPost)
    {
        $this->autoPublishPost = $autoPublishPost;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAutoPublishPost()
    {
        return $this->autoPublishPost;
    }

    /**
     * @param int $postPerPage
     *
     * @return BlogOptions
     */
    public function setPostPerPage($postPerPage)
    {
        $this->postPerPage = $postPerPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getPostPerPage()
    {
        return $this->postPerPage;
    }

}
