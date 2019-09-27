<?php

namespace Icap\BlogBundle\Controller\API;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Manager\BlogManager;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\BlogOptionsSerializer;
use Icap\BlogBundle\Serializer\BlogSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("blog/{blogId}/", options={"expose"=true})
 * @EXT\ParamConverter("blog", class="IcapBlogBundle:Blog", options={"mapping": {"blogId": "uuid"}})
 */
class BlogController
{
    use PermissionCheckerTrait;

    /** @var FinderProvider */
    private $finder;
    private $blogSerializer;
    private $blogOptionsSerializer;
    private $blogManager;
    private $postManager;

    /**
     * BlogController constructor.
     *
     * @DI\InjectParams({
     *     "finder"                = @DI\Inject("claroline.api.finder"),
     *     "blogSerializer"        = @DI\Inject("Icap\BlogBundle\Serializer\BlogSerializer"),
     *     "blogOptionsSerializer" = @DI\Inject("Icap\BlogBundle\Serializer\BlogOptionsSerializer"),
     *     "blogManager"           = @DI\Inject("icap_blog.manager.blog"),
     *     "postManager"           = @DI\Inject("icap.blog.manager.post")
     * })
     *
     * @param FinderProvider        $finder
     * @param BlogSerializer        $blogSerializer
     * @param BlogOptionsSerializer $blogOptionsSerializer
     * @param BlogManager           $blogManager
     * @param PostManager           $postManager
     */
    public function __construct(
        FinderProvider $finder,
        BlogSerializer $blogSerializer,
        BlogOptionsSerializer $blogOptionsSerializer,
        BlogManager $blogManager,
        PostManager $postManager
      ) {
        $this->finder = $finder;
        $this->blogSerializer = $blogSerializer;
        $this->blogOptionsSerializer = $blogOptionsSerializer;
        $this->blogManager = $blogManager;
        $this->postManager = $postManager;
    }

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    public function getName()
    {
        return 'blog';
    }

    /**
     * Get blog options.
     *
     * @EXT\Route("options", name="apiv2_blog_options")
     * @EXT\Method("GET")
     *
     * @param Blog $blog
     *
     * @return array
     */
    public function getOptionsAction(Request $request, Blog $blog)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);

        return new JsonResponse($this->blogOptionsSerializer->serialize($blog, $blog->getOptions(), $this->blogManager->getPanelInfos()));
    }

    /**
     * Update blog options.
     *
     * @EXT\Route("options/update", name="apiv2_blog_options_update")
     * @EXT\Method("PUT")
     *
     * @param Blog $blog
     *
     * @return array
     */
    public function updateOptionsAction(Request $request, Blog $blog)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
        $data = json_decode($request->getContent(), true);
        $this->blogManager->updateOptions($blog, $this->blogOptionsSerializer->deserialize($data), $data['infos']);

        return new JsonResponse($this->blogOptionsSerializer->serialize($blog, $blog->getOptions()));
    }

    /**
     * Get tag cloud, tags used in blog posts.
     *
     * @EXT\Route("tags", name="apiv2_blog_tags")
     * @EXT\Method("GET")
     */
    public function getTagsAction(Blog $blog)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        $parameters['limit'] = -1;
        $posts = $this->postManager->getPosts(
            $blog->getId(),
            $parameters,
            $this->checkPermission('ADMINISTRATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())
            || $this->checkPermission('MODERATE', $blog->getResourceNode())
                ? PostManager::GET_ALL_POSTS
                : PostManager::GET_PUBLISHED_POSTS,
            true);

        $postsData = [];
        if (!empty($posts)) {
            $postsData = $posts['data'];
        }

        return new JsonResponse($this->blogManager->getTags($blog, $postsData));
    }
}
