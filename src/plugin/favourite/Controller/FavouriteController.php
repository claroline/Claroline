<?php

namespace HeVinci\FavouriteBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use HeVinci\FavouriteBundle\Manager\FavouriteManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/favourite', options: ['expose' => true])]
class FavouriteController
{
    use RequestDecoderTrait;

    /** @var ObjectManager */
    protected $om; // this is required by the RequestDecoderTrait. It should be fixed

    /** @var SerializerProvider */
    private $serializer;

    /** @var FavouriteManager */
    private $manager;

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
     * @EXT\ParamConverter("currentUser", converter="current_user")
     */
    #[Route(path: '/', name: 'claro_user_favourites')]
    public function listAction(User $currentUser): JsonResponse
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
     * @EXT\ParamConverter("user", converter="current_user")
     */
    #[Route(path: '/resources/toggle', name: 'hevinci_favourite_resources_toggle', methods: ['PUT'])]
    public function toggleResourcesAction(User $user, Request $request): JsonResponse
    {
        $nodes = $this->decodeIdsString($request, ResourceNode::class);
        $this->manager->toggleResourceFavourites($user, $nodes);

        return new JsonResponse(null, 204);
    }

    /**
     * Creates or deletes favourite workspaces.
     *
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    #[Route(path: '/workspaces/toggle', name: 'hevinci_favourite_workspaces_toggle', methods: ['PUT'])]
    public function toggleWorkspacesAction(User $user, Request $request): JsonResponse
    {
        $nodes = $this->decodeIdsString($request, Workspace::class);
        $this->manager->toggleWorkspaceFavourites($user, $nodes);

        return new JsonResponse(null, 204);
    }
}
