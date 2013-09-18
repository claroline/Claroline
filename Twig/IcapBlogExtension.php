<?php
namespace Icap\BlogBundle\Twig;

use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Repository\PostRepository;
use Icap\BlogBundle\Repository\TagRepository;

class IcapBlogExtension extends \Twig_Extension
{
    /** @var \Icap\BlogBundle\Repository\TagRepository */
    protected $tagRepository;

    /** @var \Icap\BlogBundle\Repository\PostRepository */
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

    public function getFilters()
    {
        return array(
            'highlight'  => new \Twig_Filter_Method($this, 'highlight'),
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

    public function highlight($sentence, $search)
    {
        $searchParameters = explode(' ', trim($search));

        $returnHighlightedString = $sentence;

        foreach ($searchParameters as $searchParameter) {
            $returnHighlightedString = preg_replace('/(' . $searchParameter . ')/','<span class="highlight">\1</span>', $returnHighlightedString);
        }

        return $returnHighlightedString;
    }
}
