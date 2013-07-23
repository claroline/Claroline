<?php
namespace ICAP\BlogBundle\Twig;

use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Manager\TagManager;

class ICAPBlogTagExtension extends \Twig_Extension
{
    /** @var \ICAP\BlogBundle\Manager\TagManager  */
    protected $tagManager;

    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    /**
     * @return TagManager
     */
    public function getTagManager()
    {
        return $this->tagManager;
    }

    public function getName()
    {
        return 'icap_blog_tag';
    }

    public function getFunctions()
    {
        return array(
            'blog_tags' => new \Twig_Function_Method($this, 'getTagsByBlog')
        );
    }

    public function getTagsByBlog(Blog $blog)
    {
        return $this->getTagManager()->getTagsByBlog($blog);
    }
}