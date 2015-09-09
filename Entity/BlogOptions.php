<?php

namespace Icap\BlogBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="icap__blog_options")
 * @ORM\Entity(repositoryClass="Icap\BlogBundle\Repository\BlogOptionsRepository")
 * @ORM\HasLifecycleCallbacks
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
    protected $authorizeComment = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="authorize_anonymous_comment")
     */
    protected $authorizeAnonymousComment = false;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", name="post_per_page")
     */
    protected $postPerPage = 10;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="auto_publish_post")
     */
    protected $autoPublishPost = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="auto_publish_comment")
     */
    protected $autoPublishComment = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="display_title")
     */
    protected $displayTitle = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="banner_activate")
     */
    protected $bannerActivate = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="display_post_view_counter", options={"default": 1})
     */
    protected $displayPostViewCounter = true;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="banner_background_color")
     */
    protected $bannerBackgroundColor = '#FFFFFF';

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", name="banner_height")
     * @Assert\GreaterThanOrEqual(value = 100)
     */
    protected $bannerHeight = 100;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="banner_background_image", nullable=true)
     */
    protected $bannerBackgroundImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="banner_background_image_position")
     */
    protected $bannerBackgroundImagePosition = 'left top';

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="banner_background_image_repeat")
     */
    protected $bannerBackgroundImageRepeat = 'no-repeat';

    /**
     * @var UploadedFile
     *
     * @Assert\Image()
     */
    protected $file;

    /**
     * @var string
     */
    protected $oldFileName = null;

    /**
     * @var int
     *
     * Option for tag cloud rendering (Classic:0 (or null), 3D sphere:1, classic with numbre of article per tag: 2:1)
     *
     * @ORM\Column(type="smallint", name="tag_cloud", nullable=true)
     */
    protected $tagCloud = null;


    /**
     * @var string
     *
     * Option to display the widget bar on the right
     *
     * @ORM\Column(type="string", name="display_list_widget_blog_right", nullable=false)
     */
    protected $listWidgetBlog = "01112131415161" ;


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

    /**
     * @param boolean $displayTitle
     *
     * @return BlogOptions
     */
    public function setDisplayTitle($displayTitle)
    {
        $this->displayTitle = $displayTitle;

        return $this;
    }

    /**
     * @return boolean
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
        if (null !== $this->bannerBackgroundImage) {
            $this->oldFileName = $this->bannerBackgroundImage;
        }
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
     * @param boolean $bannerActivate
     *
     * @return BlogOptions
     */
    public function setBannerActivate($bannerActivate)
    {
        $this->bannerActivate = $bannerActivate;

        return $this;
    }

    /**
     * @return boolean
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
        $newFileName = $file->getClientOriginalName();

        if ($this->bannerBackgroundImage !== $newFileName) {
            $this->oldFileName           = $this->bannerBackgroundImage;
            $this->bannerBackgroundImage = null;
        }
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
     * @param boolean $displayPostViewCounter
     *
     * @return BlogOptions
     */
    public function setDisplayPostViewCounter($displayPostViewCounter)
    {
        $this->displayPostViewCounter = $displayPostViewCounter;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisplayPostViewCounter()
    {
        return $this->displayPostViewCounter;
    }

    /**
     * @return null|string
     */
    public function getBannerBackgroundImageAbsolutePath()
    {
        return (null === $this->bannerBackgroundImage) ? null : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->bannerBackgroundImage;
    }

    /**
     * @return null|string
     */
    public function getBannerBackgroundImageWebPath()
    {
        return (null === $this->bannerBackgroundImage) ? null : $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->bannerBackgroundImage;
    }

    /**
     * @throws \Exception
     * @return string
     */
    protected function getUploadRootDir()
    {
        $ds = DIRECTORY_SEPARATOR;

        $uploadRootDir = sprintf('%s%s..%s..%s..%s..%s..%s..%sweb%s%s', __DIR__, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $this->getUploadDir());

        if (!is_dir($uploadRootDir)) {
            if (false === mkdir($uploadRootDir)) {
                throw new \Exception(sprintf("Unable to create the upload directory '%s' for blog banner.", $uploadRootDir));
            }
        }

        $realpathUploadRootDir = realpath($uploadRootDir);

        if (false === $realpathUploadRootDir) {
            throw new \Exception(sprintf("Invalid upload root dir '%s'for uploading blog banner background images.", $uploadRootDir));
        }

        return $realpathUploadRootDir;
    }

    /**
     * @return string
     */
    public function getUploadDir()
    {
        return sprintf("uploads%sblogs", DIRECTORY_SEPARATOR);
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        if (null !== $this->file) {
            $this->bannerBackgroundImage = $this->file->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostUpdate()
     */
    public function postUpdate()
    {
        if (null === $this->file && null === $this->oldFileName) {
            return;
        }

        if (null !== $this->bannerBackgroundImage) {
            $this->file->move($this->getUploadRootDir(), $this->bannerBackgroundImage);
        }

        if (null !== $this->oldFileName) {
            unlink($this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->oldFileName);
            $this->oldFileName = null;
        }

        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        $filePath = $this->getBannerBackgroundImageAbsolutePath();
        if (null !== $filePath) {
            unlink($filePath);
        }
    }

    /**
     * Get bannerActivate
     *
     * @return boolean
     */
    public function getBannerActivate()
    {
        return $this->bannerActivate;
    }
}
