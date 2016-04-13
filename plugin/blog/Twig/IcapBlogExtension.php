<?php

namespace Icap\BlogBundle\Twig;

use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Repository\PostRepository;
use Icap\BlogBundle\Manager\TagManager;

class IcapBlogExtension extends \Twig_Extension
{
    /** @var \Icap\BlogBundle\Manager\TagManager */
    protected $tagManager;

    /** @var \Icap\BlogBundle\Repository\PostRepository */
    protected $postRepository;

    protected $uploadDir;
    protected $webDirectory;

    public function __construct(TagManager $tagManager, PostRepository $postManager, $uploadDir, $webDirectory)
    {
        $this->tagManager = $tagManager;
        $this->postRepository = $postManager;
        $this->uploadDir = $uploadDir;
        $this->webDirectory = $webDirectory;
    }

    /**
     * @return TagManager
     */
    public function getTagManager()
    {
        return $this->tagManager;
    }

    /**
     * @return PostRepository
     */
    public function getPostRepository()
    {
        return $this->postRepository;
    }

    public function getName()
    {
        return 'icap_blog';
    }

    public function getFunctions()
    {
        return array(
            'blog_tags' => new \Twig_Function_Method($this, 'getTagsByBlog'),
            'blog_authors' => new \Twig_Function_Method($this, 'getAuthorsByBlog'),
            'get_blog_banner' => new \Twig_Function_Method($this, 'getBlogBanner'),
            'get_blog_upload_dir' => new \Twig_Function_Method($this, 'getBlogUploadDir'),
            'get_blog_banner_web_path' => new \Twig_Function_Method($this, 'getBlogBannerWebPath'),
        );
    }

    public function getFilters()
    {
        return array(
            'highlight' => new \Twig_Filter_Method($this, 'highlight'),
            'tagnames' => new \Twig_Filter_Method($this, 'getTagNames'),
        );
    }

    public function getTagsByBlog(Blog $blog)
    {
        if ($blog->getOptions()->isTagTopMode()) {
            $tags = $this->getTagManager()->loadByBlog($blog, $blog->getOptions()->getMaxTag());
        } else {
            $tags = $this->getTagManager()->loadByBlog($blog);
            shuffle($tags);
        }

        return $tags;
    }

    public function getAuthorsByBlog(Blog $blog)
    {
        return $this->getPostRepository()->findAuthorsByBlog($blog);
    }

    public function getTagnames($tags)
    {
        $tagNames = array_map(function ($val) {return $val['name'];}, $tags);

        return $tagNames;
    }

    public function getBlogBanner(Blog $blog)
    {
        return $blog->getOptions()->getBannerBackgroundImage() ? realpath($this->uploadDir.'/'.$blog->getOptions()->getBannerBackgroundImage()) : null;
    }

    public function getBlogUploadDir()
    {
        return $this->webDirectory;
    }

    public function getBlogBannerWebPath(Blog $blog)
    {
        return $blog->getOptions()->getBannerBackgroundImage() ? $this->webDirectory.'/'.$blog->getOptions()->getBannerBackgroundImage() : null;
    }

    public function highlight($sentence, $search)
    {
        $searchParameters = explode(' ', trim($search));

        $returnHighlightedString = $sentence;

        foreach ($searchParameters as $searchParameter) {
            $returnHighlightedString = preg_replace('/('.$searchParameter.')/', '<span class="highlight">\1</span>', $returnHighlightedString);
        }

        return $returnHighlightedString;
    }
}
