<?php

namespace Icap\BlogBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Tag;
use Icap\BlogBundle\Repository\PostRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @DI\Service("icap.blog.manager.post")
 */
class PostManager
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /** @var \Icap\BlogBundle\Repository\PostRepository */
    protected $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager"),
     *     "repo" = @DI\Inject("icap.blog.post_repository")
     * })
     */
    public function __construct(ObjectManager $om, PostRepository $repo)
    {
        $this->om = $om;
        $this->repo = $repo;
    }

    /**
     * @param Blog $blog
     * @param int  $page
     * @param bool $isAdmin
     *
     * @return array
     */
    public function getPostsPaged(Blog $blog, $page = 1, $isAdmin = false)
    {
        $query = $this->repo->getByDateDesc($blog, false, $isAdmin);

        return $this->setPager($query, $page, $blog->getOptions()->getPostPerPage());
    }

    /**
     * @param Blog $blog
     * @param Tag  $tag
     * @param $filterByPublishPost
     * @param int $page
     *
     * @return array
     */
    public function getPostsByTagPaged(Blog $blog, Tag $tag, $filterByPublishPost, $page = 1)
    {
        $query = $this->repo->getByTag($blog, $tag, $filterByPublishPost, false);

        return $this->setPager($query, $page, $blog->getOptions()->getPostPerPage());
    }

    /**
     * @param Blog $blog
     * @param User $author
     * @param $filterByPublishPost
     * @param int $page
     *
     * @return array
     */
    public function getPostsByAuthorPaged(Blog $blog, User $author, $filterByPublishPost, $page = 1)
    {
        $query = $this->repo->getByAuthor($blog, $author, $filterByPublishPost, false);

        return $this->setPager($query, $page, $blog->getOptions()->getPostPerPage());
    }

    /**
     * @param Blog $blog
     * @param $date
     * @param $filterByPublishPost
     * @param int $page
     *
     * @return array
     */
    public function getPostsByDatePaged(Blog $blog, $date, $filterByPublishPost, $page = 1)
    {
        $query = $this->repo->getByDate($blog, $date, $filterByPublishPost, false);

        return $this->setPager($query, $page, $blog->getOptions()->getPostPerPage());
    }

    /**
     * @param $query
     * @param $page
     * @param $maxPerPage
     *
     * @return array
     */
    private function setPager($query, $page, $maxPerPage)
    {
        $adapter = new DoctrineORMAdapter($query);
        $pager = new PagerFanta($adapter);
        $pager
            ->setMaxPerPage($maxPerPage)
            ->setCurrentPage($page)
        ;

        // Pagerfanta returns a traversable object, not directly serializable
        $posts = [];
        foreach ($pager->getCurrentPageResults() as $post) {
            $posts[] = $post;
        }

        return [
            'total' => $pager->getNbResults(),
            'count' => count($posts),
            'posts' => $posts,
        ];
    }
}
