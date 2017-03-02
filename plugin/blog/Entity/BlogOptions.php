<?php

namespace Icap\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__blog_options")
 * @ORM\Entity(repositoryClass="Icap\BlogBundle\Repository\BlogOptionsRepository")
 * @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class BlogOptions
{
    /**
     * @var int
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
     * @Expose
     * @ORM\Column(type="boolean", name="authorize_comment")
     */
    protected $authorizeComment = false;

    /**
     * @var bool
     * @Expose
     * @ORM\Column(type="boolean", name="authorize_anonymous_comment")
     */
    protected $authorizeAnonymousComment = false;

    /**
     * @var int
     * @Expose
     * @ORM\Column(type="smallint", name="post_per_page")
     */
    protected $postPerPage = 10;

    /**
     * @var bool
     * @Expose
     * @ORM\Column(type="boolean", name="auto_publish_post")
     */
    protected $autoPublishPost = false;

    /**
     * @var bool
     * @Expose
     * @ORM\Column(type="boolean", name="auto_publish_comment")
     */
    protected $autoPublishComment = false;

    /**
     * @var bool
     * @Expose
     * @ORM\Column(type="boolean", name="display_title")
     */
    protected $displayTitle = true;

    /**
     * @var bool
     * @Expose
     * @ORM\Column(type="boolean", name="banner_activate")
     */
    protected $bannerActivate = true;

    /**
     * @var bool
     * @Expose
     * @ORM\Column(type="boolean", name="display_post_view_counter", options={"default": 1})
     */
    protected $displayPostViewCounter = true;

    /**
     * @var string
     * @Expose
     * @ORM\Column(type="string", name="banner_background_color")
     */
    protected $bannerBackgroundColor = '#FFFFFF';

    /**
     * @var int
     * @Expose
     * @ORM\Column(type="smallint", name="banner_height")
     * @Assert\GreaterThanOrEqual(value = 100)
     */
    protected $bannerHeight = 100;

    /**
     * @var string
     * @Expose
     * @ORM\Column(type="string", name="banner_background_image", nullable=true)
     */
    protected $bannerBackgroundImage;

    /**
     * @var string
     * @Expose
     * @ORM\Column(type="string", name="banner_background_image_position")
     */
    protected $bannerBackgroundImagePosition = 'left top';

    /**
     * @var string
     * @Expose
     * @ORM\Column(type="string", name="banner_background_image_repeat")
     */
    protected $bannerBackgroundImageRepeat = 'no-repeat';

    /**
     * @var UploadedFile
     *
     * @Assert\Image()
     *
     * @Expose
     */
    protected $file;

    /**
     * @var string
     * @Expose
     */
    protected $oldFileName = null;

    /**
     * @var int
     *
     * Option for tag cloud rendering (Classic:0 (or null), 3D sphere:1, classic with number of article per tag: 2:1)
     * @Expose
     * @ORM\Column(type="smallint", name="tag_cloud", nullable=true)
     */
    protected $tagCloud = null;

    /**
     * @var string
     *
     * Option to display the widget bar on the right
     * @Expose
     * @ORM\Column(type="string", name="display_list_widget_blog_right", nullable=false, options={"default" = "01112131415161"})
     */
    protected $listWidgetBlog = '01112131415161';

    /**
     * @var bool
     * @Expose
     * @ORM\Column(type="boolean", name="tag_top_mode")
     */
    protected $tagTopMode = false;

    /**
     * @var int
     * @Expose
     * @ORM\Column(type="smallint", name="max_tag", nullable=false, options={"default" = 50})
     */
    protected $maxTag = 50;

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
     * @param bool $authorizeAnonymousComment
     *
     * @return BlogOptions
     */
    public function setAuthorizeAnonymousComment($authorizeAnonymousComment)
    {
        $this->authorizeAnonymousComment = $authorizeAnonymousComment;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAuthorizeAnonymousComment()
    {
        return $this->authorizeAnonymousComment;
    }

    /**
     * @param bool $authorizeComment
     *
     * @return BlogOptions
     */
    public function setAuthorizeComment($authorizeComment)
    {
        $this->authorizeComment = $authorizeComment;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAuthorizeComment()
    {
        return $this->authorizeComment;
    }

    /**
     * @param bool $autoPublishComment
     *
     * @return BlogOptions
     */
    public function setAutoPublishComment($autoPublishComment)
    {
        $this->autoPublishComment = $autoPublishComment;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAutoPublishComment()
    {
        return $this->autoPublishComment;
    }

    /**
     * @param bool $autoPublishPost
     *
     * @return BlogOptions
     */
    public function setAutoPublishPost($autoPublishPost)
    {
        $this->autoPublishPost = $autoPublishPost;

        return $this;
    }

    /**
     * @return bool
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

    /**
     * @param bool $displayTitle
     *
     * @return BlogOptions
     */
    public function setDisplayTitle($displayTitle)
    {
        $this->displayTitle = $displayTitle;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDisplayTitle()
    {
        return $this->displayTitle;
    }

    /**
     * @param string $bannerBackgroundColor
     *
     * @return BlogOptions
     */
    public function setBannerBackgroundColor($bannerBackgroundColor)
    {
        $this->bannerBackgroundColor = $bannerBackgroundColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getBannerBackgroundColor()
    {
        return $this->bannerBackgroundColor;
    }

    /**
     * @param int $bannerHeight
     *
     * @return BlogOptions
     */
    public function setBannerHeight($bannerHeight)
    {
        $this->bannerHeight = $bannerHeight;

        return $this;
    }

    /**
     * @return int
     */
    public function getBannerHeight()
    {
        return $this->bannerHeight;
    }

    /**
     * @param string $bannerBackgroundImage
     *
     * @return BlogOptions
     */
    public function setBannerBackgroundImage($bannerBackgroundImage)
    {
        $this->bannerBackgroundImage = $bannerBackgroundImage;

        return $this;
    }

    /**
     * @return string
     */
    public function getBannerBackgroundImage()
    {
        return $this->bannerBackgroundImage;
    }

    /**
     * @param int $bannerBackgroundImagePosition
     *
     * @return BlogOptions
     */
    public function setBannerBackgroundImagePosition($bannerBackgroundImagePosition)
    {
        $this->bannerBackgroundImagePosition = $bannerBackgroundImagePosition;

        return $this;
    }

    /**
     * @return int
     */
    public function getBannerBackgroundImagePosition()
    {
        return $this->bannerBackgroundImagePosition;
    }

    /**
     * @param int $bannerBackgroundImageRepeat
     *
     * @return BlogOptions
     */
    public function setBannerBackgroundImageRepeat($bannerBackgroundImageRepeat)
    {
        $this->bannerBackgroundImageRepeat = $bannerBackgroundImageRepeat;

        return $this;
    }

    /**
     * @return int
     */
    public function getBannerBackgroundImageRepeat()
    {
        return $this->bannerBackgroundImageRepeat;
    }

    /**
     * @param bool $bannerActivate
     *
     * @return BlogOptions
     */
    public function setBannerActivate($bannerActivate)
    {
        $this->bannerActivate = $bannerActivate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBannerActivate()
    {
        return $this->bannerActivate;
    }

    /**
     * @param UploadedFile $file
     *
     * @return BlogOptions
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param int $tagCloud
     *
     * @return BlogOptions
     */
    public function setTagCloud($tagCloud)
    {
        $this->tagCloud = $tagCloud;

        return $this;
    }

    /**
     * @return int
     */
    public function getTagCloud()
    {
        return $this->tagCloud;
    }

    /**
     * @param string $listWidgetBlog
     *
     * @return BlogOptions
     */
    public function setListWidgetBlog($listWidgetBlog)
    {
        $this->listWidgetBlog = $listWidgetBlog;

        return $this;
    }

    /**
     * @return string
     */
    public function getListWidgetBlog()
    {
        return $this->listWidgetBlog;
    }

    /**
     * @param bool $displayPostViewCounter
     *
     * @return BlogOptions
     */
    public function setDisplayPostViewCounter($displayPostViewCounter)
    {
        $this->displayPostViewCounter = $displayPostViewCounter;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDisplayPostViewCounter()
    {
        return $this->displayPostViewCounter;
    }

    /**
     * @return int
     */
    public function getMaxTag()
    {
        return $this->maxTag;
    }

    /**
     * @param int $maxTag
     *
     * @return BlogOptions
     */
    public function setMaxTag($maxTag)
    {
        $this->maxTag = $maxTag;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTagTopMode()
    {
        return $this->tagTopMode;
    }

    /**
     * @param bool $tagTopMode
     *
     * @return BlogOptions
     */
    public function setTagTopMode($tagTopMode)
    {
        $this->tagTopMode = $tagTopMode;

        return $this;
    }
}
