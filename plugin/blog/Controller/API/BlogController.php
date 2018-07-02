<?php

namespace Icap\BlogBundle\Controller\API;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Manager\BlogManager;
use Icap\BlogBundle\Serializer\BlogOptionsSerializer;
use Icap\BlogBundle\Serializer\BlogSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("blog/", options={"expose"=true})
 */
class BlogController
{
    use PermissionCheckerTrait;

    /** @var FinderProvider */
    private $finder;
    private $blogSerializer;
    private $blogOptionsSerializer;
    private $blogManager;

    /**
     * BlogController constructor.
     *
     * @DI\InjectParams({
     *     "finder"                = @DI\Inject("claroline.api.finder"),
     *     "blogSerializer"        = @DI\Inject("claroline.serializer.blog"),
     *     "blogOptionsSerializer" = @DI\Inject("claroline.serializer.blog.options"),
     *     "blogManager"           = @DI\Inject("icap_blog.manager.blog")
     * })
     *
     * @param FinderProvider        $finder
     * @param BlogSerializer        $blogSerializer
     * @param BlogOptionsSerializer $blogOptionsSerializer
     * @param BlogManager           $blogManager
     */
    public function __construct(
        FinderProvider $finder,
        BlogSerializer $blogSerializer,
        BlogOptionsSerializer $blogOptionsSerializer,
        BlogManager $blogManager
      ) {
        $this->finder = $finder;
        $this->blogSerializer = $blogSerializer;
        $this->blogOptionsSerializer = $blogOptionsSerializer;
        $this->blogManager = $blogManager;
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
     * @EXT\Route("options/{blogId}", name="apiv2_blog_options")
     * @EXT\ParamConverter("blog", class="IcapBlogBundle:Blog", options={"mapping": {"blogId": "uuid"}})
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
     * @EXT\Route("options/update/{blogId}", name="apiv2_blog_options_update")
     * @EXT\ParamConverter("blog", class="IcapBlogBundle:Blog", options={"mapping": {"blogId": "uuid"}})
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

        // Options updated
        //return new JsonResponse(null, 204);

        return new JsonResponse($this->blogOptionsSerializer->serialize($blog, $blog->getOptions()));
    }
}
