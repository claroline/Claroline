<?php
namespace ICAP\BlogBundle\Twig;

use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Repository\PostRepository;
use ICAP\BlogBundle\Repository\TagRepository;

class ICAPBlogExtension extends \Twig_Extension
{
    /** @var \ICAP\BlogBundle\Repository\TagRepository */
    protected $tagRepository;

    /** @var \ICAP\BlogBundle\Repository\PostRepository */
    protected $postRepository;

    public function __construct(TagRepository $tagManager, PostRepository $postManager)
    {
        $this->tagRepository  = $tagManager;
        $this->postRepository = $postManager;
    }

    /**
     * @return TagRepository
     */
    public function getTagRepository()
    {
        return $this->tagRepository;
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
            'blog_tags'    => new \Twig_Function_Method($this, 'getTagsByBlog'),
            'blog_authors' => new \Twig_Function_Method($this, 'getAuthorsByBlog')
        );
    }

    public function getTagsByBlog(Blog $blog)
    {
        return $this->getTagRepository()->findByBlog($blog);
    }

    public function getAuthorsByBlog(Blog $blog)
    {
        return $this->getPostRepository()->findAuthorsByBlog($blog);
    }
}