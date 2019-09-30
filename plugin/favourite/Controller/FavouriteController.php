<?php

namespace HeVinci\FavouriteBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use HeVinci\FavouriteBundle\Manager\FavouriteManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/favourite", options={"expose"=true})
 */
class FavouriteController extends AbstractApiController
{
    /** @var ObjectManager */
    protected $om; // this is required by the RequestDecoderTrait. It should be fixed

    /** @var SerializerProvider */
    private $serializer;

    /** @var FavouriteManager */
    private $manager;

    /**
     * FavouriteController constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "manager"    = @DI\Inject("hevinci.favourite.manager")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     * @param FavouriteManager   $manager
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        FavouriteManager $manager
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * Gets the current user favourites.
     *
     * @EXT\Route("/", name="claro_user_favourites")
     * @EXT\ParamConverter("currentUser", converter="current_user")
     *
     * @param User $currentUser
     *
     * @return JsonResponse
     */
    public function listAction(User $currentUser)
    {
        $workspaces = $this->manager->getWorkspaces($currentUser);
        $resources = $this->manager->getResources($currentUser);

        return new JsonResponse([
            'workspaces' => array_map(function (Workspace $workspace) {
                return $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
            }, $workspaces),
            'resources' => array_map(function (ResourceNode $resource) {
                return $this->serializer->serialize($resource, [Options::SERIALIZE_MINIMAL]);
            }, $resources),
        ]);
    }

    /**
     * Creates or deletes favourite resources.
     *
     * @EXT\Route("/resources/toggle", name="hevinci_favourite_resources_toggle")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function toggleResourcesAction(User $user, Request $request)
    {
        $nodes = $this->decodeIdsString($request, ResourceNode::class);
        $this->manager->toggleResourceFavourites($user, $nodes);

        return new JsonResponse(null, 204);
    }

    /**
     * Creates or deletes favourite workspaces.
     *
     * @EXT\Route("/workspaces/toggle", name="hevinci_favourite_workspaces_toggle")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function toggleWorkspacesAction(User $user, Request $request)
    {
        $nodes = $this->decodeIdsString($request, Workspace::class);
        $this->manager->toggleWorkspaceFavourites($user, $nodes);

        return new JsonResponse(null, 204);
    }
}
