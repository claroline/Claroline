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

    public function __construct(TagManager $tagManager, PostRepository $postManager)
    {
        $this->tagManager  = $tagManager;
        $this->postRepository = $postManager;
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
            'blog_tags'    => new \Twig_Function_Method($this, 'getTagsByBlog'),
            'blog_authors' => new \Twig_Function_Method($this, 'getAuthorsByBlog')
        );
    }

    public function getFilters()
    {
        return array(
            'highlight' => new \Twig_Filter_Method($this, 'highlight'),
            'tagnames'    => new \Twig_Filter_Method($this, 'getTagNames'),
        );
    }

    public function getTagsByBlog(Blog $blog)
    {
        if($blog->getOptions()->isTagTopMode()) {
            $tags = $this->getTagManager()->loadByBlog($blog, $blog->getOptions()->getMaxTag());
        }
        else {
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
        $tagNames = array_map(function($val){return $val["name"];}, $tags);

        return $tagNames;
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
